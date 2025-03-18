<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Request;

class ClientRequest extends Request
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
        switch ($this->method()) {
            case 'POST':
                return [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'company' => 'required',
                    'email' => 'required|email|unique:users',
                    'address' => 'required',
                    'mobile' => 'required',
                    'country' => 'required|exists:countries,country_code_char2',
                    'timezone_id' => 'required',
                    'user_name' => 'unique:users,user_name',
                    'zip' => 'regex:/^[a-zA-Z0-9]+$/',
                ];

            case 'PATCH':
                $id = $this->segment(2);

                return [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|email|unique:users,email,'.$this->getSegmentFromEnd().',id',
                    'company' => 'required',
                    'address' => 'required',
                    'mobile' => 'required',
                    'timezone_id' => 'required',
                    'user_name' => 'unique:users,user_name,'.$id,
                    'zip' => 'regex:/^[a-zA-Z0-9]+$/',
                ];

            default:
                break;
        }
    }

    public function messages()
    {
        return[
            'state.required_if' => 'The state field is required when country is India.',
            'zip.regex' => 'The zip/postal code in invalid',

        ];
    }

    private function getSegmentFromEnd($position_from_end = 1)
    {
        $segments = $this->segments();

        return $segments[count($segments) - $position_from_end];
    }
}
