<?php

namespace App\Modules\License\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VersionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'product_id' => 'numeric',
            'version_number' => 'string',
            'version_status' => 'boolean',
        ];
    }
}
