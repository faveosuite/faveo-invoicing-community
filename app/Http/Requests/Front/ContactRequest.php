<?php

namespace App\Http\Requests\Front;

use App\Rules\Honeypot;
use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->is('contact-us')) {
            return [
                'conName' => 'required',
                'email' => 'required|email',
                'conmessage' => 'required',
                'Mobile' => 'required',
                'country_code' => 'required',
                'contact' => [new Honeypot()],
            ];
        } elseif ($this->is('demo-request')) {
            return [
                'demoname' => 'required',
                'demoemail' => 'required|email',
                'country_code' => 'required',
                'Mobile' => 'required',
                'demomessage' => 'required',
                'demo' => [new Honeypot()],
            ];
        }
    }

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
