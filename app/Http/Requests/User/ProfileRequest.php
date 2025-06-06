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
                'state' => 'required',
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
                'state' => 'required_if:country,IN',
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
        return[
            'new_password.different' => \Lang::get('message.new_password_different'),
            'mobile_code.required' => 'Enter Country code (mobile)',
            'state.required_if' => 'The state field is required when country is India.',
            'email.unique' => 'The email address has already been taken. Please choose a different email.',
            'profile_pic.mimes' => __('message.image_allowed'),
            'profile_pic.max' => __('message.image_max'),
        ];
    }
}
