<?php

namespace App\Http\Requests\Payment;

use App\Http\Requests\Request;

class TaxRequest extends Request
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
            'rate' => 'required|numeric',
            'level' => 'required|integer',
            'country' => 'required',
            'state' => 'required',
            // 'country' => 'exists:countries,country_id',
            // 'state'   => 'exists:states,state_subdivision_id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('validation.tax_form.name.required'),
            'rate.required' => __('validation.tax_form.rate.required'),
            'rate.numeric' => __('validation.tax_form.rate.numeric'),
            'level.required' => __('validation.tax_form.level.required'),
            'level.integer' => __('validation.tax_form.level.integer'),
            'country.required' => __('validation.tax_form.country.required'),
            'state.required' => __('validation.tax_form.state.required'),
            // Optional if you enable exists validation
            // 'country.exists' => __('validation.tax_form.country.exists'),
            // 'state.exists' => __('validation.tax_form.state.exists'),
        ];
    }
}
