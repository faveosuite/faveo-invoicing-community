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
        ];
    }

    #[Override]
    public function messages()
    {
        return [
        ];
    }
}
