<?php

namespace App\License\Requests;

use App\Traits\RequestJsonValidation;
use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
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
            'client_fname' => 'string',
            'client_lname' => 'string',
            'client_email' => 'string|unique:users,client_email|email',
            'client_active_date' => 'date',
            'client_cancel_date' => 'date',
            'client_status' => 'boolean',
        ];
    }
}
