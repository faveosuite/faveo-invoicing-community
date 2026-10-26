<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Traits\RequestJsonValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Override;

class UpdateStoragePathRequest extends FormRequest
{
    use RequestJsonValidation;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'disk' => ['required', 'string'],
            'path' => ['string', 'nullable'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $path = $this->input('path');

            if ($this->input('disk') === 'system' && (! is_dir((string) $path) || ! is_writable((string) $path))) {
                $validator->errors()->add('path', __('validation.storage_path.path.invalid'));
            }
        });
    }

    #[Override]
    public function messages()
    {
        return [
            'disk.required' => __('validation.storage_path.disk.required'),
            'disk.string' => __('validation.storage_path.disk.string'),
            'path.string' => __('validation.storage_path.path.string'),
            'path.nullable' => __('validation.storage_path.path.nullable'),
        ];
    }
}
