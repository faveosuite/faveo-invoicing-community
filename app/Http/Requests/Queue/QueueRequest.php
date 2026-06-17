<?php

namespace App\Http\Requests\Queue;

use Illuminate\Foundation\Http\FormRequest;

class QueueRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $request = $this->except('_token');

        return $this->setRule($request);
    }

    /**
     * @return 'required'[]
     */
    public function setRule($request): array
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
