<?php

namespace App\Http\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $regex = '/^(https?:\/\/)?([\w-]+\.)+([a-z]{2,6})(\/[\w-]*)*(\?.*)?(#.*)?$/i';
        return [
            'company' => 'required|max:50',
            'company_email' => 'required|email',
            'title'=>'max:50',
            'website' => 'required|url|regex:' . $regex,
            'phone' => 'required',
            'address' => 'required',
            'state' => 'required',
            'country' => 'required',
            'gstin'=>'max:15',
            'default_currency' => 'required',
            'admin-logo' => 'sometimes|mimes:jpeg,png,jpg|max:2048',
            'fav-icon' => 'sometimes|mimes:jpeg,png,jpg|max:2048',
            'logo' => 'sometimes|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function messages()
    {
        return[
            'company.required' => 'The Company name field is required',
            'company.max' => 'The Company name must not be greater than 50 characters',
            'logo.mimes' => 'The Client Panel Logo must be a file of type: jpeg, jpg, png',
            'admin-logo.mimes' => 'The Admin Panel Logo must be a file of type: jpeg, jpg, png',
            'fav-icon.mimes' => 'The Favicon must be a file of type: jpeg, jpg, png',
        ];
    }
}
