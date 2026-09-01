<?php

declare(strict_types=1);

namespace App\Http\Requests\Common;

use App\Http\Requests\Request;
use App\Traits\RequestJsonValidation;
use Override;

class SeoDefaultPageRequest extends Request
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
        return [
            'meta_title' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],
            'og_title' => ['nullable', 'string'],
            'og_description' => ['nullable', 'string'],
            'og_image' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'og_same_as_meta' => ['nullable', 'boolean'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'og_image.mimes' => __('validation.og_image.mimes'),
            'og_image.max' => __('validation.og_image.max'),
        ];
    }
}
