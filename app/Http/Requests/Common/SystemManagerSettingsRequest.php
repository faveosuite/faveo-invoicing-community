<?php

declare(strict_types=1);

namespace App\Http\Requests\Common;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class SystemManagerSettingsRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'existingAccManager' => ['nullable', 'required_with:newAccManager', 'integer'],
            'newAccManager' => ['nullable', 'required_with:existingAccManager', 'integer', 'different:existingAccManager'],
            'existingSaleManager' => ['nullable', 'required_with:newSaleManager', 'integer'],
            'newSaleManager' => ['nullable', 'required_with:existingSaleManager', 'integer', 'different:existingSaleManager'],
            'autoAssignAccount' => ['required', 'boolean'],
            'autoAssignSales' => ['required', 'boolean'],
        ];
    }

    #[Override]
    public function messages(): array
    {
        return [
            'existingAccManager.required_with' => __('message.existingAccManager_required'),
            'newAccManager.required_with' => __('message.newAccManager_required'),
            'newAccManager.different' => __('message.same_account_manager_error'),
            'existingSaleManager.required_with' => __('message.select_system_sales_manager'),
            'newSaleManager.required_with' => __('message.select_new_sales_manager'),
            'newSaleManager.different' => __('message.sales_manager_must_be_different'),
        ];
    }
}
