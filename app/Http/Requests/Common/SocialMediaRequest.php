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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('class') && $this->has('name')) {
            $this->merge(['class' => 'social-icons-'.\Illuminate\Support\Str::slug((string) $this->input('name'))]);
        }
        if (! $this->has('fa_class') && $this->has('name')) {
            $this->merge(['fa_class' => 'fab fa-'.\Illuminate\Support\Str::slug((string) $this->input('name'))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $regex = '/^(https?:\/\/)?([\w-]+\.)+([a-z]{2,10})(\/[\w\-@.~%+=]*)*(\?.*)?(#.*)?$/i';
        if ($this->method() == 'POST') {
            return [
                'name' => ['required', 'unique:social_media', 'max:50'],
                'link' => 'required|regex:'.$regex,
                'class' => 'required|max:255',
                'fa_class' => 'required|max:225',
            ];
        }

        if ($this->method() == 'PATCH') {
            return [
                'name' => ['required', Rule::unique('social_media', 'name')->ignore($this->route('id'))],
                'link' => 'required|regex:'.$regex,
                'class' => 'required|max:255',
                'fa_class' => 'required|max:225',
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
            'class.required' => __('validation.social_media_form.class.required'),
            'fa_class.required' => __('validation.social_media_form.fa_class.required'),
        ];
    }
}
