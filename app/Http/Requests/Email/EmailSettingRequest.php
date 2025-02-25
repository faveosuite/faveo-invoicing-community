<?php

namespace App\Http\Requests\Email;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Lang;
class EmailSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if(in_array($this->driver,['smtp','mailgun','mandrill','ses','sparkpost'])){
            return [
                'password' => 'required_if:driver,smtp,mandrill,ses,sparkpost',
                'port' => 'required_if:driver,smtp',
                'encryption' => 'required_if:driver,smtp',
                'host' => 'required_if:driver,smtp',
                'secret' => 'required_if:driver,mailgun,mandrill,ses,sparkpost',
                'domain' => 'required_if:driver,mailgun',
                'key' => 'required_if:driver,ses',
                'region' => 'required_if:driver,ses',
                'email' => 'required_if:driver,smtp,mailgun,mandrill,ses',
            ];
        } else {
            return [
                'driver' => 'required',
                'email' => [
                    'required',
                    'email',
                    function ($attribute, $value, $fail) {
                        $emailDomain = explode('@', $value)[1];
                        $url = \Request::url();
                        $domain = parse_url($url);
                        if (strcasecmp($domain['host'], $emailDomain) !== 0) {
                            return $fail(Lang::get('message.email_not_matching'));
                        }
                    },
                ],
            ];
        }
    }
}
