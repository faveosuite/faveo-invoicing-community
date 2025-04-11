<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Common\StatusSetting;
use Illuminate\Http\Request;

class BaseMailChimpController extends Controller
{
    public function getLists()
    {
        try {
            $result = $this->mailchimp->request('lists');

            return $result;
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getListById()
    {
        try {
            $result = $this->mailchimp->request("lists/$this->list_id");

            return $result;
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    //Update to Mailchimp For Free Product
    public function updateSubscriberForFreeProduct($email, $productid)
    {
        try {
            $productGroupId = '';
            $interestGroupIdForNo = '';
            $interestGroupIdForYes = '';
            $merge_fields = $this->field($email);
            $hash = md5($email);
            $isPaidStatus = StatusSetting::select()->value('mailchimp_ispaid_status');
            $productStatusStatus = StatusSetting::select()->value('mailchimp_product_status');
            if ($isPaidStatus == 1) {
                $interestGroupIdForNo = $this->relation->is_paid_no; //Interest GroupId for IsPaid Is No
                $interestGroupIdForYes = $this->relation->is_paid_yes; //Interest GroupId for IsPaid Is Yes
            }
            if ($productStatusStatus == 1) {
                $productGroupId = $this->groupRelation->where('agora_product_id', $productid)
                ->pluck('mailchimp_group_cat_id')->first();
            }
            if ($interestGroupIdForNo && $productGroupId) {
                $result = $this->mailchimp->patch("lists/$this->list_id/members/$hash", [
                    'interests' => [$interestGroupIdForNo => true, $interestGroupIdForYes => false, $productGroupId => true],
                ]);
                //refer to https://us7.api.mailchimp.com/playground
            } elseif ($interestGroupIdForNo && $productGroupId == null) {
                $result = $this->mailchimp->patch("lists/$this->list_id/members/$hash", [
                    'interests' => [$interestGroupIdForNo => true, $interestGroupIdForYes => false],
                ]);
                //refer to https://us7.api.mailchimp.com/playground
            } elseif ($productGroupId && $interestGroupIdForNo == null || $interestGroupIdForYes == null) {
                $result = $this->mailchimp->patch("lists/$this->list_id/members/$hash", [
                    'interests' => [$productGroupId => true],
                ]);
            }
        } catch (\Exception $ex) {
            $exe = json_decode($ex->getMessage(), true);
        }
    }

    public function updateSubscriberForPaidProduct($email, $productid)
    {
        try {
            $merge_fields = $this->field($email);
            $hash = md5($email);
            $isPaidStatus = StatusSetting::select()->value('mailchimp_ispaid_status');
            $productStatusStatus = StatusSetting::select()->value('mailchimp_product_status');
            if ($isPaidStatus == 1) {
                $interestGroupIdForNo = $this->relation->is_paid_no; //Interest GroupId for IsPaid Is No
                $interestGroupIdForYes = $this->relation->is_paid_yes; //Interest GroupId for IsPaid Is Yes
            }
            if ($productStatusStatus == 1) {
                $productGroupId = $this->groupRelation->where('agora_product_id', $productid)
                ->pluck('mailchimp_group_cat_id')->first();
            }
            if ($interestGroupIdForNo && $productGroupId) {
                $result = $this->mailchimp->patch("lists/$this->list_id/members/$hash", [
                    'interests' => [$interestGroupIdForNo => false, $interestGroupIdForYes => true, $productGroupId => true],
                    //refer to https://us7.api.mailchimp.com/playground
                ]);
            } elseif ($interestGroupIdForNo && $productGroupId == null) {
                $result = $this->mailchimp->patch("lists/$this->list_id/members/$hash", [
                    'interests' => [$interestGroupIdForNo => false, $interestGroupIdForYes => true],
                ]);
                //refer to https://us7.api.mailchimp.com/playground
            } elseif ($productGroupId && $interestGroupIdForNo == null || $interestGroupIdForYes == null) {
                $result = $this->mailchimp->patch("lists/$this->list_id/members/$hash", [
                    'interests' => [$productGroupId => true],
                ]);
            }
            //refer to https://us7.api.mailchimp.com/playground
        } catch (\Exception $ex) {
        }
    }

    public function getMergeFields()
    {
        try {
            $result = $this->mailchimp->get("lists/$this->list_id/merge-fields?count=20");

            return $result;
        } catch (\Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function mailChimpSettings()
    {
        try {
            $set = $this->mailchimp_set;
            $allists = $this->mailchimp->get('lists?count=20')['lists'];
            $selectedList[] = $set->list_id;

            return view('themes.default1.common.mailchimp.settings', compact('set', 'allists', 'selectedList'));
        } catch (\Exception $ex) {
            return errorResponse(\Lang::get('message.mailchimp_apikey_error'));
        }
    }

    public function postMailChimpSettings(Request $request)
    {
        $this->validate($request, [
            'list_id' => 'required',
        ]);
        try {
            $this->mailchimp_set->first()->update(['subscribe_status' => $request->input('subscribe_status'),
                'list_id' => $request->input('list_id'), ]);
            $this->addListsToAgora();
            $data=['list_id' =>1];
            return successResponse(\Lang::get('message.github_valid'),$data);

        } catch (\Exception $ex) {
            return errorResponse(\Lang::get('message.mailchimp_apikey_error'));
        }
    }

    public function addListsToAgora()
    {
        try {
            $lists = $this->getLists();
            $agora_lists = $this->lists->get();
            if (count($agora_lists) > 0) {
                foreach ($agora_lists as $agora) {
                    $agora->delete();
                }
            }
            foreach ($lists['lists'] as $list) {
                $name = $list->name;
                $list_id = $list->id;
                $this->lists->create([
                    'name' => $name,
                    'list_id' => $list_id,
                ]);
            }
            //return redirect()->back()->with('success', \Lang::get('message.mailchimp-list-added-to-agora'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }
}
