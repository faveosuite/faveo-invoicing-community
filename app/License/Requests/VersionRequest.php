<?php

declare(strict_types=1);

namespace App\License\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VersionRequest extends FormRequest
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
     * @return array<mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['numeric'],
            'version_number' => ['string'],
            'version_status' => ['boolean'],
        ];
    }
}
