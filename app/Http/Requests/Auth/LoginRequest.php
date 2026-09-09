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
     * Get the validation rules that apply to the request.
     *
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'email_username' => ['required', 'string'],
            'password1' => ['required', 'string'],
            'login' => [new Honeypot],
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
            'email_username.required' => __('message.password_email'),
            'password1.required' => __('message.please_enter_password'),
        ];
    }
}
