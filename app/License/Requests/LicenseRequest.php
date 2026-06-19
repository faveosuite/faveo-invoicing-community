<?php

declare(strict_types=1);

namespace App\License\Requests;

use App\Traits\RequestJsonValidation;
use Illuminate\Foundation\Http\FormRequest;

class LicenseRequest extends FormRequest
{
    use RequestJsonValidation;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [

            'license_code' => ['nullable', 'string'],
            'product_id' => ['numeric'],
            'license_order_number' => ['numeric'],
            'license_require_domain' => ['boolean'],
            'license_limit' => ['numeric'],
            'license_date' => ['date'],
            'license_cancel_date' => ['date'],
            'license_expire_email_date' => ['date'],
            'license_updates_date' => ['date'],
            'license_updates_email_date' => ['date'],
            'license_support_email_date' => ['date'],
            'license_support_date' => ['date'],
            'license_status' => ['integer', 'in:0,1,2'],
        ];
    }

    /*public function messages()
    {
        return[
            'license_code.required' => 'Please Generate license code',
            'product_id.required' => 'Please select the product',
            'license_expire_date.required' => 'Please select License Expiration Date',
            'license_updates_date.required' => 'Please select License Update Date',
            'license_support_date.required' => 'Please select License support Date',
        ];
    }*/
}
