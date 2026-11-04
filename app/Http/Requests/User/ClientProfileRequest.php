<?php

namespace App\Http\Requests\User;

/**
 * Client panel: My Profile.
 */
class ClientProfileRequest extends ProfileRequest
{
    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'min:3', 'max:30'],
            'last_name' => ['required', 'max:30'],
            // Email & mobile are not saved here (OTP flow only), so not validated either.
            'company' => ['required', 'max:50'],
            'address' => ['required'],
            'country' => ['required', 'exists:countries,country_code_char2'],
            'profile_pic' => ['sometimes', 'mimes:jpeg,png,jpg', 'max:2048'],
            'timezone_id' => ['required', 'exists:timezone,id'],

        ];
    }
}
