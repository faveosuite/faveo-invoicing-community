<?php

declare(strict_types=1);

namespace App\Http\Requests\Cart;

use App\Traits\RequestJsonValidation;
use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'plan_id' => ['nullable', 'integer'],
            'quantity' => ['sometimes', 'integer', 'min:1'],
            'agents' => ['sometimes', 'integer', 'min:1'],
            'domain' => ['nullable', 'string', 'max:255'],
            'data_center_id' => ['nullable', 'integer', 'exists:cloud_data_centers,id'],
            'billing_cycle' => ['sometimes', 'in:monthly,yearly,onetime'],
        ];
    }
}
