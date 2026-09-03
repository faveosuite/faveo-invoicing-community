<?php

declare(strict_types=1);

namespace App\License\Requests;

use App\Traits\RequestJsonValidation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SecuritySettingsRequest extends FormRequest
{
    use RequestJsonValidation;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'auto_ban_enabled' => ['required', 'boolean'],
            'failed_licensings_limit' => ['required', 'integer', 'min:0', 'max:10'],
        ];
    }
}
