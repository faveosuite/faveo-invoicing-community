<?php

namespace App\Plugins\Payment\Exceptions;

use RuntimeException;

/**
 * Base exception for every failure raised by this package. Drivers catch their
 * vendor SDK exceptions and rethrow as a PaymentException, so callers depend on
 * the package's own type rather than on Stripe's or Razorpay's SDK internals.
 */
class PaymentException extends RuntimeException
{
}
