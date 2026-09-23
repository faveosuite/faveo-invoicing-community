<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Traits\RequestJsonValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Override;

class UpdatePdfSettingsRequest extends FormRequest
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
            'pdf_driver' => ['required', 'string'],
            'chrome_path' => ['string', 'nullable'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $path = (string) $this->input('chrome_path');

            if ($this->input('pdf_driver') === 'chrome' && ! is_file($path)) {
                $validator->errors()->add('chrome_path', __('validation.pdf_settings.chrome_path.invalid'));
            }
        });
    }

    #[Override]
    public function messages()
    {
        return [
            'chrome_path.required' => __('validation.pdf_settings.chrome_path.required'),
            'chrome_path.string' => __('validation.pdf_settings.chrome_path.string'),
        ];
    }
}
