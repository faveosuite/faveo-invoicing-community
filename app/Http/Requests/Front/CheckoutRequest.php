<?php

declare(strict_types=1);

namespace App\Http\Requests\Front;

use App\Http\Requests\Request;
use App\Traits\RequestJsonValidation;
use Override;

class CheckoutRequest extends Request
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
     *
     * @return array<mixed>
     */
    public function rules(): array
    {
        if ($this->method() == 'POST') {
            return [
                'first_name' => ['required'],
                'last_name' => ['required'],
                'company' => ['required'],
                'mobile' => ['regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/'],
                'address' => ['required'],
                'zip' => ['required', 'min:5', 'numeric'],
                'email' => ['required', 'email', 'unique:users,email'],
            ];
        }

        if ($this->method() == 'PATCH') {
            return [
                'first_name' => ['required'],
                'last_name' => ['required'],
                'company' => ['required'],
                'mobile' => ['regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/'],
                'address' => ['required'],
                'zip' => ['required', 'min:5', 'numeric'],
                'email' => ['required', 'email'],
            ];
        }

        return [];
    }

    #[Override]
    public function messages()
    {
        return [
            'payment_gatway.required' => __('message.choose_one_payment_gateway'),
            'first_name.required' => __('validation.customer_form.first_name.required'),
            'last_name.required' => __('validation.customer_form.last_name.required'),
            'company.required' => __('validation.customer_form.company.required'),
            'mobile.regex' => __('validation.customer_form.mobile.regex'),
            'address.required' => __('validation.customer_form.address.required'),
            'zip.required' => __('validation.customer_form.zip.required'),
            'zip.min' => __('validation.customer_form.zip.min'),
            'zip.numeric' => __('validation.customer_form.zip.numeric'),
            'email.required' => __('validation.customer_form.email.required'),
            'email.email' => __('validation.customer_form.email.email'),
            'email.unique' => __('validation.customer_form.email.unique'),
        ];
    }
}
