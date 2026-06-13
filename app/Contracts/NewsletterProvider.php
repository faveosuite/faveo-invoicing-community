<?php

namespace App\Contracts;

interface NewsletterProvider
{
    public function name(): string;

    public function isEnabled(): bool;

    public function subscribeEmail(string $email): void;
}
