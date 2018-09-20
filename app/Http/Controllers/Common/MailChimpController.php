<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Model\Common\Mailchimp\MailchimpField;
use App\Model\Common\Mailchimp\MailchimpFieldAgoraRelation;
use App\Model\Common\Mailchimp\MailchimpLists;
use App\Model\Common\Mailchimp\MailchimpSetting;
use App\User;
use Exception;
use Illuminate\Http\Request;

class MailChimpController extends Controller
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
        $mailchimp_set = new MailchimpSetting();
        $this->mailchimp_set = $mailchimp_set->firstOrFail();
        $this->mail_api_key = $this->mailchimp_set->api_key;
        $this->list_id = $this->mailchimp_set->list_id;

        $mailchimp_filed_model = new MailchimpField();
        $this->mailchimp_field_model = $mailchimp_filed_model;

        $lists = new MailchimpLists();
        $this->lists = $lists;

        $relation = new MailchimpFieldAgoraRelation();
        $this->relation = $relation->firstOrFail();

        $this->mailchimp = new \Mailchimp\Mailchimp($this->mail_api_key);
    }

    public function getLists()
    {
        try {
            $result = $this->mailchimp->request('lists');

            return $result;
        } catch (Exception $ex) {
            dd($ex);

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

    public function addSubscriber($email)
    {
        try {
            $merge_fields = $this->field($email);
            $result = $this->mailchimp->post("lists/$this->list_id/members", [
                'status'        => $this->mailchimp_set->subscribe_status,
                'email_address' => $email,
                'merge_fields'  => $merge_fields,

            ]);

            return $result;
        } catch (Exception $ex) {
            $exe = json_decode($ex->getMessage(), true);
            if ($exe['status'] == 400) {
                throw new Exception("$email is already subscribed to newsletter", 400);
            }
        }
    }

    public function addSubscriberByClientPanel(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        try {
            $email = $request->input('email');
            $result = $this->mailchimp->post("lists/$this->list_id/members", [
                'status'        => $this->mailchimp_set->subscribe_status,
                'email_address' => $email,

            ]);

            return redirect()->back()->with('success', 'email added to mailchimp');
        } catch (Exception $ex) {
            $exe = json_decode($ex->getMessage(), true);
            if ($exe['status'] == 400) {
                $error = "$email is already subscribed to newsletter";

                return redirect()->back()->with('warning', $error);
            }

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function field($email)
    {
        try {
            $user = new User();
            $user = $user->where('email', $email)->first();
            if ($user) {
                $fields = ['first_name', 'last_name', 'company', 'mobile',
                 'address', 'town', 'state', 'zip', 'active', 'role', ];
                $relation = $this->relation;
                $merge_fields = [];
                foreach ($fields as $field) {
                    if ($relation->$field) {
                        $merge_fields[$relation->$field] = $user->$field;
                    }
                }

                return $merge_fields;
            } else {
                return redirect()->back()->with('fails', 'user not found');
            }
        } catch (Exception $ex) {
            //dd($ex);
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function getMergeFields()
    {
        try {
            $result = $this->mailchimp->get("lists/$this->list_id/merge-fields");

            return $result;
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
                    'tag'      => $tag,
                    'name'     => $name,
                    'type'     => $type,
                    'required' => $required,
                    'list_id'  => $list_id,
                ]);
            }
        } catch (Exception $ex) {
            dd($ex);

            return redirect()->back()->with('fails', $ex->getMessage());
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
                    'name'    => $name,
                    'list_id' => $list_id,
                ]);
            }
            //return redirect()->back()->with('success', \Lang::get('message.mailchimp-list-added-to-agora'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function mailChimpSettings()
    {
        try {
            $set = $this->mailchimp_set;
            $lists = $this->lists->pluck('name', 'list_id')->toArray();

            return view('themes.default1.common.mailchimp.settings', compact('set', 'lists'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function postMailChimpSettings(Request $request)
    {
        $this->validate($request, [
            'api_key' => 'required',
            //'list_id'=>'required',
        ]);

        try {
            $this->mailchimp_set->fill($request->input())->save();
            $this->addListsToAgora();

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (Exception $ex) {
            dd($ex);

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function mapField()
    {
        try {
            $model = $this->relation;
            $this->addFieldsToAgora();
            $mailchimp_fields = $this->mailchimp_field_model
            ->where('list_id', $this->list_id)->pluck('name', 'tag')->toArray();

            return view('themes.default1.common.mailchimp.map', compact('mailchimp_fields', 'model'));
        } catch (Exception $ex) {
            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }

    public function postMapField(Request $request)
    {
        try {
            $this->relation->fill($request->input())->save();

            return redirect()->back()->with('success', \Lang::get('message.updated-successfully'));
        } catch (Exception $ex) {
            dd($ex);

            return redirect()->back()->with('fails', $ex->getMessage());
        }
    }
}
