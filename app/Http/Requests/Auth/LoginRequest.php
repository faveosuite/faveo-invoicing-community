<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Rules\Honeypot;
use App\Traits\RequestJsonValidation;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class LoginRequest extends FormRequest
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
     * `login` = email or username.
     *
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
            'honeypot' => [new Honeypot],
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<mixed>     */
    #[Override]
    /**
     * @return array<mixed>
     */
    public function messages()
    {
        return [
            'login.required' => __('message.password_email'),
            'password.required' => __('message.please_enter_password'),
        ];
    }
}
