<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\File;

class StoreLanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'language' => ['required', function ($attribute, $value, $fail) {
                $availableLanguages = array_map('basename', File::directories(lang_path()));
                if (! in_array($value, $availableLanguages)) {
                    return $fail(__('validation.language.invalid'));
                }
            }],
        ];
    }

    public function messages(): array
    {
        return [
            'language.required' => __('validation.language.required'),
        ];
    }
}
