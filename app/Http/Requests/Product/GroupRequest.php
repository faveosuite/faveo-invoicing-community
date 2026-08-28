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
            'name.unique' => __('validation.group.name.unique'),
        ];
    }
}
