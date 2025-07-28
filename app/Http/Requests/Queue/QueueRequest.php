<?php

namespace App\Http\Requests\Queue;

use Illuminate\Foundation\Http\FormRequest;

class QueueRequest extends FormRequest
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
        $request = $this->except('_token');
        $rules = $this->setRule($request);

        return $rules;
    }

    public function setRule($request)
    {
        $rules = ['input' => 'required'];
        if (count($request) > 0) {
            unset($rules['input']);
            foreach ($request as $key => $value) {
                $rules[$key] = 'required';
            }
        }

        return $rules;
    }
}
