<?php

namespace App\Http\Requests;

use App\Traits\RequestJsonValidation;
use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Override;

class verifyOtp extends FormRequest
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
        $email = $this->request->get('newemail');
        $pass = User::where('email', $email)->value('password');

        return [
            'verify_email' => ['sometimes', 'required', 'verify_number', 'numeric'],
            'password' => [

                function ($_attribute, $value, $fail) use ($pass) { // NOSONAR
                    if (! Hash::check($value, $pass)) {
                        return $fail(__('validation.password_otp.invalid'));
                    }
                },
            ],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'verify_email.required' => __('validation.verify_email.required'),
            'verify_email.email' => __('validation.verify_email.email'),
            'verify_email.verify_email' => __('validation.verify_email.verify_email'), // Custom validation rule message
            'verify_country_code.required' => __('validation.verify_country_code.required'),
            'verify_country_code.numeric' => __('validation.verify_country_code.numeric'),
            'verify_country_code.verify_country_code' => __('validation.verify_country_code.verify_country_code'), // Custom validation rule message
            'verify_number.required' => __('validation.verify_number.required'),
            'verify_number.numeric' => __('validation.verify_number.numeric'),
            'verify_number.verify_number' => __('validation.verify_number.verify_number'), // Custom validation rule message
            'password.required' => __('validation.password_otp.required'),
        ];
    }
}
