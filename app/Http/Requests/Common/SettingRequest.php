<?php

declare(strict_types=1);

namespace App\Http\Requests\Common;

use App\Http\Requests\Request;
use Override;

class SettingRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'company' => ['required'],
            'website' => ['url'],
            'phone' => ['regex:/\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/'],
            'address' => ['required', 'max:300'],
            'logo' => ['mimes:png'],
            'driver' => ['required'],
            'port' => ['integer'],
            'email' => ['required', 'email', 'unique:users,email', 'unique:users,user_name'],
            'password' => ['required'],
            'error_email' => ['email'],

        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'company.required' => __('validation.settings_form.company.required'),
            'website.url' => __('validation.settings_form.website.url'),
            'phone.regex' => __('validation.settings_form.phone.regex'),
            'address.required' => __('validation.settings_form.address.required'),
            'address.max' => __('validation.settings_form.address.max'),
            'logo.mimes' => __('validation.settings_form.logo.mimes'),
            'driver.required' => __('validation.settings_form.driver.required'),
            'port.integer' => __('validation.settings_form.port.integer'),
            'email.required' => __('validation.settings_form.email.required'),
            'email.email' => __('validation.settings_form.email.email'),
            'email.unique' => __('validation.profile_form.email.unique'),
            'password.required' => __('validation.settings_form.password.required'),
            'error_email.email' => __('validation.settings_form.error_email.email'),
        ];
    }
}
