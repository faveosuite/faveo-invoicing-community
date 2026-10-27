<?php

declare(strict_types=1);

return [
    /*
     * Cart page
     */
    'cart' => [
        'group' => '{{group}}',
        'name' => '{{name}}',
        'price' => '{{price}}',
        'price-year' => '{{price-year}}',
        'price-description' => '{{price-description}}',
        'pricemonth-description' => '{{pricemonth-description}}',
        'strike-price' => '{{strike-price}}',
        'strike-priceyear' => '{{strike-priceyear}}',
        'feature' => '<li>{{feature}}</li>',
        'product_description' => '{{product_description}}',
        'subscription' => '{{subscription}}',
        'subscription-year' => '{{subscription-year}}',
        'url' => '{{url}}',

    ],
    /*
     * This is for welcome mail content
     */
    'welcome_mail' => [
        'website_url' => '{{website_url}}',
        'name' => '{{name}}',
        'username' => '{{username}}',
        'otp' => '{{otp}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
        'reply_email' => '{{reply_email}}',

    ],

    /*
     * This is for order mail content
     */
    'order_mail' => [
        'name' => '{{name}}',
        'downloadurl' => '{{downloadurl}}',
        'invoiceurl' => '{{invoiceurl}}',
        'product' => '{{product}}',
        'number' => '{{number}}',
        'expiry' => '{{expiry}}',
        'url' => '{{url}}',
        'serialkeyurl' => '{{serialkeyurl}}',
        'knowledge_base' => '{{knowledge_base}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
        'orderHeading' => '{{orderHeading}}',
        'reply_email' => '{{reply_email}}',
        'licenseCode' => '{{licenseCode}}',

    ],

    'cloud_order' => [
        'name' => '{{name}}',
        'downloadurl' => '{{downloadurl}}',
        'invoiceurl' => '{{invoiceurl}}',
        'product' => '{{product}}',
        'number' => '{{number}}',
        'expiry' => '{{expiry}}',
        'url' => '{{url}}',
        'serialkeyurl' => '{{serialkeyur}}',
        'knowledge_base' => '{{knowledge_base}}',
        'reply_email' => '{{reply_email}}',
    ],

    /*
     * This is for invoice mail content
     */
    'invoice_mail' => [
        'name' => '{{name}}',
        'number' => '{{number}}',
        'address' => '{{address}}',
        'invoiceurl' => '{{invoiceurl}}',
        'total' => '{{total}}',
        'content' => '{{content}}',
        'currency' => '{{currency}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
        'reply_email' => '{{reply_email}}',
    ],

    /*
     * This is for forgot password mail content
     */
    'forgot_password_mail' => [
        'name' => '{{name}}',
        'url' => '{{url}}',
        'contact_us' => '{{contact-us}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
        'reply_email' => '{{reply_email}}',

    ],

    'subscription_going_to_end_mail' => [
        'name' => '{{name}}',
        'number' => '{{number}}',
        'product' => '{{product}}',
        'expiry' => '{{expiry}}',
        'url' => '{{url}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
        'product_type' => '{{product_type}}',
        'deletionDate' => '{{deletionDate}}',
        'reply_email' => '{{reply_email}}',
    ],

    'subscription_over_mail' => [
        'name' => '{{name}}',
        'number' => '{{number}}',
        'product' => '{{product}}',
        'expiry' => '{{expiry}}',
        'url' => '{{url}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
        'product_type' => '{{product_type}}',
        'deletionDate' => '{{deletionDate}}',
        'reply_email' => '{{reply_email}}',
    ],

    'sales_manager_email' => [
        'name' => '{{name}}',
        'manager_first_name' => '{{manager_first_name}}',
        'manager_last_name' => '{{manager_last_name}}',
        'manager_email' => '{{manager_email}}',
        'manager_code' => '{{manager_code}}',
        'manager_mobile' => '{{manager_mobile}}',
        'manager_skype' => '{{manager_skype}}',
        'reply_email' => '{{reply_email}}',
    ],

    'account_manager_email' => [
        'name' => '{{name}}',
        'manager_first_name' => '{{manager_first_name}}',
        'manager_last_name' => '{{manager_last_name}}',
        'manager_email' => '{{manager_email}}',
        'manager_code' => '{{manager_code}}',
        'manager_mobile' => '{{manager_mobile}}',
        'manager_skype' => '{{manager_skype}}',
        'reply_email' => '{{reply_email}}',
    ],

    'auto_subscription_going_to_end' => [
        'name' => '{{name}}',
        'number' => '{{number}}',
        'product' => '{{product}}',
        'expiry' => '{{expiry}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
        'renewPrice' => '{{renewPrice}}',
        'product_type' => '{{product_type}}',
        'deletionDate' => '{{deletionDate}}',
        'reply_email' => '{{reply_email}}',
    ],

    'payment_failed' => [
        'name' => '{{name}}',
        'product' => '{{product}}',
        'total' => '{{total}}',
        'number' => '{{number}}',
        'expiry' => '{{expiry}}',
        'exception' => '{{exception}}',
        'url' => '{{url}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
        'reply_email' => '{{reply_email}}',
    ],
    'payment_successfull' => [
        'name' => '{{name}}',
        'product' => '{{product}}',
        'total' => '{{total}}',
        'number' => '{{number}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
        'future_expiry' => '{{future_expiry}}',
        'reply_email' => '{{reply_email}}',
    ],

    'cloud_deleted' => [
        'name' => '{{name}}',
        'product' => '{{product}}',
        'number' => '{{number}}',
        'expiry' => '{{expiry}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
        'reply_email' => '{{reply_email}}',
    ],

    'cloud_created' => [
        'name' => '{{name}}',
        'message' => '{{message}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
        'title' => '{{title}}',
        'company_email' => '{{company_email}}',
        'reply_email' => '{{reply_email}}',
    ],

    'contact_us' => [
        'name' => '{{name}}',
        'message' => '{{message}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
        'title' => '{{title}}',
        'email' => '{{email}}',
        'mobile' => '{{mobile}}',
        'ip_address' => '{{ip_address}}',
        'request_url' => '{{request_url}}',
        'reply_email' => '{{reply_email}}',
    ],

    'demo_request' => [
        'name' => '{{name}}',
        'message' => '{{message}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
        'title' => '{{title}}',
        'email' => '{{email}}',
        'mobile' => '{{mobile}}',
        'ip_address' => '{{ip_address}}',
        'request_url' => '{{request_url}}',
        'reply_email' => '{{reply_email}}',
    ],

    'stripe_subscription_authentication' => [
        'name' => '{{name}}',
        'product' => '{{product}}',
        'total' => '{{total}}',
        'contact' => '{{contact}}',
        'logo' => '{{logo}}',
        'expiry_date' => '{{expiry_date}}',
        'reply_email' => '{{reply_email}}',
        'application_title' => '{{application_title}}',
        'company_title' => '{{company_title}}',
        'url' => '{{url}}',
        'number' => '{{number}}',
        'date' => '{{date}}',
    ],

    'razorpay_autorenew_setup' => [
        'name' => '{{name}}',
        'product' => '{{product}}',
        'total' => '{{total}}',
        'contact' => '{{contact}}',
        'logo' => '{{logo}}',
        'expiry_date' => '{{expiry_date}}',
        'reply_email' => '{{reply_email}}',
        'application_title' => '{{application_title}}',
        'company_title' => '{{company_title}}',
        'url' => '{{url}}',
        'number' => '{{number}}',
        'date' => '{{date}}',
    ],

    'registration_mail' => [
        'name' => '{{name}}',
        'username' => '{{username}}',
        'password' => '{{password}}',
        'website_url' => '{{website_url}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
        'reply_email' => '{{reply_email}}',
    ],

    'verify_new_email' => [
        'logo' => '{{logo}}',
        'name' => '{{name}}',
        'otp' => '{{otp}}',
        'app_name' => '{{app_name}}',
        'contact' => '{{contact}}',
        'contact_url' => '{{contact_url}}',
    ],

    'confirm_old_email' => [
        'logo' => '{{logo}}',
        'name' => '{{name}}',
        'otp' => '{{otp}}',
        'app_name' => '{{app_name}}',
        'contact' => '{{contact}}',
        'contact_url' => '{{contact_url}}',
    ],

    'confirm_mobile_number_change' => [
        'logo' => '{{logo}}',
        'name' => '{{name}}',
        'otp' => '{{otp}}',
        'app_name' => '{{app_name}}',
        'contact' => '{{contact}}',
        'contact_url' => '{{contact_url}}',
    ],

    'open_payment_success' => [
        'name' => '{{name}}',
        'transaction_id' => '{{transaction_id}}',
        'currency' => '{{currency}}',
        'amount' => '{{amount}}',
        'gateway' => '{{gateway}}',
        'date' => '{{date}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
    ],

    'open_payment_failed' => [
        'name' => '{{name}}',
        'currency' => '{{currency}}',
        'amount' => '{{amount}}',
        'gateway' => '{{gateway}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
    ],

    'open_payment_admin_success' => [
        'name' => '{{name}}',
        'company' => '{{company}}',
        'email' => '{{email}}',
        'currency' => '{{currency}}',
        'base_amount' => '{{base_amount}}',
        'processing_fee' => '{{processing_fee}}',
        'fee_rate' => '{{fee_rate}}',
        'amount' => '{{amount}}',
        'gateway' => '{{gateway}}',
        'transaction_id' => '{{transaction_id}}',
        'date' => '{{date}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
    ],

    'open_payment_admin_failed' => [
        'name' => '{{name}}',
        'company' => '{{company}}',
        'email' => '{{email}}',
        'currency' => '{{currency}}',
        'amount' => '{{amount}}',
        'gateway' => '{{gateway}}',
        'date' => '{{date}}',
        'logo' => '{{logo}}',
        'contact' => '{{contact}}',
    ],
];
