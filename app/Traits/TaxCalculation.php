<?php

namespace App\Traits;

use App\Model\Common\Setting;
use App\Model\Payment\Tax;
use App\Model\Payment\TaxByState;
use App\Model\Payment\TaxClass;
use App\Model\Payment\TaxOption;
use App\Model\Payment\TaxProductRelation;

trait TaxCalculation
{
    public function calculateTax($productid, $user_state = '', $user_country = '', $taxCaluculationFromAdminPanel = false)
    {
        try {
            if ($taxCaluculationFromAdminPanel) {
                $taxCondition = ['name' => 'null', 'value' => '0%'];
            } else {
                $taxCondition = new \Darryldecode\Cart\CartCondition([
                    'name' => 'null', 'type' => 'tax',
                    'value' => '0%',
                ]);
            }
            if (TaxOption::findOrFail(1)->inclusive == 0) {
                $tax_enable = TaxOption::findOrFail(1)->tax_enable;
                //Check the state of user for calculating GST(cgst,igst,utgst,sgst)
                $indian_state = TaxByState::where('state_code', $user_state)->first();
                $origin_state = Setting::first()->state; //Get the State of origin
                $origin_country = Setting::first()->country; //Get the State of origin
                $tax_class_id = TaxProductRelation::where('product_id', $productid)->pluck('tax_class_id')->toArray();
                if ($tax_class_id) {//If the product is allowed for tax (Check in tax_product relation table)
                    if ($tax_enable == 1) {//If GST is Enabled
                        $tax = $this->getTaxDetails($indian_state, $user_country, $user_state, $origin_state, $origin_country, $productid);
                        //All the da a attribute that is sent to the checkout Page if tax_compound=0
                        $taxCondition = $this->getTaxConditions($tax, $taxCaluculationFromAdminPanel);
                    } elseif ($tax_enable == 0) { //If Tax enable is 0 and other tax is available
                        $tax = $this->whenOtherTaxAvailableAndTaxNotEnable($productid, $user_state, $user_country);
                        $taxCondition = $this->getTaxConditions($tax, $taxCaluculationFromAdminPanel);
                    }
                }
            }

            return  $taxCondition;
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getTaxConditions($tax, $taxCaluculationFromAdminPanel)
    {
        if ($tax) {
            if ($taxCaluculationFromAdminPanel) {
                $taxCondition = ['name' => $tax->name, 'value' => $tax->value.'%'];
            } else {
                $taxCondition = new \Darryldecode\Cart\CartCondition([
                    'name' => $tax->name, 'type' => 'tax',
                    'value' => $tax->value.'%',
                ]);
            }
        } else {
            if ($taxCaluculationFromAdminPanel) {
                $taxCondition = ['name' => 'null', 'value' => '0%'];
            } else {
                $taxCondition = new \Darryldecode\Cart\CartCondition([
                    'name' => 'null', 'type' => 'tax',
                    'value' => '0%',
                ]);
            }
        }

        return $taxCondition;
    }

    public function getTaxDetails($indian_state, $user_country, $user_state, $origin_state, $origin_country, $productid, $status = 1)
    {
        if ($origin_country == 'IN' && $indian_state) {//Get the CGST,SGST,IGST,STATE_CODE of the user,if user from INdia
            $c_gst = $indian_state->c_gst;
            $s_gst = $indian_state->s_gst;
            $i_gst = $indian_state->i_gst;
            $ut_gst = $indian_state->ut_gst;
            $state_code = $indian_state->state_code;
            if ($state_code == $origin_state) {//If user and origin state are same
                $rateDetails = $this->getTaxWhenIndianSameState($user_state, $origin_state, $productid, $c_gst, $s_gst, $state_code, $status);
            } elseif ($state_code != $origin_state && $ut_gst == 'NULL') {//If user is from other state
                $rateDetails = $this->getTaxWhenIndianOtherState($user_state, $origin_state, $productid, $i_gst, $state_code, $status);
            } elseif ($state_code != $origin_state && $ut_gst != 'NULL') {//if user from Union Territory
                $rateDetails = $this->getTaxWhenUnionTerritory($user_state, $origin_state, $productid, $c_gst, $ut_gst, $state_code, $status);
            }
        } else {
            $rateDetails = $this->getDetailsWhenUserFromOtherCountry($user_state, $user_country, $productid, $status);
        }

        return $rateDetails;
    }

    /**
     * When from same Indian State.
     */
    public function getTaxWhenIndianSameState($user_state, $origin_state, $productid, $c_gst, $s_gst, $state_code, $status)
    {
        $taxes = '';
        $taxClassId = TaxClass::where('name', 'Intra State GST')->select('id')->first(); //Get the class Id  of state
        if ($taxClassId) {
            $taxes = $this->getTaxByPriority($taxClassId);
            $taxes->value = $this->getValueForSameState($productid, $c_gst, $s_gst, $taxes, $taxClassId);
            if (! $taxes->value) {
                $taxes = '';
            }
        }

        return $taxes;
    }

    /**
     * When from other Indian State.
     */
    public function getTaxWhenIndianOtherState($user_state, $origin_state, $productid, $i_gst, $state_code, $status)
    {
        $taxes = '';
        $taxClassId = TaxClass::where('name', 'Inter State GST')->select('id')->first(); //Get the class Id  of state
        if ($taxClassId) {
            $taxes = $this->getTaxByPriority($taxClassId);
            $taxes->value = $this->getValueForOtherState($productid, $i_gst, $taxes, $taxClassId);
            if (! $taxes->value) {
                $taxes = '';
            }
        }

        return $taxes;
    }

    /**
     * When from Union Territory.
     */
    public function getTaxWhenUnionTerritory($user_state, $origin_state, $productid, $c_gst, $ut_gst, $state_code, $status)
    {
        $taxes = '';
        $taxClassId = TaxClass::where('name', 'Union Territory GST')->select('id')->first(); //Get the class Id  of state
        if ($taxClassId) {
            $taxes = $this->getTaxByPriority($taxClassId);
            $taxes->value = $this->getValueForUnionTerritory($productid, $c_gst, $ut_gst, $taxes, $taxClassId);
            if (! $taxes->value) {
                $taxes = '';
            }
        }

        return $taxes;
    }

    public function getDetailsWhenUserFromOtherCountry($user_state, $user_country, $productid, $status = 1)
    {
        $taxes = '';
        $taxClassId = Tax::where('state', $user_state)->orWhere('state', '')->where('country', $user_country)->select('tax_classes_id as id')->first();
        if ($taxClassId) { //if state equals the user State or country equals user country
            $taxes = $this->getTaxForSpecificCountry($taxClassId, $productid, $status);
        } else {//if Tax is selected for Any Country Any State
            $taxClassId = Tax::where('country', '')
                    ->where('state', '')
                    ->select('tax_classes_id as id')->first();
            if ($taxClassId) {
                $taxes = $this->getTaxForSpecificCountry($taxClassId, $productid, $status);
            }
        }

        return $taxes;
    }

    public function whenOtherTaxAvailableAndTaxNotEnable($productid, $user_state, $user_country)
    {
        $taxes = '';
        $taxClassId = Tax::where('country', '')
            ->where('state', '')
            ->select('tax_classes_id as id')->first(); //In case of India when
        // other tax is available and tax is not enabled
        if ($taxClassId) {
            $taxes = $this->getTaxByPriority($taxClassId);
            $taxes->value = $this->getValueForOthers($productid, $taxClassId, $taxes);
        } else {//In case of other country
            //when tax is available and tax is not enabled
            //(Applicable when Global Tax class for any country and state is not there)
            $taxClassId = Tax::where('state', $user_state)
            ->orWhere('country', $user_country)
            ->select('tax_classes_id as id')->first();
            if ($taxClassId) { //if state equals the user State
                $taxes = $this->getTaxByPriority($taxClassId);
                $taxes->value = $this->getValueForOthers($productid, $taxClassId, $taxes);
            }
        }

        return $taxes;
    }

    /**
     *   Get tax value for Same State.
     *
     * @param  int  $productid
     * @param  type  $c_gst
     * @param  type  $s_gst
     *                       return type
     */

    /**
     * When from Other Country and tax is applied for that country or state.
     */
    public function getTaxForSpecificCountry($taxClassId, $productid, $status)
    {
        $taxes = $this->getTaxByPriority($taxClassId);
        $taxes->value = $this->getValueForOthers($productid, $taxClassId, $taxes);
        if (! $taxes->value) {
            $taxes = '';
        }

        return $taxes;
    }

    public function getValueForSameState($productid, $c_gst, $s_gst, $taxes, $taxClassId)
    {
        try {
            $value = $taxes->active ? (TaxProductRelation::where('product_id', $productid)->where('tax_class_id', $taxClassId->id)->count() ? $c_gst + $s_gst : 0) : 0;

            return $value;
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    /**
     *   Get tax value for Other States.
     *
     * @param  type  $productid
     * @param  type  $i_gst
     *                       return type
     */
    public function getValueForOtherState($productid, $i_gst, $taxes, $taxClassId)
    {
        $value = $taxes->active ? //If the Current Class is active
              (TaxProductRelation::where('product_id', $productid)->where('tax_class_id', $taxClassId->id)->count() ?
                        $i_gst : 0) : 0; //IGST

        return $value;
    }

    /**
     *  Get tax value for Union Territory States.
     *
     * @param  type  $productid
     * @param  type  $c_gst
     * @param  type  $ut_gst
     *                        return type
     */
    public function getValueForUnionTerritory($productid, $c_gst, $ut_gst, $taxes, $taxClassId)
    {
        $value = $taxes->active ?
             (TaxProductRelation::where('product_id', $productid)
                ->where('tax_class_id', $taxClassId->id)
                ->count() ? $ut_gst + $c_gst : 0) : 0;

        return $value;
    }

    public function getValueForOthers($productid, $taxClassId, $taxes)
    {
        $value = $taxes->active ? (TaxProductRelation::where('product_id', $productid)
          ->where('tax_class_id', $taxClassId->id)->count() ? Tax::where('tax_classes_id', $taxClassId->id)->first()->rate : 0) : 0;

        return $value;
    }

    /**
     * @param  type  $tax_class_id
     * @return type
     *
     * @throws \Exception
     */
    public function getTaxByPriority($taxClassId)
    {
        try {
            $taxe_relation = Tax::where('tax_classes_id', $taxClassId->id)->first();

            return $taxe_relation;
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    /**
     * @param  type  $rate
     * @param  type  $price
     * @return type
     */
    public static function taxValue($rate, $price)
    {
        try {
            $result = 0;
            if ($rate) {
                $rate = str_replace('%', '', $rate);
                $tax = intval($price) * (intval($rate) / 100);
                $result = $tax;

                $result = rounding($result);
            }

            return $result;
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }
}
