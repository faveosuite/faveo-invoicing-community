<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Honeypot implements ValidationRule
{
    /**
     * Indicates whether the rule should be implicit.
     *
     * @var bool
     */
    public $implicit = true;
    protected int $minTime;
    protected string $message;

    public function __construct(int $minTime = 1, string $message = 'Your submission was flagged as automated. If this is a mistake, please try again.')
    {
        $this->minTime = $minTime;
        $this->message = $message;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value) || count($value) !== 2) {
            $fail($this->message);

            return;
        }

        // Detect pot field
        $pot = \Arr::first($value, fn ($val, $key) => \Str::startsWith($key, 'p'));
        if ($pot !== '' && $pot !== null) {
            $fail($this->message);

            return;
        }

        // Detect and validate encrypted time
        $time = \Arr::first($value, fn ($val, $key) => \Str::startsWith($key, 't'));

        if (! $this->validateTimeField($time)) {
            $fail($this->message);
        }
    }

    private function validateTimeField($value): bool
    {
        if (! $value) {
            return false;
        }

        try {
            $decrypted = \Crypt::decrypt($value);
        } catch (\Exception $e) {
            return false;
        }

        return is_numeric($decrypted) && time() >= ($decrypted + $this->minTime);
    }
}
