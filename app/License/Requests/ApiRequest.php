<?php

namespace App\License\Requests;

use App\Traits\RequestJsonValidation;
use Illuminate\Foundation\Http\FormRequest;

class ApiRequest extends FormRequest
{
    use RequestJsonValidation;

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

            'api_key_secret' => ['required', 'string', 'unique:afl_api_keys,api_key_secret'],
            /*'api_key_ip'     => 'string',*/
            'api_key_clients_add' => ['required', 'boolean'],
            'api_key_clients_edit' => ['required', 'boolean'],
            'api_key_licenses_add' => ['required', 'boolean'],
            'api_key_licenses_edit' => ['required', 'boolean'],
            'api_key_products_add' => ['required', 'boolean'],
            'api_key_products_edit' => ['required', 'boolean'],
            'api_key_installations_edit' => ['required', 'boolean'],
            'api_key_search' => ['required', 'boolean'],
            'api_key_status' => ['required', 'boolean'],
            'api_key_description' => ['required', 'max:250'],
        ];
    }
}
