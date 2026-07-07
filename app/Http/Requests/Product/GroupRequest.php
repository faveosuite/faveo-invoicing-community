<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Http\Requests\Request;
use Override;

class GroupRequest extends Request
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
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'headline' => ['nullable', 'string'],
            'tagline' => ['nullable', 'string'],
            'hidden' => ['nullable', 'integer'],
            'pricing_templates_id' => ['required', 'exists:pricing_templates,id'],
            'status' => ['nullable', 'boolean'],
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
        ];
    }
}
