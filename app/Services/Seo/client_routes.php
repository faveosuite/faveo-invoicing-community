<?php

declare(strict_types=1);

/**
 * Title/description for client-panel routes with no real per-page admin
 * setting and no module of their own — the last-resort tier in
 * SeoMetaService::fallback(), also shipped to the client SPA via
 * resolveClientRoutes() so clientRouter.js looks up an already-resolved
 * value instead of duplicating this list. login/forgot_password/reset_password/
 * contact_us aren't here — those resolve via SeoMetaService::fromDefaultPage()/
 * fromContactUsPage() (SeoDefaultPage/FrontendPage tables), not this file.
 *
 * Keyed by the route path with dynamic segments (:id) normalized to '*' —
 * matched against the incoming request path via SeoMetaService::matchRoute(),
 * which prefers the most specific (fewest wildcards) matching pattern.
 *
 * 'title'/'description' are each either a lang key (translated via
 * SeoMetaService::resolveText() if it exists in lang/{locale}/message.php)
 * or literal text (shown exactly as written if it doesn't).
 *
 * @return array<string, array{title: string, description: string}>
 */
return [
    'client-dashboard' => ['title' => 'message.dashboard', 'description' => 'Your account dashboard — track orders, invoices, and subscriptions.'],
    'my-orders' => ['title' => 'message.my_orders', 'description' => 'View and manage your order history.'],
    'my-order/*' => ['title' => 'message.order_details', 'description' => 'Order details and status.'],
    'my-invoices' => ['title' => 'message.my_invoices', 'description' => 'View, download, and pay your invoices.'],
    'my-invoice/*' => ['title' => 'Invoice Details', 'description' => 'Invoice details and payment options.'],
    'my-profile' => ['title' => 'message.my_profile', 'description' => 'Manage your account profile and settings.'],
    'my-profile/change-password' => ['title' => 'message.change_password', 'description' => 'Change your account password.'],
    'my-profile/2fa' => ['title' => 'message.two_factor_auth', 'description' => 'Manage two-factor authentication for your account.'],
    'cart' => ['title' => 'message.shopping_cart', 'description' => 'Review items in your shopping cart.'],
    'checkout' => ['title' => 'message.checkout', 'description' => 'Complete your purchase securely.'],
    'place-order' => ['title' => 'message.place_order', 'description' => 'Confirm and place your order.'],
    'payment-success' => ['title' => 'Payment Successful', 'description' => 'Your payment was successful.'],
    'pricing' => ['title' => 'message.pricing', 'description' => 'Review pricing and plans.'],
    'verify' => ['title' => 'message.verify_email', 'description' => 'Verify your email address.'],
    'verify-2fa' => ['title' => 'message.two_factor_authentication', 'description' => 'Verify your identity with two-factor authentication.'],
    'pay' => ['title' => 'message.pay', 'description' => 'Secure payment page.'],
    'admin' => ['title' => 'Admin', 'description' => 'Administration panel.'],

    // Client-only error routes — no server-rendered equivalent, but still
    // listed so the frontend never needs a hardcoded title/description either.
    '404' => ['title' => 'Not Found', 'description' => 'The page you requested could not be found.'],
    '403' => ['title' => 'Forbidden', 'description' => "You don't have permission to access this page."],
    '500' => ['title' => 'Server Error', 'description' => 'Something went wrong on our end.'],
    '*' => ['title' => 'Not Found', 'description' => 'The page you requested could not be found.'],
];
