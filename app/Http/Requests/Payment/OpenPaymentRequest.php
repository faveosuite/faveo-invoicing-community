<?php

declare(strict_types=1);

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class OpenPaymentRequest extends FormRequest
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
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email'],
            'mobile' => ['required', 'string', 'min:8', 'max:20'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string'],
            'state' => ['required', 'string'],
            'zip' => ['required', 'string', 'max:15'],
            'country' => ['required', 'string'],
            'company' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['required', 'in:INR,USD'],
            'gateway' => ['required', 'in:Razorpay,Stripe'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    #[Override]
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter your name.',
            'name.max' => 'Name cannot exceed 100 characters.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'mobile.required' => 'Please enter your mobile number.',
            'mobile.min' => 'Mobile number must be at least 8 characters.',
            'mobile.max' => 'Mobile number cannot exceed 20 characters.',
            'address.required' => 'Please enter your address.',
            'city.required' => 'Please enter your city.',
            'state.required' => 'Please enter your state.',
            'zip.required' => 'Please enter your ZIP/postal code.',
            'zip.max' => 'ZIP code cannot exceed 15 characters.',
            'country.required' => 'Please select your country.',
            'company.required' => 'Please enter your company name.',
            'amount.required' => 'Please enter the payment amount.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least 1.',
            'currency.required' => 'Please select a currency.',
            'currency.in' => 'Currency must be either INR or USD.',
            'gateway.required' => 'Please select a payment gateway.',
            'gateway.in' => 'Gateway must be either Razorpay or Stripe.',
        ];
    }
}
