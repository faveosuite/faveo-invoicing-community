<?php

declare(strict_types=1);

namespace App\Http\Requests\Common;

use App\Traits\RequestJsonValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class SocialMediaRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
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
                'name' => ['required', Rule::unique('social_media', 'name')->ignore($this->route('id'))],
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
