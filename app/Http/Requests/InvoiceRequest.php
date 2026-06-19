<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class InvoiceRequest extends FormRequest
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
            'user' => ['required'],
            'date' => ['required', 'date'],
            'domain' => ['sometimes', 'nullable', 'regex:/^(?!:\/\/)(?=.{1,255}$)((.{1,63}\.){1,127}(?![0-9]*$)[a-z0-9-]+\.?)$/i'],
            'plan' => ['required_if:subscription,true'],
            'price' => ['required'],
            'product' => ['required'],
        ];
    }

    #[Override]
    public function messages()
    {
        return [
            'user.required' => __('validation.invoice.user.required'),
            'date.required' => __('validation.invoice.date.required'),
            'date.date' => __('validation.invoice.date.date'),
            'domain.regex' => __('validation.invoice.domain.regex'),
            'plan.required_if' => __('validation.invoice.plan.required_if'),
            'price.required' => __('validation.invoice.price.required'),
            'product.required' => __('validation.invoice.product.required'),
        ];
    }
}
