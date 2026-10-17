<?php

declare(strict_types=1);

namespace App\Http\Requests\Cart;

use App\Traits\RequestJsonValidation;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
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
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'agents' => ['sometimes', 'integer', 'min:1'],
            // The license validator substring-matches this against the install URL,
            // so it must be a bare host (sub.domain.com) or an IP — no scheme or path.
            'domain' => ['nullable', 'string', 'max:255', function (string $attribute, mixed $value, Closure $fail): void {
                $isIp = filter_var($value, FILTER_VALIDATE_IP) !== false;
                $isHost = str_contains((string) $value, '.') && filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
                if (! $isIp && ! $isHost) {
                    $fail(__('validation.invoice.domain.regex'));
                }
            }],
        ];
    }
}
