<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\Request;

class GroupRequest extends Request
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
            // 'features.*.name' => 'required',
            // 'title'           => 'required_with:type,price,value',
            // 'type'            => 'required_with:title,price,value',
            // 'price.*.name'    => 'required_unless:type,1|numeric',
            // 'price.*.name'    => 'required_unless:type,2|numeric',
            // 'value.*.name'    => 'required_unless:type,1',
            // 'value.*.name'    => 'required_unless:type,2',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('validation.group.name.required'),
            'features.*.name.required' => __('validation.group.features.name.required'),
            'price.*.name.required_unless' => __('validation.group.price.name.required_unless'),
            'value.*.name.required_unless' => __('validation.group.value.name.required_unless'),
            'type.required_with' => __('validation.group.type.required_with'),
            'title.required_with' => __('validation.group.title.required_with'),
        ];
    }
}
