<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
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
            'product_id'    => ['required', 'integer', 'exists:products,id'],
            'plan_id'       => ['nullable', 'integer'],
            'quantity'      => ['sometimes', 'integer', 'min:1'],
            'agents'        => ['sometimes', 'integer', 'min:1'],
            'domain'        => ['nullable', 'string', 'max:255'],
            'billing_cycle' => ['sometimes', 'in:monthly,yearly,onetime'],
        ];
    }
}
