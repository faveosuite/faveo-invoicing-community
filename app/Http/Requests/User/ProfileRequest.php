<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;
use App\Rules\StrongPassword;

class ProfileRequest extends Request
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
        if ($this->segment(1) == 'profile') {
            $userid = \Auth::user()->id;

            return [
                'first_name' => 'required',
                'last_name' => 'required',
                'company' => 'required|max:50',
                'email' => 'required',
                'mobile' => 'required',
                'address' => 'required',
                'user_name' => 'required|unique:users,user_name,'.$userid,
                'timezone_id' => 'required',
                'profile_pic' => 'sometimes|mimes:jpeg,png,jpg|max:2048',
                'country' => 'required',
            ];
        }

        if ($this->segment(1) == 'my-profile') {
            $userid = \Auth::user()->id;

            return [
                'first_name' => 'required|min:3|max:30',
                'last_name' => 'required|max:30',
                'mobile' => 'required|regex:/[0-9]/|min:5|max:20',
                'email' => 'required|email|unique:users,email,'.$userid,
                'company' => 'required|max:50',
                'address' => 'required',
                'country' => 'required|exists:countries,country_code_char2',
                'profile_pic' => 'sometimes|mimes:jpeg,png,jpg|max:2048',

            ];
        }
        if ($this->segment(1) == 'password' || $this->segment(1) == 'my-password') {
            return [
                'old_password' => 'required|min:6',
                'new_password' => [
                    'required',
                    new StrongPassword(),
                    'different:old_password',
                ],
                'confirm_password' => 'required|same:new_password',
            ];
        }

        if ($this->segment(1) == 'auth') {
            return [
                'first_name' => 'required|min:2|max:30',
                'last_name' => 'required|max:30',
                'email' => 'required|email|unique:users',
                'company' => 'required|max:50',
                'mobile' => 'required',
                'address' => 'required',
                'terms' => 'sometimes',
                'password' => [
                    'required',
                    new StrongPassword(),
                ],
                'password_confirmation' => 'required|same:password',
                // 'country'               => 'required|exists:countries,country_code_char2',
            ];
        }
    }

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

            'country.required' => __('validation.profile_form.country.required'),
            'country.exists' => __('validation.profile_form.country.exists'),

            'state.required_if' => __('validation.profile_form.state.required_if'),

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

            'profile_pic.mimes' => __('message.image_allowed'),
            'profile_pic.max' => __('message.image_max'),

            'mobile_code.required' => __('validation.profile_form.mobile_code.required'),
        ];
    }
}
