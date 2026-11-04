<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use App\Traits\RequestJsonValidation;
use Override;

/**
 * Shared base for the user forms: authorisation, JSON error format and field messages.
 */
abstract class ProfileRequest extends Request
{
    use RequestJsonValidation;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rules for the endpoint; each subclass is one form (sign-up, profiles, password change).
     *
     * @return array<mixed>
     */
    abstract public function rules(): array;

    #[Override]
    public function messages()
    {
        return [
            'first_name.required' => __('validation.profile_form.first_name.required'),
            'first_name.min' => __('validation.profile_form.first_name.min'),
            'first_name.max' => __('validation.profile_form.first_name.max'),

            'last_name.required' => __('validation.profile_form.last_name.required'),
            'last_name.max' => __('validation.profile_form.last_name.max'),

            'company.required' => __('validation.profile_form.company.required'),
            'company.max' => __('validation.profile_form.company.max'),

            'email.required' => __('validation.profile_form.email.required'),
            'email.email' => __('validation.profile_form.email.email'),
            'email.unique' => __('validation.profile_form.email.unique'),

            'mobile.required' => __('validation.profile_form.mobile.required'),
            'mobile.regex' => __('validation.profile_form.mobile.regex'),
            'mobile.min' => __('validation.profile_form.mobile.min'),
            'mobile.max' => __('validation.profile_form.mobile.max'),

            'address.required' => __('validation.profile_form.address.required'),

            'user_name.required' => __('validation.profile_form.user_name.required'),
            'user_name.unique' => __('validation.profile_form.user_name.unique'),

            'timezone_id.required' => __('validation.profile_form.timezone_id.required'),
            'timezone_id.exists' => __('validation.profile_form.timezone_id.required'),

            'country.required' => __('validation.profile_form.country.required'),
            'country.exists' => __('validation.profile_form.country.exists'),

            'state.required' => __('validation.profile_form.state.required_if'),
            'gstin.regex' => __('validation.profile_form.gstin.regex'),

            'old_password.required' => __('validation.profile_form.old_password.required'),
            'old_password.min' => __('validation.profile_form.old_password.min'),

            'new_password.required' => __('validation.profile_form.new_password.required'),
            'new_password.different' => __('validation.profile_form.new_password.different'),

            'confirm_password.required' => __('validation.profile_form.confirm_password.required'),
            'confirm_password.same' => __('validation.profile_form.confirm_password.same'),

            'terms.required' => __('validation.profile_form.terms.required'),

            'password.required' => __('validation.profile_form.password.required'),
            'password_confirmation.required' => __('validation.profile_form.password_confirmation.required'),
            'password_confirmation.same' => __('validation.profile_form.password_confirmation.same'),

            'mobile.unique' => __('message.mobile_unique'),
            'profile_pic.mimes' => __('message.image_invalid_message'),
            'profile_pic.max' => __('message.image_max'),

            'mobile_code.required' => __('validation.profile_form.mobile_code.required'),
        ];
    }
}
