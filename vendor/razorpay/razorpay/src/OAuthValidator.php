<?php

namespace Razorpay\Api;
use Razorpay\Api\Errors;

class OAuthValidator
{
    protected $data;
    protected $rules;

    protected $messages = [
        'id'         => 'The :attribute is not valid.',
        'required'   => 'The :attribute field is required.',
        'url'        => 'The :attribute is not valid',
        'token_type' => 'The :attribute must be either refresh_code or access_token.',
    ];

    public function __construct(array $data, array $rules)
    {
        $this->data  = $data;
        $this->rules = $rules;
    }

    public function validateOrFail()
    {
        foreach ($this->rules as $field => $ruleSet) {
            $rules = explode('|', $ruleSet);
            foreach ($rules as $rule) {
                $this->applyRule($field, $rule);
            }
        }

        return true;
    }

    protected function applyRule($field, $rule)
    {
        $params = [];
        if (strpos($rule, ':') !== false) {
            [$rule, $paramString] = explode(':', $rule);
            $params = explode(',', $paramString);
        }
    
        // Convert snake_case to camelCase (e.g., token_type -> TokenType)
        $method = 'validate' . str_replace('_', '', ucwords($rule, '_'));
    
        // Check if the method exists and call it
        if (method_exists($this, $method)) {
            $this->$method($field, ...$params);
        } else {
            throw new Exception("Validation rule '$rule' not found.");
        }
    }

    protected function validateId($field)
    {
        $idRegex = "/^[A-Za-z0-9]{1,14}$/";
        $value = $this->data[$field];
        if (!preg_match($idRegex, $value)){
            $this->throwException($field, 'id');
        }
    }

    protected function validateRequired($field)
    {
        $value = $this->data[$field] ?? null;
    
        // Check if the value is empty or not set
        if (is_null($value) || (is_string($value) && trim($value) === '')) {
            $this->throwException($field, 'required');
        }
    
        // Check if the value is an array and is empty
        if (is_array($value) && empty($value)) {
            $this->throwException($field, 'required');
        }
    }

    protected function validateTokenType($field)
    {
        $validTypes = ['refresh_code', 'access_token'];

        if (!in_array($this->data[$field] ?? '', $validTypes)) {
            $this->throwException($field, 'token_type');
        }
    }

    protected function validateUrl($field)
    {
        $url = $this->data[$field] ?? '';
    
        $urlRegex = "/^(https?):\\/\\/[^\\s\\/$.?#].[^\\s]*$/i";
    
        if (!preg_match($urlRegex, $url)) {
            $this->throwException($field, 'url');
        }
    }

    protected function throwException($field, $rule, $params = [])
    {
        $message = str_replace(':attribute', $field, $this->messages[$rule] ?? 'The :attribute is invalid.');

        foreach ($params as $key => $param) {
            $message = str_replace(':' . $key, $param, $message);
        }

        throw new Errors\BadRequestError($message, "BAD_REQUEST_ERROR", 400);
    }
}
