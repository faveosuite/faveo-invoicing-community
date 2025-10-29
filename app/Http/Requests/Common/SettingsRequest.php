<?php

namespace App\Http\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $regex = '/^(https?:\/\/)?([\w-]+\.)+([a-z]{2,6})(\/[\w-]*)*(\?.*)?(#.*)?$/i';

        return [
            'company' => 'required|max:50',
            'company_email' => 'required|email',
            'title' => 'max:50',
            'website' => 'required|url|regex:'.$regex,
            'phone' => 'required',
            'address' => 'required',
            'state' => 'required',
            'country' => 'required',
            'gstin' => 'max:15',
            'default_currency' => 'required',
            'admin-logo' => 'sometimes|mimes:jpeg,png,jpg|max:2048',
            'fav-icon' => 'sometimes|mimes:jpeg,png,jpg|max:2048',
            'logo' => 'sometimes|mimes:jpeg,png,jpg|max:2048',
            'autorenewal_status' => 'sometimes',
        ];
    }

    public function messages()
    {
        return [
            'company.required' => __('validation.settings_forms.company.required'),
            'company.max' => __('validation.settings_forms.company.max'),

            'company_email.required' => __('validation.settings_forms.company_email.required'),
            'company_email.email' => __('validation.settings_forms.company_email.email'),

            'title.max' => __('validation.settings_forms.title.max'),

            'website.required' => __('validation.settings_forms.website.required'),
            'website.url' => __('validation.settings_forms.website.url'),
            'website.regex' => __('validation.settings_forms.website.regex'),

            'phone.required' => __('validation.settings_forms.phone.required'),
            'address.required' => __('validation.settings_forms.address.required'),
            'state.required' => __('validation.settings_forms.state.required'),
            'country.required' => __('validation.settings_forms.country.required'),

            'gstin.max' => __('validation.settings_forms.gstin.max'),

            'default_currency.required' => __('validation.settings_forms.default_currency.required'),

            'admin-logo.mimes' => __('validation.settings_forms.admin_logo.mimes'),
            'admin-logo.max' => __('validation.settings_forms.admin_logo.max'),

            'fav-icon.mimes' => __('validation.settings_forms.fav_icon.mimes'),
            'fav-icon.max' => __('validation.settings_forms.fav_icon.max'),

            'logo.mimes' => __('validation.settings_forms.logo.mimes'),
            'logo.max' => __('validation.settings_forms.logo.max'),
        ];
    }
}
