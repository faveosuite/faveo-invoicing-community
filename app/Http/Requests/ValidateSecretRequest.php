<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\Honeypot;
use App\Traits\RequestJsonValidation;
use Override;

class ValidateSecretRequest extends Request
{
    use RequestJsonValidation;

    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'honeypot' => [new Honeypot],
            // The 6-digit authenticator-app code.
            'code' => ['bail', 'required', 'digits:6'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'code.required' => __('validation.validate_secret.totp.required'),
            'code.digits' => __('validation.validate_secret.totp.digits'),
        ];
    }
}
