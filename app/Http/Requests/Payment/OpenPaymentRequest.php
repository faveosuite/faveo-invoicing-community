<?php

declare(strict_types=1);

namespace App\Http\Requests\Payment;

use App\Model\Payment\Currency;
use App\Rules\PhoneNumber;
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
            'mobile' => ['required', 'string', 'min:8', 'max:20', new PhoneNumber($this->country)],
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
            'name.required' => __('message.open_payment_name_required'),
            'name.max' => __('message.open_payment_name_max'),
            'email.required' => __('message.open_payment_email_required'),
            'email.email' => __('message.open_payment_email_invalid'),
            'mobile.required' => __('message.open_payment_mobile_required'),
            'mobile.min' => __('message.open_payment_mobile_min'),
            'mobile.max' => __('message.open_payment_mobile_max'),
            'address.required' => __('message.open_payment_address_required'),
            'city.required' => __('message.open_payment_city_required'),
            'state.required' => __('message.open_payment_state_required'),
            'zip.required' => __('message.open_payment_zip_required'),
            'zip.max' => __('message.open_payment_zip_max'),
            'country.required' => __('message.open_payment_country_required'),
            'company.required' => __('message.open_payment_company_required'),
            'amount.required' => __('message.open_payment_amount_required'),
            'amount.numeric' => __('message.open_payment_amount_numeric'),
            'amount.min' => __('message.open_payment_amount_min'),
            'amount.max' => __('message.open_payment_amount_max'),
            'currency.required' => __('message.open_payment_currency_required'),
            'currency.in' => __('message.open_payment_currency_invalid'),
            'gateway.required' => __('message.open_payment_gateway_required'),
            'gateway.in' => __('message.open_payment_gateway_invalid'),
        ];
    }
}
