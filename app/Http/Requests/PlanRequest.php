<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlanRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required',

            // Main array
            'currency' => 'required|array',

            // Other arrays must match currency count
            'add_price' => 'required|array|array_size_equals:currency',
            'renew_price' => 'required|array|array_size_equals:currency',
            'offer_price' => 'nullable|array|array_size_equals:currency',

            // Element-level checks
            'currency.*' => 'required_with:currency',
            'add_price.*' => 'required_with:currency|integer|min:0|max:10000000',
            'renew_price.*' => 'required_with:currency|integer|min:0|max:1000000',
            'offer_price.*' => ['nullable', 'numeric', 'between:0,100'],

            'product' => 'required',
            'days' => 'nullable|numeric',
            'product_quantity' => 'required_without:no_of_agents|integer|min:0',
            'no_of_agents' => 'required_without:product_quantity|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.plan_request.name_required'),
            'product_quantity.required_without' => __('validation.plan_request.product_quant_req'),
            'no_of_agents.required_without' => __('validation.plan_request.no_agent_req'),
            'product.required' => __('validation.plan_request.pro_req'),
            'add_price.*.max' => trans('message.regular_price_numeric'),
            'add_price.*.required_with' => trans('message.add_price_required'),
            'renew_price.*.required_with' => trans('message.renew_price_required'),
            'renew_price.*.numeric' => trans('message.renew_price_numeric'),
            'currency.*.required_with' => trans('message.currency_missing'),
            'offer_price.*.between' => __('validation.plan_request.offer_price'),
            'offer_price.*.numeric' => __('validation.plan_request.offer_price'),
        ];
    }
}
