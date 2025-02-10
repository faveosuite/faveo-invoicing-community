<?php

namespace App\Http\Requests\Payment;

use App\Http\Requests\Request;

class PromotionRequest extends Request
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
        $rules = [
            'code' => 'required',
            'type' => 'required',
            'applied' => 'required',
            'uses' => 'required|numeric',
            'start' => 'required',
            'expiry' => 'required|after:start',
        ];
        // If 'type' is 'percentage', add additional validation for 'value'
        if ($this->input('type') === '1') {
            $rules['value'] = 'required|numeric|between:1,100';
        } else {
            $rules['value'] = 'required|numeric';
        }

        return $rules;
    }

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
        ];
    }
}
