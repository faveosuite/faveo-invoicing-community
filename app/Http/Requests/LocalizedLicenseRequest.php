<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LocalizedLicenseRequest extends FormRequest
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
        return [
            'domain' => 'required|url',
        ];
    }

    public function messages()
    {
        return [
            'domain.required' => __('validation.domain_form.domain.required'),
            'domain.url' => __('validation.domain_form.domain.url'),
        ];
    }
}
