<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\Request;

class BundleRequest extends Request
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
            'name' => 'required',
            'items.*' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('validation.bundle.name.required'),
            'items.*.required' => __('validation.bundle.items.required'),
        ];
    }
}
