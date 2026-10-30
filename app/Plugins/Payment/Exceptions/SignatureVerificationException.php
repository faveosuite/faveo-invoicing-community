<?php

declare(strict_types=1);

namespace App\Plugins\Payment\Exceptions;

/**
 * Raised when a gateway callback fails signature/authenticity verification —
 * i.e. the payload was not genuinely produced by the gateway. Distinct from a
 * generic {@see PaymentException} so callers can treat tampering specially
 * (e.g. alert an admin) without parsing messages.
 */
class SignatureVerificationException extends PaymentException {}
