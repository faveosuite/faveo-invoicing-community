<?php

declare(strict_types=1);

/**
 * Title/description for every admin-panel route — the last tier in
 * admin.blade.php's title cascade, used only when General SEO
 * (Settings > SEO > General) has nothing configured.
 *
 * Keyed by the route path with dynamic segments (:id, :token, ...)
 * normalized to '*' — matched against the incoming request path via
 * SeoMetaService::matchRoute(), which prefers the most specific (fewest
 * wildcards) matching pattern.
 *
 * 'title'/'description' are each either a lang key (translated via
 * SeoMetaService::resolveText() if it exists in lang/{locale}/message.php)
 * or literal text (shown exactly as written if it doesn't).
 *
 * @return array<string, array{title: string, description: string}>
 */
return [
    'dashboard' => ['title' => 'message.dashboard', 'description' => 'Overview of orders, invoices, and account activity.'],

    'profile' => ['title' => 'message.profile', 'description' => 'Manage your admin account profile and settings.'],

    'users' => ['title' => 'message.all-contacts', 'description' => 'Manage user accounts and permissions.'],
    'users/create' => ['title' => 'message.create_new_user', 'description' => 'Create a new user account.'],
    'users/*' => ['title' => 'message.user_details', 'description' => "View a user's account details."],
    'users/*/edit' => ['title' => 'message.edit_user', 'description' => "Edit a user's account details."],
    'users/*/payments/create' => ['title' => 'message.create-payment', 'description' => 'Record a new payment for this user.'],
    'users/*/payments/*/edit' => ['title' => 'message.edit-payment', 'description' => 'Edit a recorded payment for this user.'],
    'users/suspended' => ['title' => 'message.suspended_users', 'description' => 'View suspended user accounts.'],

    'orders' => ['title' => 'message.orders', 'description' => 'View and manage customer orders.'],
    'orders/*/renew' => ['title' => 'message.renew_order', 'description' => "Renew a customer's order."],
    'orders/*' => ['title' => 'Order Details', 'description' => 'View order details and status.'],

    'invoices' => ['title' => 'message.invoices', 'description' => 'View and manage customer invoices.'],
    'invoices/create' => ['title' => 'message.create-invoice', 'description' => 'Create a new invoice.'],
    'invoices/*' => ['title' => 'Invoice Details', 'description' => 'View invoice details and payment status.'],

    'pages' => ['title' => 'message.pages', 'description' => 'Manage frontend content pages.'],
    'pages/create' => ['title' => 'message.create_new_page', 'description' => 'Create a new content page.'],
    'pages/*/edit' => ['title' => 'message.edit_page', 'description' => 'Edit a content page.'],
    'pages/settings' => ['title' => 'message.page_settings', 'description' => 'Configure the demo request button and the default landing page.'],

    'products' => ['title' => 'message.products', 'description' => 'Manage products and pricing.'],
    'products/create' => ['title' => 'message.create_new_product', 'description' => 'Create a new product.'],
    'products/apply-build' => ['title' => 'message.apply_build_to_products', 'description' => 'Apply a shared build release to products.'],
    'products/*/edit' => ['title' => 'message.edit_product', 'description' => 'Edit a product.'],
    'products/*/versions/create' => ['title' => 'message.add_version', 'description' => 'Add a new version to this product.'],
    'products/*/versions/*/edit' => ['title' => 'message.edit_version', 'description' => 'Edit a product version.'],
    'products/plans' => ['title' => 'message.plans', 'description' => 'Manage pricing plans.'],
    'products/plans/create' => ['title' => 'message.create_plan', 'description' => 'Create a new pricing plan.'],
    'products/plans/*/edit' => ['title' => 'message.edit_plan', 'description' => 'Edit a pricing plan.'],
    'products/coupons' => ['title' => 'message.coupons', 'description' => 'Manage discount coupons.'],
    'products/coupons/create' => ['title' => 'message.create_new_coupon', 'description' => 'Create a new discount coupon.'],
    'products/coupons/*/edit' => ['title' => 'message.edit_coupon', 'description' => 'Edit a discount coupon.'],
    'products/groups' => ['title' => 'message.product_groups', 'description' => 'Manage product groups.'],
    'products/groups/create' => ['title' => 'message.create_group', 'description' => 'Create a new product group.'],
    'products/groups/*/edit' => ['title' => 'message.edit_group', 'description' => 'Edit a product group.'],

    'reports' => ['title' => 'message.reports', 'description' => 'View sales and usage reports.'],
    'reports/settings' => ['title' => 'message.report_settings', 'description' => 'Configure report settings.'],

    'settings' => ['title' => 'message.settings', 'description' => 'Configure application settings.'],
    'settings/company' => ['title' => 'message.company-settings', 'description' => 'Configure company details and branding.'],
    'settings/system' => ['title' => 'message.system-settings', 'description' => 'Configure system-wide settings.'],
    'settings/cron' => ['title' => 'message.cron-setting', 'description' => 'Configure scheduled tasks.'],
    'settings/license-type' => ['title' => 'message.license_types', 'description' => 'Manage license types.'],
    'settings/license-permissions' => ['title' => 'message.license_permission', 'description' => 'Configure license permissions.'],
    'settings/file-storage' => ['title' => 'message.file_system', 'description' => 'Configure file storage settings.'],
    'settings/payment-gateway' => ['title' => 'message.payment_gateways', 'description' => 'Manage payment gateways.'],
    'settings/payment-gateway/*/edit' => ['title' => 'message.edit_payment_gateway', 'description' => 'Edit a payment gateway.'],
    'settings/system-managers' => ['title' => 'message.system_manager_settings', 'description' => 'Manage system manager accounts.'],
    'settings/third-party-apps' => ['title' => 'message.third_party_apps', 'description' => 'Configure third-party app integrations.'],
    'settings/cloud-details' => ['title' => 'message.cloud_hub', 'description' => 'Configure cloud hosting details.'],
    'settings/localized-license' => ['title' => 'message.localized_license', 'description' => 'Configure localized license settings.'],
    'settings/debugging' => ['title' => 'message.debugging', 'description' => 'Configure debugging settings.'],
    'settings/social-logins' => ['title' => 'message.social_logins', 'description' => 'Manage social login providers.'],
    'settings/social-logins/*/edit' => ['title' => 'message.edit_social_login', 'description' => 'Edit a social login provider.'],
    'settings/language' => ['title' => 'message.language', 'description' => 'Configure language settings.'],
    'settings/whatsapp-users' => ['title' => 'message.whatsapp_users', 'description' => 'Manage WhatsApp users.'],
    'settings/whatsapp-integration' => ['title' => 'message.whatsapp_config', 'description' => 'Configure WhatsApp integration.'],
    'settings/contact-options' => ['title' => 'message.contact_options', 'description' => 'Configure contact options.'],
    'settings/open-payments' => ['title' => 'Open Payments', 'description' => 'Manage open payment links.'],
    'settings/deployment' => ['title' => 'message.deployment_settings', 'description' => 'Configure deployment settings.'],
    'settings/seo' => ['title' => 'message.seo_general_settings', 'description' => 'Configure SEO settings.'],
    'settings/seo/pages' => ['title' => 'message.seo_pages', 'description' => 'Manage default SEO pages.'],
    'settings/seo/*/edit' => ['title' => 'message.edit_seo', 'description' => 'Edit default page SEO.'],

    'settings/logs/system' => ['title' => 'message.log_setting', 'description' => 'View system logs.'],
    'settings/logs/activity' => ['title' => 'message.activity_logs', 'description' => 'View activity logs.'],
    'settings/logs/payment' => ['title' => 'message.payment_logs', 'description' => 'View payment logs.'],
    'settings/logs/msg91' => ['title' => 'message.msg_reports', 'description' => 'View MSG91 SMS reports.'],

    'settings/email/settings' => ['title' => 'message.email_settings', 'description' => 'Configure email settings.'],
    'settings/email/template-settings' => ['title' => 'message.template_settings', 'description' => 'Configure email template settings.'],
    'settings/email/templates' => ['title' => 'message.email_templates', 'description' => 'Manage email templates.'],
    'settings/email/templates/*/edit' => ['title' => 'message.edit_template', 'description' => 'Edit an email template.'],

    'settings/api/pipedrive' => ['title' => 'message.pipedrive', 'description' => 'Configure the Pipedrive integration.'],
    'settings/api/recaptcha' => ['title' => 'message.recaptcha', 'description' => 'Configure reCAPTCHA settings.'],
    'settings/api/third-party' => ['title' => 'message.third_party_integrations', 'description' => 'Configure third-party integrations.'],
    'settings/api/msg91' => ['title' => 'message.msg91_heading', 'description' => 'Configure MSG91 SMS settings.'],
    'settings/api/github' => ['title' => 'message.github_settings', 'description' => 'Configure GitHub integration settings.'],
    'settings/api/mailchimp' => ['title' => 'message.mailchimp', 'description' => 'Configure Mailchimp integration settings.'],
    'settings/api/terms' => ['title' => 'message.terms_heading', 'description' => 'Configure terms and conditions.'],
    'settings/api/email-validation' => ['title' => 'message.email_provider', 'description' => 'Configure email validation settings.'],
    'settings/api/email-validation/logs' => ['title' => 'message.email_validation_logs', 'description' => 'View email validation logs.'],
    'settings/api/mobile-validation' => ['title' => 'message.mobile_provider', 'description' => 'Configure mobile number validation settings.'],
    'settings/api/zoho' => ['title' => 'message.zoho_integration', 'description' => 'Configure the Zoho integration.'],
    'settings/api/zoho/*' => ['title' => 'message.zoho_platform_settings', 'description' => 'Configure Zoho platform settings.'],

    'settings/common/tax' => ['title' => 'message.tax', 'description' => 'Manage tax rates.'],
    'settings/common/tax/create' => ['title' => 'message.create_tax', 'description' => 'Create a new tax rate.'],
    'settings/common/tax/*/edit' => ['title' => 'message.edit_tax', 'description' => 'Edit a tax rate.'],
    'settings/common/currency' => ['title' => 'message.currency', 'description' => 'Manage supported currencies.'],
    'settings/common/countries' => ['title' => 'message.countries', 'description' => 'Manage supported countries.'],
    'settings/common/queues' => ['title' => 'message.queues', 'description' => 'View and manage queue workers.'],
    'settings/common/queue/*' => ['title' => 'message.queue', 'description' => 'Configure a queue.'],
    'settings/common/cache' => ['title' => 'message.cache', 'description' => 'Manage the application cache.'],
    'settings/common/cache/*' => ['title' => 'message.cache_driver_settings', 'description' => 'Configure a cache driver.'],

    'settings/widgets/footer' => ['title' => 'message.footer_widget', 'description' => 'Configure the footer widget.'],
    'settings/widgets/social-media' => ['title' => 'message.social_media', 'description' => 'Manage social media links.'],
    'settings/widgets/social-media/create' => ['title' => 'message.create_new_social_media', 'description' => 'Add a new social media link.'],
    'settings/widgets/social-media/*/edit' => ['title' => 'message.edit_social_media', 'description' => 'Edit a social media link.'],
    'settings/widgets/analytics' => ['title' => 'message.analytics', 'description' => 'Manage analytics integrations.'],
    'settings/widgets/analytics/create' => ['title' => 'message.add_analytics', 'description' => 'Add a new analytics integration.'],
    'settings/widgets/analytics/*/edit' => ['title' => 'message.edit_analytics', 'description' => 'Edit an analytics integration.'],

    'versions' => ['title' => 'message.versions', 'description' => 'Manage product versions.'],
    'versions/list' => ['title' => 'message.versions', 'description' => 'Manage product versions.'],
    'versions/*/view' => ['title' => 'message.version_view', 'description' => 'View version details.'],

    'licenses' => ['title' => 'message.licenses', 'description' => 'Manage customer licenses.'],
    'licenses/list' => ['title' => 'message.licenses', 'description' => 'Manage customer licenses.'],
    'licenses/create' => ['title' => 'message.new_license', 'description' => 'Create a new license.'],
    'licenses/*/edit' => ['title' => 'message.edit_license', 'description' => 'Edit a license.'],
    'licenses/*/view' => ['title' => 'message.license_view', 'description' => 'View license details.'],

    'installations' => ['title' => 'message.installations', 'description' => 'View license installations.'],
    'installations/list' => ['title' => 'message.installations', 'description' => 'View license installations.'],
    'installations/*/edit' => ['title' => 'message.edit_installation', 'description' => 'Edit an installation.'],
    'installations/*/view' => ['title' => 'message.installation_view', 'description' => 'View installation details.'],

    'callbacks' => ['title' => 'message.callbacks', 'description' => 'View license server callback events.'],
    'callbacks/list' => ['title' => 'message.callbacks', 'description' => 'View license server callback events.'],

    'banned-hosts' => ['title' => 'message.banned_hosts', 'description' => 'Manage banned hosts and IP restrictions.'],
    'banned-hosts/list' => ['title' => 'message.all_banned_hosts', 'description' => 'Manage banned hosts and IP restrictions.'],
    'banned-hosts/create' => ['title' => 'message.new_banned_host', 'description' => 'Ban a new host.'],
    'banned-hosts/*/edit' => ['title' => 'message.edit_banned_host', 'description' => 'Edit a banned host.'],
    'banned-hosts/settings' => ['title' => 'message.security_settings', 'description' => 'Configure auto-ban for repeated failed license checks.'],

    'server' => ['title' => 'message.server_notifications', 'description' => 'Server notifications and update settings.'],
    'server/notifications' => ['title' => 'message.license_custom_notification', 'description' => 'Customize license server notifications.'],
    'server/update-notifications' => ['title' => 'message.update_custom_notification', 'description' => 'Customize update notifications.'],

    'log-reports' => ['title' => 'Log Reports', 'description' => 'View license, update, and system log reports.'],
    'log-reports/crack' => ['title' => 'Cracking Reports', 'description' => 'View license cracking reports.'],
    'log-reports/license' => ['title' => 'License Reports', 'description' => 'View license usage reports.'],
    'log-reports/update' => ['title' => 'Update Reports', 'description' => 'View update reports.'],
    'log-reports/system' => ['title' => 'System Reports', 'description' => 'View system reports.'],

    // Client-only error routes — no server-rendered equivalent, but still
    // listed so the frontend never needs a hardcoded title/description either.
    '404' => ['title' => 'Not Found', 'description' => 'The page you requested could not be found.'],
    '403' => ['title' => 'Forbidden', 'description' => "You don't have permission to access this page."],
    '500' => ['title' => 'Server Error', 'description' => 'Something went wrong on our end.'],
    '*' => ['title' => 'Not Found', 'description' => 'The page you requested could not be found.'],
];
