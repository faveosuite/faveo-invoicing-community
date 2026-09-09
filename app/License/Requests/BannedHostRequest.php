<?php

declare(strict_types=1);

namespace App\License\Requests;

use App\Traits\RequestJsonValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     *
     * @return array<mixed>
     */
    public function rules(): array
    {
        // used for both add (no `id`) and edit (`id` present, must ignore itself in the unique check)
        $unique = Rule::unique('license_banned_hosts', 'banned_host_ip');
        if ($this->filled('id')) {
            $unique = $unique->ignore($this->input('id'));
        }

        return [
            'banned_host_ip' => ['required', 'ip', $unique],
            'comments' => ['nullable', 'string'],
        ];
    }
}
