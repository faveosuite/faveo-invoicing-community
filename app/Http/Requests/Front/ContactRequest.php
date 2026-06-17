<?php

declare(strict_types=1);

namespace App\Http\Requests\Front;

use App\Rules\Honeypot;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class ContactRequest extends FormRequest
{
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->is('contact-us')) {
            return [
                'conName' => ['required'],
                'email' => ['required', 'email'],
                'conmessage' => ['required'],
                'Mobile' => ['required'],
                'country_code' => ['required'],
                'contact' => [new Honeypot()],
            ];
        }

        if ($this->is('demo-request')) {
            return [
                'demoname' => ['required'],
                'demoemail' => ['required', 'email'],
                'country_code' => ['required'],
                'Mobile' => ['required'],
                'demomessage' => ['required'],
                'demo' => [new Honeypot()],
            ];
        }

        return [];
    }

    #[Override]
    public function messages()
    {
        return [
            'conName.required' => __('validation.contact_request.conName'),
            'email.required' => __('validation.contact_request.email'),
            'conmessage.required' => __('validation.contact_request.conmessage'),
            'Mobile.required' => __('validation.contact_request.Mobile'),
            'country_code.required' => __('validation.contact_request.country_code'),
            'demoname.required' => __('validation.contact_request.demoname'),
            'demomessage.required' => __('validation.contact_request.demomessage'),
            'demoemail.required' => __('validation.contact_request.demoemail'),
        ];
    }
}
