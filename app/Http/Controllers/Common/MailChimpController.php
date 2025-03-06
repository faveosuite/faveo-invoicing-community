<?php

namespace App\Http\Controllers\Common;

use App\Model\Common\Country;
use App\Model\Common\Mailchimp\MailchimpField;
use App\Model\Common\Mailchimp\MailchimpFieldAgoraRelation;
use App\Model\Common\Mailchimp\MailchimpGroup;
use App\Model\Common\Mailchimp\MailchimpGroupAgoraRelation;
use App\Model\Common\Mailchimp\MailchimpLists;
use App\Model\Common\Mailchimp\MailchimpSetting;
use App\Model\Common\Setting;
use App\Model\Common\StatusSetting;
use App\Model\Product\Product;
use App\Rules\CaptchaValidation;
use App\User;
use Exception;
use Illuminate\Http\Request;

class MailChimpController extends BaseMailChimpController
{
    protected $mail_api_key;

    protected $mailchimp;

    protected $mailchimp_field_model;

    protected $mailchimp_set;

    protected $list_id;

    protected $lists;

    protected $relation;

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['addSubscriberByClientPanel']]);
        $mailchimp_set = new MailchimpSetting();
        $this->mailchimp_set = $mailchimp_set->firstOrFail();
        $this->mail_api_key = $this->mailchimp_set->api_key;
        $this->list_id = $this->mailchimp_set->list_id;
        $this->product_group_id = $this->mailchimp_set->group_id_products;
        $this->is_paid_group_id = $this->mailchimp_set->group_id_is_paid;

        $mailchimp_filed_model = new MailchimpField();
        $this->mailchimp_field_model = $mailchimp_filed_model;

        $lists = new MailchimpLists();
        $this->lists = $lists;

        $groups = new MailchimpGroup();
        $this->groups = $groups;

        $relation = new MailchimpFieldAgoraRelation();
        $this->relation = $relation->firstOrFail();

        $groupRelation = new MailchimpGroupAgoraRelation();
        $this->groupRelation = $groupRelation;

        $this->mailchimp = new \Mailchimp\Mailchimp($this->mail_api_key);
    }

    public function addSubscriber($user)
    {
        try {
            if (! is_array($user)) {
                $user = User::where('email', $user)->firstOrFail()->toArray();
            }
            // Assuming $user array contains 'first_name' and 'last_name'
            $merge_fields = [
                'FNAME' => $user['first_name'],
                'LNAME' => $user['last_name'],
            ];

            $interestGroupIdForNo = $this->relation->is_paid_no; // Interest GroupId for IsPaid Is No
            $interestGroupIdForYes = $this->relation->is_paid_yes; // Interest GroupId for IsPaid Is Yes

            $result = $this->mailchimp->post("lists/$this->list_id/members", [
                'status' => $this->mailchimp_set->subscribe_status,
                'email_address' => $user['email'],
                'merge_fields' => $merge_fields,
                // 'interests' => [$interestGroupIdForNo => true, $interestGroupIdForYes => false],
            ]);

            return $result;
        } catch (Exception $ex) {
            $exe = json_decode($ex->getMessage(), true);
            if ($exe['status'] == 400) {
                return errorResponse("$user[email] is already subscribed to the newsletter");
            }
        }
    }

    //Update to Mailchimp For Paid Product
    public function addSubscriberByClientPanel(Request $request)
    {
        $this->validate($request, [
            'newsletterEmail' => 'required|email',
            'g-recaptcha-response' => [isCaptchaRequired()['is_required'], new CaptchaValidation()],
        ], [
            'mailchimp-recaptcha-response-1.required' => 'Robot Verification Failed.',
        ]);

        try {
            $email = $request->input('newsletterEmail');
            $result = $this->mailchimp->post("lists/$this->list_id/members", [
                'status' => $this->mailchimp_set->subscribe_status,
                'email_address' => $email,

            ]);

            return successResponse('Email added to mailchimp');
        } catch (Exception $ex) {
            $message = $ex->getMessage();

            if (isJson($message)) {
                $exe = json_decode($message, true);

                if (isset($exe['status']) && $exe['status'] == 400) {
                    return errorResponse(trans('message.member_exist'));
                }

                return errorResponse($exe['detail'] ?? $message);
            }

            return errorResponse($message);
        }
    }

    public function field($email)
    {
        try {
            $user = new User();
            $setting = new Setting();
            $user = $user->where('email', $email)->first();
            $country = Country::where('country_code_char2', $user->country)->pluck('nicename')->first();
            if ($user) {
                $fields = ['first_name', 'last_name', 'company', 'mobile',
                    'address', 'town', 'country', 'state', 'zip', 'active', 'role', 'source', ];
                $relation = $this->relation;
                $merge_fields = [];
                foreach ($fields as $field) {
                    if ($relation->$field) {
                        $merge_fields[$relation->$field] = $user->$field;
                    }
                }
                $merge_fields[$relation->source] = $setting->findorFail(1)->title;

                return $merge_fields;
            } else {
                return redirect()->back()->with('fails', 'user not found');
            }
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function mapField()
    {
        try {
            $model = $this->relation;
            $model2 = $this->groupRelation;
            $this->addFieldsToAgora();

            $mailchimp_fields = $this->mailchimp_field_model
            ->where('list_id', $this->list_id)->pluck('name', 'tag')->toArray();
            $agoraProducts = Product::pluck('name', 'id')->toArray();

            $mailchimpProducts = $this->mailchimp->get("lists/$this->list_id/interest-categories");
            if ($mailchimpProducts['categories'] === []) {
                return redirect()->back()->with('fails', \Lang::get('message.mailchimp_group_error'));
            }
            $selectedProducts = MailchimpGroupAgoraRelation::select('agora_product_id', 'mailchimp_group_cat_id')->orderBy('id', 'asc')->get()->toArray();
            $allGroups = $this->mailchimp->get("lists/$this->list_id/interest-categories"); //Get all the groups(interest-categories for a list)

            foreach ($allGroups['categories']  as $key => $value) {
                $display[] = ['id' => $value->id, 'title' => $value->title];
            }
            $this->addProductInterestFieldsToAgora(); //add all the fields in Product Section of Groups to the db
            $group_fields = $this->groups->where('list_id', $this->list_id)
          ->select('category_name', 'category_option_id', 'category_id')->get()->toArray();
            // dd($group_fields[0]);
            $relations = MailchimpGroupAgoraRelation::where('id', '!=', 0)
          ->select('agora_product_id', 'mailchimp_group_cat_id')
          ->orderBy('id', 'asc')->get()->toArray();
            $productList = [];
            $categoryList = [];
            if (count($relations) != 0) {
                foreach ($relations as $key => $value) {
                    $categoryList[] = $this->groups->where('category_option_id', $value['mailchimp_group_cat_id'])->pluck('category_name')->first();
                    $productList[] = Product::where('id', $value['agora_product_id'])->pluck('name')->first();
                }
            }
            $isPaidYesId = MailchimpFieldAgoraRelation::first()->pluck('is_paid_yes')->toArray();
            $selectedIsPaid[] = $isPaidYesId ? MailchimpGroup::where('category_option_id', $isPaidYesId)->pluck('category_id')->first() : '';
            $status = StatusSetting::select('mailchimp_product_status', 'mailchimp_ispaid_status')->first();

            return view('themes.default1.common.mailchimp.map', compact('mailchimp_fields', 'model2', 'model', 'agoraProducts', 'display', 'selectedProducts', 'relations', 'group_fields', 'categoryList', 'productList', 'status', 'selectedIsPaid'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function addFieldsToAgora()
    {
        try {
            /** @scrutinizer ignore-call */
            $fields = $this->getMergeFields($this->list_id);
            $mailchimp_field_in_agora = $this->mailchimp_field_model->get();
            if (count($mailchimp_field_in_agora) > 0) {
                foreach ($mailchimp_field_in_agora as $field) {
                    $field->delete();
                }
            }
            foreach ($fields['merge_fields'] as $key => $value) {
                $merge_id = $value->merge_id;
                $name = $value->name;
                $type = $value->type;
                $required = $value->required;
                $list_id = $value->list_id;
                $tag = $value->tag;

                $this->mailchimp_field_model->create([
                    'merge_id' => $merge_id,
                    'tag' => $tag,
                    'name' => $name,
                    'type' => $type,
                    'required' => $required,
                    'list_id' => $list_id,
                ]);
            }
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function addInterestFieldsToAgora(Request $request, $value)
    {
        $groupInterests = $this->mailchimp->get("lists/$this->list_id/interest-categories/$value/interests?count=20");
        echo '<option value="">Choose a Category</option>';
        if (count($groupInterests) > 0) {
            foreach ($groupInterests['interests'] as $key => $value) {
                $fields[] = ['category_id' => $value->category_id,
                    'list_id' => $value->list_id,
                    'category_option_id' => $value->id,
                    'category_option_name' => $value->name,
                ];
            }
            foreach ($fields as $field) {
                $selectedCategory = MailchimpGroupAgoraRelation::where('mailchimp_group_cat_id', $field['category_option_id'])->pluck('mailchimp_group_cat_id')->first();
                echo '<option value='.$field['category_option_id'].'>'.$field['category_option_name'].'</option>';
            }
        }
    }

    public function addProductInterestFieldsToAgora()
    {
        $checkCategory = $this->mailchimp->get("lists/$this->list_id/interest-categories")['categories'];
        if (count($checkCategory) > 0) {
            foreach ($checkCategory as $interest) {
                $groupInterests = $this->mailchimp->get("lists/$this->list_id/interest-categories/$interest->id/interests?count=20");
                if (count($groupInterests['interests']) > 0) {
                    foreach ($groupInterests['interests'] as $key => $value) {
                        $category_id = $value->category_id;
                        $list_id = $value->list_id;
                        $category_option_id = $value->id;
                        $category_option_name = $value->name;
                        $this->groups->updateOrCreate([
                            'category_id' => $category_id,
                            'list_id' => $list_id,
                            'category_option_id' => $category_option_id,
                            'category_name' => $category_option_name,
                        ]);
                    }
                }
            }
        }
    }

    public function postMapField(Request $request)
    {
        try {
            $this->relation->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function postGroupMapField(Request $request)
    {
        try {
            MailchimpGroupAgoraRelation::where('id', '!=', 0)->delete();
            foreach ($request->row as $key => $value) {
                MailchimpGroupAgoraRelation::create(['agora_product_id' => $value[0],
                    'mailchimp_group_cat_id' => $value[1], ]);
            }

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function postIsPaidMapField(Request $request)
    {
        try {
            $group = $request->input('group');
            $groupInterests = $this->mailchimp->get("lists/$this->list_id/interest-categories/$group/interests?count=20")['interests'];
            foreach ($groupInterests as $interest) {
                //IS Paid Should be either Yes/No or True/False
                if ((strcasecmp($interest->name, 'Yes') == 0) || (strcasecmp($interest->name, 'True') == 0)) {
                    MailchimpFieldAgoraRelation::find(1)->update(['is_paid_yes' => $interest->id]);
                } elseif ((strcasecmp($interest->name, 'No') == 0) || (strcasecmp($interest->name, 'False') == 0)) {
                    MailchimpFieldAgoraRelation::find(1)->update(['is_paid_no' => $interest->id]);
                } else {
                    return redirect()->back()->with('fails', 'The Group Should have Dropdown with values either Yes/No or True/False');
                }
            }

            return redirect()->back()->with('success', 'Settings Updated Successfully');
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }
}
