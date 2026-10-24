<?php

declare(strict_types=1);

namespace App\License\Requests;

use App\Traits\RequestJsonValidation;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'product_title' => ['string', 'unique:afl_products,product_title'],
            'product_sku' => ['string', 'unique:afl_products,product_sku'],
            /*'product_date'=> 'date',
            'product_version'=> 'string',
            'product_envato_id'=> 'integer|unique:afl_products,product_envato_id',
            'product_status'=> 'boolean'*/

        ];
    }
}
