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
        if (in_array($this->driver, ['smtp', 'mailgun', 'mandrill', 'ses', 'sparkpost'])) {
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

    public function messages()
    {
        return [
            'password.required_if' => __('validation.custom.password.required_if'),
            'port.required_if' => __('validation.custom.port.required_if'),
            'encryption.required_if' => __('validation.custom.encryption.required_if'),
            'host.required_if' => __('validation.custom.host.required_if'),
            'secret.required_if' => __('validation.custom.secret.required_if'),
            'domain.required_if' => __('validation.custom.domain.required_if'),
            'key.required_if' => __('validation.custom.key.required_if'),
            'region.required_if' => __('validation.custom.region.required_if'),
            'email.required_if' => __('validation.custom.email.required_if'),
            'driver.required' => __('validation.custom.driver.required'),
            'email.required' => __('validation.custom.email.required'),
            'email.email' => __('validation.custom.email.email'),
        ];
    }
}
