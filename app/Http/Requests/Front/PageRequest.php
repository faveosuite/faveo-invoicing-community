<?php

declare(strict_types=1);

namespace App\Http\Requests\Front;

use App\Traits\RequestJsonValidation;
use App\Http\Requests\Request;
use Illuminate\Validation\Rule;
use Override;

class PageRequest extends Request
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
        // Update (PUT) is a partial update — name/slug/content aren't
        // resent when only e.g. the SEO fields are being changed, so they're
        // optional there. Create (POST) always needs the full set.
        $requiredRule = $this->isMethod('PUT') ? 'sometimes' : 'required';

        return [
            'name' => [$requiredRule, 'string', Rule::unique('frontend_pages', 'name')->ignore($this->route('id'))],
            'slug' => [$requiredRule, 'string', Rule::unique('frontend_pages', 'slug')->ignore($this->route('id'))],
            'url' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'publish' => ['nullable', 'boolean'],
            'parent_page_id' => ['nullable', 'integer'],
            'content' => [$requiredRule, 'string'],
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
            'name.unique' => __('validation.frontend_pages.name.unique'),
            'slug.required' => __('validation.frontend_pages.slug.required'),
            'slug.unique' => __('validation.frontend_pages.slug.unique'),
            'content.required' => __('validation.frontend_pages.content.required'),
        ];
    }
}
