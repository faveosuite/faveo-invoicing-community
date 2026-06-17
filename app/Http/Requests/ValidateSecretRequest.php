<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\Honeypot;
use Override;

class ValidateSecretRequest extends Request
{
    public function rules(): array
    {
        return [
            '2fa_code' => [new Honeypot()],
            'totp' => ['bail', 'required', 'digits:6'],
        ];
    }

    #[Override]
    public function messages()
    {
        return[
            'totp.required' => __('validation.validate_secret.totp.required'),
            'totp.digits' => __('validation.validate_secret.totp.digits'),
        ];
    }
}
