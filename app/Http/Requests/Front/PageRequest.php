<?php

declare(strict_types=1);

namespace App\Http\Requests\Front;

use App\Http\Requests\Request;
use Override;

class PageRequest extends Request
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
    public function rules(): array
    {
        // Update (PUT) is a partial update — name/slug/content aren't
        // resent when only e.g. the SEO fields are being changed, so they're
        // optional there. Create (POST) always needs the full set.
        $requiredRule = $this->isMethod('PUT') ? 'sometimes' : 'required';

        return [
            'name' => [$requiredRule, 'string'],
            'slug' => [$requiredRule, 'string'],
            'url' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'publish' => ['nullable', 'boolean'],
            'parent_page_id' => ['nullable', 'integer'],
            'content' => [$requiredRule, 'string'],
            'default_page_id' => ['nullable', 'integer'],
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
            'name.required' => __('validation.frontend_pages.name.required'),
            'slug.required' => __('validation.frontend_pages.slug.required'),
            'content.required' => __('validation.frontend_pages.content.required'),
        ];
    }
}
