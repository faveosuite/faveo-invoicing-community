<?php

declare(strict_types=1);

namespace App\Http\Requests\Payment;

use App\Traits\RequestJsonValidation;
use App\Http\Requests\Request;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\Price;
use Override;

class PromotionRequest extends Request
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
        $rules = [
            'code' => 'required',
            'type' => 'required|exists:promotion_types,id',
            'applied' => 'required',
            'uses' => 'required|numeric',
            'start' => 'required',
            'expiry' => 'required|after:start',
        ];

        // Type 1 = Percentage, 2 = Fixed Amount. Cast for comparison: JSON requests
        // send this as a number (int 1), not the string '1' — a strict '===' here
        // used to silently skip the percentage bounds check entirely.
        if ((string) $this->input('type') === '1') {
            $rules['value'] = 'required|numeric|between:1,100';
        } else {
            $rules['value'] = ['required', 'integer', 'min:0'];

            // Fixed Amount: cap at the applied product's highest known price
            // (direct product pricing or any of its plans, any currency) so a
            // discount can never exceed what the product could ever cost.
            if ((string) $this->input('type') === '2') {
                $maxPrice = $this->maxProductPrice((int) $this->input('applied'));
                if ($maxPrice > 0) {
                    $rules['value'][] = 'max:'.(int) $maxPrice;
                }
            }
        }

        return $rules;
    }

    private function maxProductPrice(int $productId): float
    {
        // price/add_price are string columns and not always clean numerics
        // (e.g. "999/ Issue") — casting in PHP after fetching avoids MySQL's
        // MAX() sorting them lexicographically instead of numerically.
        $planIds = Plan::where('product', $productId)->pluck('id');

        return Price::where('product_id', $productId)->pluck('price')
            ->merge(PlanPrice::whereIn('plan_id', $planIds)->pluck('add_price'))
            ->map(fn ($v) => (float) $v)
            ->max() ?? 0.0;
    }

    #[Override]
    public function messages()
    {
        return [
            'code.required' => __('validation.coupon_form.code.required'),
            'code.string' => __('validation.coupon_form.code.string'),
            'code.max' => __('validation.coupon_form.code.max'),
            'type.required' => __('validation.coupon_form.type.required'),
            'type.in' => __('validation.coupon_form.type.in'),
            'applied.required' => __('validation.coupon_form.applied.required'),
            'applied.date' => __('validation.coupon_form.applied.date'),
            'uses.required' => __('validation.coupon_form.uses.required'),
            'uses.numeric' => __('validation.coupon_form.uses.numeric'),
            'uses.min' => __('validation.coupon_form.uses.min'),
            'start.required' => __('validation.coupon_form.start.required'),
            'start.date' => __('validation.coupon_form.start.date'),
            'expiry.required' => __('validation.coupon_form.expiry.required'),
            'expiry.date' => __('validation.coupon_form.expiry.date'),
            'expiry.after' => __('validation.coupon_form.expiry.after'),
            'value.required' => __('validation.coupon_form.value.required'),
            'value.numeric' => __('validation.coupon_form.value.numeric'),
            'value.between' => __('validation.coupon_form.value.between'),
            'value.max' => __('validation.coupon_form.value.max'),
        ];
    }
}
