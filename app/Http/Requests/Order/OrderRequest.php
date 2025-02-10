<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\Request;

class OrderRequest extends Request
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
        return [
            'client' => 'required',
            'payment_method' => 'required',
            'promotion_code' => 'required',
            'order_status' => 'required',
            'product' => 'required',
            //'domain'         => 'url',
            'subscription' => 'required',
            'price_override' => 'numeric',
            'qty' => 'integer',
        ];
    }

    public function messages()
    {
        return [
            'price_override.numeric' => __('validation.price_numeric_value'),
            'qty.integer' => __('validation.quantity_integer_value'),
            'client.required' => __('validation.order_form.client.required'),
            'payment_method.required' => __('validation.order_form.payment_method.required'),
            'promotion_code.required' => __('validation.order_form.promotion_code.required'),
            'order_status.required' => __('validation.order_form.order_status.required'),
            'product.required' => __('validation.order_form.product.required'),
            'subscription.required' => __('validation.order_form.subscription.required'),
        ];
    }
}
