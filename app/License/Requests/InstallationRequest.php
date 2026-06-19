<?php

declare(strict_types=1);

namespace App\License\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstallationRequest extends FormRequest
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
            'installation_ip' => ['string', 'unique:installations,installation_ip'],
            'installation_status' => ['boolean'],
            'installation_disable_ip_verification' => ['boolean'],
            'installation_disable_ip' => ['boolean'],

        ];
    }
}
