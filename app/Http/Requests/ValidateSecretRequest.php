<?php

namespace App\Http\Requests;

class ValidateSecretRequest extends Request
{
    public function rules()
    {
        return [
            'totp' => 'bail|required|digits:6',
        ];
    }

    public function messages()
    {
        return[
            'totp.required' => __('validation.validate_secret.totp.required'),
            'totp.digits' => __('validation.validate_secret.totp.digits'),
        ];
    }
}
