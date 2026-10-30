<?php

namespace App\Rules;

use Arr;
use Closure;
use Crypt;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Str;

class Honeypot implements ValidationRule
{
    /**
     * Indicates whether the rule should be implicit.
     *
     * @var bool
     */
    public $implicit = true;

    public function __construct(protected int $minTime = 1, protected string $message = 'Your submission was flagged as automated. If this is a mistake, please try again.') {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        //        For v3 we dont need honeypot
        if (isV3Api()) {
            return;
        }

        if (! is_array($value) || count($value) !== 2) {
            $fail($this->message);

            return;
        }

        // Detect pot field
        $pot = Arr::first($value, fn ($val, $key) => Str::startsWith($key, 'p'));
        if ($pot !== '' && $pot !== null) {
            $fail($this->message);

            return;
        }

        // Detect and validate encrypted time
        $time = Arr::first($value, fn ($val, $key) => Str::startsWith($key, 't'));

        if (! $this->validateTimeField($time)) {
            $fail($this->message);
        }
    }

    private function validateTimeField(mixed $value): bool
    {
        if (! $value) {
            return false;
        }

        try {
            $decrypted = Crypt::decrypt($value);
        } catch (Exception) {
            return false;
        }

        return is_numeric($decrypted) && time() >= ($decrypted + $this->minTime);
    }
}
