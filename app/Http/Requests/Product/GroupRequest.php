<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Http\Requests\Request;
use App\Traits\RequestJsonValidation;
use Illuminate\Validation\Rule;
use Override;

class GroupRequest extends Request
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
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', Rule::unique('product_groups', 'name')->ignore($this->route('group_id'))],
            'headline' => ['nullable', 'string'],
            'tagline' => ['nullable', 'string'],
            'hidden' => ['nullable', 'integer'],
            // Design-template picker removed from the UI (2026-09) — it never drove any
            // actual rendering difference, just a name/thumbnail. Kept nullable here so
            // the column (still NOT NULL + FK, defaulted in GroupController::groupCreate())
            // stays intact for whoever implements real per-template rendering later.
            'pricing_templates_id' => ['nullable', 'integer', 'exists:pricing_templates,id'],
            'status' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:255'],
            'og_image' => ['sometimes', 'file', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'og_same_as_meta' => ['nullable', 'boolean'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'name.unique' => __('validation.group.name.unique'),
            'og_image.mimes' => __('validation.og_image.mimes'),
            'og_image.max' => __('validation.og_image.max'),
        ];
    }
}
