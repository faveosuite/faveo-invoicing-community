<?php

namespace App\Rules;

use App\Http\Controllers\Common\PhoneNumberController;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumber implements ValidationRule
{
    public function __construct(protected string $mobileCountryIso)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        $phoneService = new PhoneNumberController;

        if (! $phoneService->isValid($value, $this->mobileCountryIso, strict: true)) {
            $fail(__('validation.phone_number', [
                'attribute' => $attribute,
            ]));
        }
    }
}
