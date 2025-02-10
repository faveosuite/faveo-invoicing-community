<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRenewalRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'domain' => 'required|no_http',

        ];
    }

    public function messages()
    {
        return [
            'domain.required' => __('validation.product_renewal.domain.required'),
            'domain.no_http' => __('validation.product_renewal.domain.no_http'),
        ];
    }
}
