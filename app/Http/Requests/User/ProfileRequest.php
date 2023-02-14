<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

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

        $value = $this->input('value');
        

        if ($this->segment(1) == 'profile') {
            $userid = \Auth::user()->id;

            return [
                'first_name'             => 'required',
                'last_name'              => 'required',
                'email'                  => 'required',
                'mobile'                 => 'required',
                'user_name'              => 'unique:users,user_name,'.$userid,
                'timezone_id'            => 'required',
                'profile_pic'            => 'sometimes|mimes:jpeg,jpg,png,gif|max:100000',

            ];
        }

        if ($this->segment(1) == 'my-profile') {
            $userid = \Auth::user()->id;

            return [
                'first_name'            => 'required|min:3|max:30',
                'last_name'              => 'required|max:30',
                'mobile'                => 'required|regex:/[0-9]/|min:5|max:20',
                'email'                 => 'required',
                'mobile'                 => 'required',
                'country'                => 'required|exists:countries,country_code_char2',
                'profile_pic'            => 'sometimes|mimes:jpeg,jpg,png,gif|max:100000',

            ];
        }
        if ($this->segment(1) == 'password' || $this->segment(1) == 'my-password') {
            return [
                'old_password'     => 'required|min:6',
                'new_password'     => 'required|min:6',
                'confirm_password' => 'required|same:new_password',
            ];
        }

        if ($this->segment(1) == 'auth' && $value == '0') {
            return [
                'first_name'            => 'required|min:2|max:30',
                'last_name'             => 'required|max:30',

                'email'                 => 'required|email|unique:users',

                'mobile'                => 'required',
                'terms'                 => 'accepted',
                'country'               => 'required|exists:countries,country_code_char2',
            ];
           
        }
         if ($this->segment(1) == 'auth' && $value == '1') {
            return [
                'first_name'            => 'required|min:2|max:30',
                'last_name'             => 'required|max:30',
                'email'                 => 'required|email|unique:users',
                'mobile'                => 'required',
                'logterms'                 => 'accepted',
                'country'               => 'required|exists:countries,country_code_char2',
            ];
           
        }
    }

    public function messages()
    {
        return[
            // 'mobile_code.required'           => 'Enter Country code (mobile)',
        ];
    }
}
