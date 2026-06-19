<?php

declare(strict_types=1);

namespace App\Plugins\Mailchimp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'mailchimp_auth_key' => ['required', 'string', 'min:10'],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'mailchimp_auth_key.required' => __('message.mailchimp_apikey_error'),
            'mailchimp_auth_key.min' => __('message.mailchimp_apikey_error'),
        ];
    }
}
