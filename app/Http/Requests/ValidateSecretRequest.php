<?php

namespace App\Http\Requests;

use App\Rules\Honeypot;

class ValidateSecretRequest extends Request
{
    public function rules()
    {
        return [
            '2fa_code' => [new Honeypot()],
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
