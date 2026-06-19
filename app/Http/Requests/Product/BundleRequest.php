<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Http\Requests\Request;
use Override;

class BundleRequest extends Request
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
            'items.*' => ['required'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'name.required' => __('validation.bundle.name.required'),
            'items.*.required' => __('validation.bundle.items.required'),
        ];
    }
}
