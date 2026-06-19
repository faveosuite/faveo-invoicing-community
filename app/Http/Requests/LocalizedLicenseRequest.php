<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class LocalizedLicenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'domain' => ['required', 'url'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'domain.required' => __('validation.domain_form.domain.required'),
            'domain.url' => __('validation.domain_form.domain.url'),
        ];
    }
}
