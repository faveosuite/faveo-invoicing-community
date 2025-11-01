<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Model\Common\Country;
use App\Model\Common\State;
use App\Model\Payment\Tax;
use App\Model\Payment\TaxByState;
use App\Model\Payment\TaxClass;
use App\Model\Payment\TaxOption;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public $tax;

    public $country;

    public $state;

    public $tax_option;

    public $tax_class;

    public function __construct()
    {
        $this->middleware('auth', ['except' => 'getState']);
        $this->middleware('admin', ['except' => 'getState']);

        $tax = new Tax();
        $this->tax = $tax;

        $country = new Country();
        $this->country = $country;

        $state = new State();
        $this->state = $state;

        $tax_option = new TaxOption();
        $this->tax_option = $tax_option;

        $tax_class = new TaxClass();
        $this->tax_class = $tax_class;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Response
     */

    public function getTaxOptionsApi()
    {
        try {
            $options = $this->tax_option->find(1) ?: '';
            $classes = $this->tax_class->pluck('name', 'id')->toArray();

            if (empty($classes)) {
                $classes = $this->tax_class->get();
            }
            $countries = getSupportedCountriesForIntlInput();
            return successResponse('', [
                'options' => $options,
                'classes' => $classes,
                'countries' => $countries,
            ]);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage(), 500);
        }
    }

    /**
     * @return type
     */
    public function getTax(Request $request)
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortOrder    = $request->input('sort-order', 'desc');
            $sortField    = $request->input('sort-field', 'created_at');
            $limit        = $request->input('limit', 10);

            $taxes = Tax::with('taxClass')
                ->when($searchString, function ($query) use ($searchString) {
                    $query->where(function ($q) use ($searchString) {
                        $q->where('name', 'like', "%{$searchString}%")
                            ->orWhere('country', 'like', "%{$searchString}%")
                            ->orWhere('state', 'like', "%{$searchString}%");
                    });
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            $taxes->getCollection()->transform(function ($tax) {
                return [
                    'id'              => $tax->id,
                    'name'            => ucfirst($tax->name),
                    'country'         => $tax->country,
                    'state'           => $tax->state,
                    'rate'            => $tax->rate ?: 'Default',
                    'tax_class_name'  => $tax->taxClass ? ucfirst($tax->taxClass->name) : null,
                ];
            });

            return successResponse( __('message.tax_fetched'), ['data' => $taxes], 200);

        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function getTaxTable(Request $request)
    {
        try {
            $searchString = $request->input('search-query', '');
            $sortField = $request->input('sort-field', 'state');
            $sortOrder = $request->input('sort-order', 'asc');
            $limit = $request->input('limit', 10);

            $taxable = TaxByState::select('id', 'state', 'c_gst', 's_gst', 'i_gst', 'ut_gst')
                ->when($searchString, function ($query) use ($searchString) {
                    $query->where(function ($q) use ($searchString) {
                        $q->where('state', 'like', "%{$searchString}%")
                            ->orWhere('c_gst', 'like', "%{$searchString}%")
                            ->orWhere('s_gst', 'like', "%{$searchString}%")
                            ->orWhere('i_gst', 'like', "%{$searchString}%")
                            ->orWhere('ut_gst', 'like', "%{$searchString}%");
                    });
                })
                ->orderBy($sortField, $sortOrder)
                ->simplePaginate($limit);

            // Map and format response
           $taxable->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'state' => ucfirst($item->state),
                    'c_gst' => $item->c_gst,
                    's_gst' => $item->s_gst,
                    'i_gst' => $item->i_gst,
                    'ut_gst' => $item->ut_gst,
                ];
            });

            // Return success response
            return successResponse('',$taxable);

        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage(), 500);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Response
     */
    public function editTaxApi($id)
    {
        try {
            $tax = $this->tax->find($id);

            if (!$tax) {
                return errorResponse(__('message.tax_record_not_found'), 404);
            }

            $options = $this->tax_option->find(1);
            $tax = $this->tax->where('id', $id)->first();
            $taxClassName = $tax->taxClass()->find($tax->tax_classes_id)->name; //Find the Tax Class Name related to the tax
            $txClass = $this->tax_class->where('id', $tax->tax_classes_id)->first();
            $state = getStateByCode($tax->country, $tax->state);
            $states = findStateByRegionId($tax->country);
            $active = $tax->active;

            return successResponse( '', [
                'options'       => $options,
                'tax'           => $tax,
                'tax_class'     => $txClass,
                'tax_class_name'=> $taxClassName,
                'states'        => $states,
                'state'         => $state,
                'active'        => $active
            ]);

        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function updateTaxApi($id, Request $request)
    {
        try {
            $rules = [
                'name' => 'required',
                'tax_classes_id' => 'required'
            ];

            if ($request->tax_classes_id == 'Others') {
                $rules['rate'] = 'required|numeric';
            }

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return errorResponse($validator->errors()->first(), 422);
            }
            $taxClassesName = $request->tax_classes_id;
            $tax = $this->tax->find($id);
            if (!$tax) {
                return errorResponse(__('message.tax_not_found'), 404);
            }
            $taxClass = TaxClass::where('id', $tax->tax_classes_id)->first();
            if (! $taxClass) {
                $taxClass = $this->tax_class->create(['name' => $taxClassesName]);
            }
            $taxId = $taxClass->id;
            $tax->fill($request->except('tax_classes_id'))->save();

            $tax->where('id', $id)->update(['tax_classes_id' => $taxId]);
            if ($taxClassesName != 'Others') {
                $country = 'IN';
                $state = '';
                $rate = '';
                $tax->where('id', $id)
                ->update(['tax_classes_id' => $taxId, 'country' => $country, 'state' => $state, 'rate' => $rate]);
            }

            return successResponse(__('message.tax_updated_successfully'), [
                'tax' => $tax,
                'tax_class' => $taxClass
            ]);

        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Response
     */
    public function deleteTax(Request $request)
    {
        try {
            $ids = $request->input('select', []);

            $ids = array_filter(array_unique(array_map('intval', array_map('trim', $ids))));

            if (empty($ids)) {
                return errorResponse(__('message.select-a-row'), 400);
            }

            $foundAny = false;

            foreach ($ids as $id) {
                $tax = $this->tax->find($id);

                if ($tax) {
                    $foundAny = true;

                    $taxClass = $this->tax_class->find($tax->tax_classes_id);

                    if ($taxClass) {
                        $taxClass->tax_product_relation()->delete();
                        $taxClass->delete();
                    }

                    $tax->delete();
                }
            }

            if (! $foundAny) {
                return errorResponse(__('message.no-record'), 404);
            }

            return successResponse(__('message.deleted-successfully'));

        } catch (\Exception $e) {
            return errorResponse($e->getMessage(), 500);
        }
    }


    /**
     * @param  Request  $request
     * @param  type  $state
     * @return type
     */
    public function getState(Request $request, $stateid)
    {
        try {
            $id = $stateid;
            $states = \App\Model\Common\State::where('country_code', $id)
            ->orderBy('state_subdivision_name', 'asc')->get();
            echo '<option value="">'.__('message.choose').'</option>';
            foreach ($states as $state) {
                echo '<option value='.$state->iso2.'>'.$state->state_subdivision_name.'</option>';
            }

            return successResponse('', [
                'states' => $states
            ]);
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    public function saveTaxOptionSetting(Request $request)
    {
        try {
            $taxOption = $this->tax_option->find(1);

            if (!$taxOption) {
                return errorResponse(__('message.tax_option_not_found'), 404);
            }

            $taxOption->fill($request->all())->save();

            return successResponse(__('message.tax_settings_saved_successfully'));
        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */

    /**
     * @param  Request  $request
     * @return type
     */
    public function saveTaxClassSettingApi(Request $request)
    {
        try {
            $rules = [
                'name' => 'required',
                'tax-name' => 'required'
            ];

            if ($request->input('name') == 'Others') {
                $rules['rate'] = 'required|numeric';
            }

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return errorResponse($validator->errors()->first(), 422);
            }

            $taxClass = $this->tax_class->create([
                'name' => $request->input('name')
            ]);

            $country = $request->input('rate') ? $request->input('country') : 'IN';

            $tax = $this->tax->create([
                'name' => $request->input('tax-name'),
                'rate' => $request->input('rate') ?? '',
                'country' => $country,
                'state' => $request->input('state') ?? '',
                'tax_classes_id' => $taxClass->id,
            ]);

            return successResponse(__('message.created-successfully'), [
                'tax' => $tax,
                'tax_class' => $taxClass
            ]);

        } catch (\Exception $ex) {
            return errorResponse($ex->getMessage(), 500);
        }
    }
}
