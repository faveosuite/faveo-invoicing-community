<?php

declare(strict_types=1);

namespace App\Http\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class SocialMediaRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $regex = '/^(https?:\/\/)?([\w-]+\.)+([a-z]{2,6})(\/[\w-]*)*(\?.*)?(#.*)?$/i';
        if ($this->method() == 'POST') {
            return [
                'name' => ['required', 'unique:social_media', 'max:50'],
                'link' => 'required|regex:'.$regex,
            ];
        }

        if ($this->method() == 'PATCH') {
            return [
                'name' => ['required'],
                'link' => 'required|url|regex:'.$regex,
            ];
        }

        return [];
    }

    #[Override]
    public function messages()
    {
        return [
            'name.required' => __('validation.social_media_form.name.required'),
            'name.unique' => __('validation.social_media_form.name.unique'),
            'name.max' => __('validation.social_media_form.name.max'),
            'link.required' => __('validation.social_media_form.link.required'),
            'link.url' => __('validation.social_media_form.link.url'),
            'link.regex' => __('validation.social_media_form.link.regex'),
        ];
    }
}
