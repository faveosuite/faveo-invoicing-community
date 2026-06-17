<?php

declare(strict_types=1);

namespace App\License\Requests;

use App\Traits\RequestJsonValidation;
use Illuminate\Foundation\Http\FormRequest;

class BannedHostRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'banned_host_ip' => [
                'required',
                'string',
                'unique:license_banned_hosts,banned_host_ip',
                'regex:/\b(?:[0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}\b|\b(?:\d{1,3}\.){3}\d{1,3}\b/',
            ],
            'banned_host_date' => ['date'],
            'banned_host_blocks' => ['numeric'],
            'banned_host_last_block_date' => ['date'],
        ];
    }
}
