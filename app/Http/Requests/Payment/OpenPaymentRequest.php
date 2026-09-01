<?php

declare(strict_types=1);

namespace App\Http\Requests\Payment;

use App\Model\Payment\Currency;
use App\Traits\RequestJsonValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class OpenPaymentRequest extends FormRequest
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
            // Column is decimal(10,2) — 99999999.99 is the hard ceiling, and the
            // stored total is base+fee, not just base. Capping base at half that
            // leaves room for the fee (a small admin-configured %, currently
            // 2-2.5%, but not itself bounded) to never push the total over.
            // Without this cap, MySQL's non-strict sql_mode here silently clamps
            // an out-of-range decimal to the column max instead of erroring, so
            // base_amount/amount end up wrong while the separately-computed
            // processing_fee (still in range) stays correct — a breakdown that
            // doesn't add up.
            'amount' => ['required', 'numeric', 'min:1', 'max:50000000'],
            // Must match whatever /pay/config actually offers the frontend
            // (every admin-enabled currency), not a fixed pair — otherwise a
            // currency the dropdown lets you pick gets rejected here.
            'currency' => ['required', Rule::in(Currency::where('status', 1)->pluck('code'))],
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
            'amount.max' => 'Amount cannot exceed 50,000,000.',
            'currency.required' => 'Please select a currency.',
            'currency.in' => 'Please select a supported currency.',
            'gateway.required' => 'Please select a payment gateway.',
            'gateway.in' => 'Gateway must be either Razorpay or Stripe.',
        ];
    }
}
