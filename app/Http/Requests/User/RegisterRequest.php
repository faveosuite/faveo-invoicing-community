<?php

namespace App\Http\Requests\User;

use App\Rules\PhoneNumber;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Sign-up form (POST auth/register).
 */
class RegisterRequest extends ProfileRequest
{
    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'min:2', 'max:30'],
            'last_name' => ['required', 'max:30'],
            'email' => ['required', 'email', 'unique:users', 'unique:users,user_name', 'unique:settings,email', 'unique:settings,company_email'],
            // Company/address/country aren't asked at sign-up (collected later); still validated if sent.
            'company' => ['nullable', 'max:50'],
            'mobile' => ['required', 'unique:users', new PhoneNumber($this->mobile_country_iso)],
            'address' => ['nullable', 'string', 'regex:/^[^<>]*$/'],
            'terms' => ['sometimes'],
            'password' => [
                'required',
                PasswordRule::defaults(),
            ],
            'password_confirmation' => ['required', 'same:password'],
        ];
    }
}
