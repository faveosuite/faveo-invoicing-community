<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
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
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'agents'   => ['sometimes', 'integer', 'min:1'],
            'domain'   => ['nullable', 'string', 'max:255'],
        ];
    }
}
