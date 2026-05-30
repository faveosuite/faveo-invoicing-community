# Payment Gateway Package

A small, **application-agnostic** payment package. The drivers depend only on
their vendor SDK (`stripe/stripe-php`, `razorpay/razorpay`) and plain PHP — no
Laravel, no application models, no session/auth/config. Drop the `Payment`
folder into any PHP project, give a driver its credentials, and it works.

## Layout

```
Payment/
├── Contracts/PaymentGateway.php   The interface every driver implements
├── Dto/                           Immutable value objects (input + output)
│   ├── PaymentRequest.php           what to charge (amount, currency, customer…)
│   ├── Customer.php                 the payer's details (all optional)
│   ├── PaymentSession.php           createPayment() result (id + client config)
│   └── PaymentResult.php            capturePayment() result (paid? + reference)
├── Gateways/
│   ├── StripeGateway.php            embedded Checkout Session
│   └── RazorpayGateway.php          Orders flow + signature verification
├── Support/Money.php              major ↔ minor unit conversion (ISO-4217)
├── Exceptions/                    package-owned exceptions
│   ├── PaymentException.php
│   └── SignatureVerificationException.php
└── PaymentGatewayManager.php      credential-free registry, resolve by name
```

## The flow

```php
interface PaymentGateway {
    createPayment(PaymentRequest $request): PaymentSession;   // open a payment
    capturePayment(array $payload): PaymentResult;            // verify a callback
    refundPayment(string $ref, ?float $amount = null): PaymentResult;
    getPaymentStatus(string $ref): string;
    verifyWebhook(string $rawPayload, string $signature): bool;
    name(): string;
    supportedCurrencies(): array;
}
```

- **`createPayment`** opens a payment and returns `clientConfig`, ready to hand
  to the browser SDK.
- **`capturePayment`** verifies the client callback against the gateway and
  reports whether money was captured (`PaymentResult::$paid`).
- **`refundPayment`** refunds a captured payment (full when `$amount` is null,
  else that amount in major units).
- **`getPaymentStatus`** reads the gateway's current status string for a payment.
- **`verifyWebhook`** authenticates a raw webhook body against its signature
  (needs the webhook secret passed to the driver's constructor).

Every gateway call throws `PaymentException` on failure — so "no exception"
means success; only `verifyWebhook` returns a plain bool instead of throwing.

## Usage

### Stripe (embedded Checkout)

```php
use App\Plugins\Payment\Gateways\StripeGateway;
use App\Plugins\Payment\Dto\{PaymentRequest, Customer};

$stripe  = new StripeGateway($secretKey, $publishableKey);

$session = $stripe->createPayment(new PaymentRequest(
    amount: 49.99, currency: 'USD', reference: 'INV-1001',
    customer: new Customer(email: 'jane@example.com'),
    description: 'Invoice INV-1001', metadata: ['invoice_id' => 1001],
));

// Front-end: stripe.initEmbeddedCheckout({ clientSecret: clientConfig.client_secret })
// then on its onComplete callback, post clientConfig.session_id back, and:

$result = $stripe->capturePayment(['session_id' => $session->id]);
if ($result->paid) { /* fulfil */ }
```

### Razorpay (Orders)

```php
use App\Plugins\Payment\Gateways\RazorpayGateway;

$razorpay = new RazorpayGateway($keyId, $keySecret, 'My Store');
$session  = $razorpay->createPayment(new PaymentRequest(500, 'INR', 'INV-1001'));

// Front-end: new Razorpay(clientConfig).open(); the handler returns
// razorpay_order_id / razorpay_payment_id / razorpay_signature — post them back:

$result = $razorpay->capturePayment($handlerResponse); // throws on a bad signature
```

### Resolving by name

```php
use App\Plugins\Payment\PaymentGatewayManager;

$gateways = (new PaymentGatewayManager)
    ->register('Stripe',   fn () => new StripeGateway($secret, $publishable))
    ->register('Razorpay', fn () => new RazorpayGateway($key, $keySecret));

$gateways->gateway('stripe')->createPayment($request); // case-insensitive
```

## Errors

All failures raise `App\Plugins\Payment\Exceptions\PaymentException`. A tampered
or invalid gateway callback raises the more specific
`SignatureVerificationException` so callers can react to it distinctly.

## Application integration

Wiring this package into *this* application — pulling credentials from the
`ApiKey` model, building a `PaymentRequest` from an `Invoice`, recording the
payment and fulfilling the order — lives **outside** the package, in
`app/Services/Payment/` (so the package stays dependency-pure).
