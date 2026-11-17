<?php

namespace App\Http\Requests;

use App\Traits\RequestJsonValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class PlanRequest extends FormRequest
{
    use RequestJsonValidation;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required'],

            // Main array
            'currency' => ['required', 'array'],

            // Other arrays must match currency count
            'add_price' => ['required', 'array', 'array_size_equals:currency'],
            'renew_price' => ['required', 'array', 'array_size_equals:currency'],
            'offer_price' => ['nullable', 'array', 'array_size_equals:currency'],

            // Element-level checks
            'currency.*' => ['required_with:currency', 'distinct'],
            'add_price.*' => ['required_with:currency', 'integer', 'min:0', 'max:10000000'],
            'renew_price.*' => ['required_with:currency', 'integer', 'min:0', 'max:1000000'],
            'offer_price.*' => ['nullable', 'numeric', 'between:0,100'],

            'product' => ['required'],
            'days' => ['nullable', 'numeric'],
            'status' => [
                'required',
                Rule::unique('plans')
                    ->where(fn ($q) => $q
                        ->where('product', $this->product)
                        ->where('days', $this->days)
                        ->where('status', 1)
                    )
                    ->ignore($this->route('planId')),
            ],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'name.required' => __('validation.plan_request.name_required'),
            'product.required' => __('validation.plan_request.pro_req'),
            'add_price.*.max' => trans('message.regular_price_numeric'),
            'add_price.*.required_with' => trans('message.add_price_required'),
            'renew_price.*.required_with' => trans('message.renew_price_required'),
            'renew_price.*.numeric' => trans('message.renew_price_numeric'),
            'currency.*.required_with' => trans('message.currency_missing'),
            'currency.*.distinct' => __('validation.plan_request.currency_duplicate'),
            'offer_price.*.between' => __('validation.plan_request.offer_price'),
            'offer_price.*.numeric' => __('validation.plan_request.offer_price'),
            'status.unique' => __('message.active_plan_exists_simple'),
        ];
    }
}
