<?php

namespace App\Http\Requests\User;

use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Change password (admin and client panels).
 */
class ChangePasswordRequest extends ProfileRequest
{
    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'old_password' => ['required', 'min:6'],
            'new_password' => [
                'required',
                PasswordRule::defaults(),
                'different:old_password',
            ],
            'confirm_password' => ['required', 'same:new_password'],
        ];
    }
}
