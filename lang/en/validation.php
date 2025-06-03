<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'The :attribute must be accepted.',
    'accepted_if' => 'The :attribute must be accepted when :other is :value.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute must only contain letters.',
    'alpha_dash' => 'The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute must only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'between' => [
        'array' => 'The :attribute must have between :min and :max items.',
        'file' => 'The :attribute must be between :min and :max kilobytes.',
        'numeric' => 'The :attribute must be between :min and :max.',
        'string' => 'The :attribute must be between :min and :max characters.',
    ],
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'current_password' => 'The password is incorrect.',
    'date' => 'The :attribute is not a valid date.',
    'date_equals' => 'The :attribute must be a date equal to :date.',
    'date_format' => 'The :attribute does not match the format :format.',
    'declined' => 'The :attribute must be declined.',
    'declined_if' => 'The :attribute must be declined when :other is :value.',
    'different' => 'The :attribute and :other must be different.',
    'digits' => 'The :attribute must be :digits digits.',
    'digits_between' => 'The :attribute must be between :min and :max digits.',
    'dimensions' => 'The :attribute has invalid image dimensions.',
    'distinct' => 'The :attribute field has a duplicate value.',
    'doesnt_start_with' => 'The :attribute may not start with one of the following: :values.',
    'email' => 'The :attribute must be a valid email address.',
    'ends_with' => 'The :attribute must end with one of the following: :values.',
    'enum' => 'The selected :attribute is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'file' => 'The :attribute must be a file.',
    'filled' => 'The :attribute field must have a value.',
    'gt' => [
        'array' => 'The :attribute must have more than :value items.',
        'file' => 'The :attribute must be greater than :value kilobytes.',
        'numeric' => 'The :attribute must be greater than :value.',
        'string' => 'The :attribute must be greater than :value characters.',
    ],
    'gte' => [
        'array' => 'The :attribute must have :value items or more.',
        'file' => 'The :attribute must be greater than or equal to :value kilobytes.',
        'numeric' => 'The :attribute must be greater than or equal to :value.',
        'string' => 'The :attribute must be greater than or equal to :value characters.',
    ],
    'image' => 'The :attribute must be an image.',
    'in' => 'The selected :attribute is invalid.',
    'in_array' => 'The :attribute field does not exist in :other.',
    'integer' => 'The :attribute must be an integer.',
    'ip' => 'The :attribute must be a valid IP address.',
    'ipv4' => 'The :attribute must be a valid IPv4 address.',
    'ipv6' => 'The :attribute must be a valid IPv6 address.',
    'json' => 'The :attribute must be a valid JSON string.',
    'lt' => [
        'array' => 'The :attribute must have less than :value items.',
        'file' => 'The :attribute must be less than :value kilobytes.',
        'numeric' => 'The :attribute must be less than :value.',
        'string' => 'The :attribute must be less than :value characters.',
    ],
    'lte' => [
        'array' => 'The :attribute must not have more than :value items.',
        'file' => 'The :attribute must be less than or equal to :value kilobytes.',
        'numeric' => 'The :attribute must be less than or equal to :value.',
        'string' => 'The :attribute must be less than or equal to :value characters.',
    ],
    'mac_address' => 'The :attribute must be a valid MAC address.',
    'max' => [
        'array' => 'The :attribute must not have more than :max items.',
        'file' => 'The :attribute must not be greater than :max kilobytes.',
        'numeric' => 'The :attribute must not be greater than :max.',
        'string' => 'The :attribute must not be greater than :max characters.',
    ],
    'mimes' => 'The :attribute must be a file of type: :values.',
    'mimetypes' => 'The :attribute must be a file of type: :values.',
    'min' => [
        'array' => 'The :attribute must have at least :min items.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'numeric' => 'The :attribute must be at least :min.',
        'string' => 'The :attribute must be at least :min characters.',
    ],
    'multiple_of' => 'The :attribute must be a multiple of :value.',
    'not_in' => 'The selected :attribute is invalid.',
    'not_regex' => 'The :attribute format is invalid.',
    'numeric' => 'The :attribute must be a number.',
    'password' => [
        'letters' => 'The :attribute must contain at least one letter.',
        'mixed' => 'The :attribute must contain at least one uppercase and one lowercase letter.',
        'numbers' => 'The :attribute must contain at least one number.',
        'symbols' => 'The :attribute must contain at least one symbol.',
        'uncompromised' => 'The given :attribute has appeared in a data leak. Please choose a different :attribute.',
    ],
    'present' => 'The :attribute field must be present.',
    'prohibited' => 'The :attribute field is prohibited.',
    'prohibited_if' => 'The :attribute field is prohibited when :other is :value.',
    'prohibited_unless' => 'The :attribute field is prohibited unless :other is in :values.',
    'prohibits' => 'The :attribute field prohibits :other from being present.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_array_keys' => 'The :attribute field must contain entries for: :values.',
    'required_if' => 'The :attribute field is required when :other is :value.',
    'required_unless' => 'The :attribute field is required unless :other is in :values.',
    'required_with' => 'The :attribute field is required when :values is present.',
    'required_with_all' => 'The :attribute field is required when :values are present.',
    'required_without' => 'The :attribute field is required when :values is not present.',
    'required_without_all' => 'The :attribute field is required when none of :values are present.',
    'same' => 'The :attribute and :other must match.',
    'size' => [
        'array' => 'The :attribute must contain :size items.',
        'file' => 'The :attribute must be :size kilobytes.',
        'numeric' => 'The :attribute must be :size.',
        'string' => 'The :attribute must be :size characters.',
    ],
    'starts_with' => 'The :attribute must start with one of the following: :values.',
    'string' => 'The :attribute must be a string.',
    'timezone' => 'The :attribute must be a valid timezone.',
    'unique' => 'The :attribute has already been taken.',
    'uploaded' => 'The :attribute failed to upload.',
    'url' => 'The :attribute must be a valid URL.',
    'uuid' => 'The :attribute must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom_dup' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],
    'publish_date_required' => 'Publish Date is required',
    'price_numeric_value' => 'Price should be a numeric value',
    'quantity_integer_value' => 'Quantity should be a integer value',
    'order_has_Expired' => 'Order has Expired',
    'expired' => 'Expired',
    'eid_required' => 'The EID field is required.',
    'eid_string' => 'The EID must be a string.',
    'otp_required' => 'The OTP field is required.',
    'amt_required' => 'The amount field is required',
    'amt_numeric' => 'The amount must be a number',
    'payment_date_required' => 'Payment date is required.',
    'payment_method_required' => 'Payment method is required.',
    'total_amount_required' => 'Total amount is required.',
    'total_amount_numeric' => 'Total amount must be a numeric value.',
    'invoice_link_required' => 'Please link the amount with at least one Invoice.',
    /*
   Request file custom validation messages
   */

    //Common
    'settings_form' => [
        'company' => [
            'required' => 'The company field is required.',
        ],
        'website' => [
            'url' => 'The website must be a valid URL.',
        ],
        'phone' => [
            'regex' => 'The phone number format is invalid.',
        ],
        'address' => [
            'required' => 'The address field is required.',
            'max' => 'The address may not be greater than 300 characters.',
        ],
        'logo' => [
            'mimes' => 'The logo must be a PNG file.',
        ],
        'driver' => [
            'required' => 'The driver field is required.',
        ],
        'port' => [
            'integer' => 'The port must be an integer.',
        ],
        'email' => [
            'required' => 'The email field is required.',
            'email' => 'The email must be a valid email address.',
        ],
        'password' => [
            'required' => 'The password field is required.',
        ],
        'error_email' => [
            'email' => 'The error email must be a valid email address.',
        ],
    ],

    'settings_forms' => [
        'company' => [
            'required' => 'The company name is required.',
            'max' => 'The company name must not exceed 50 characters.',
        ],
        'company_email' => [
            'required' => 'The company email is required.',
            'email' => 'The company email must be a valid email address.',
        ],
        'title' => [
            'max' => 'The title must not exceed 50 characters.',
        ],
        'website' => [
            'required' => 'The website URL is required.',
            'url' => 'The website must be a valid URL.',
            'regex' => 'The website format is invalid.',
        ],
        'phone' => [
            'required' => 'The phone number is required.',
        ],
        'address' => [
            'required' => 'The address is required.',
        ],
        'state' => [
            'required' => 'The state is required.',
        ],
        'country' => [
            'required' => 'The country is required.',
        ],
        'gstin' => [
            'max' => 'The GSTIN must not exceed 15 characters.',
        ],
        'default_currency' => [
            'required' => 'The default currency is required.',
        ],
        'admin_logo' => [
            'mimes' => 'The admin logo must be a file of type: jpeg, png, jpg.',
            'max' => 'The admin logo may not be greater than 2MB.',
        ],
        'fav_icon' => [
            'mimes' => 'The fav icon must be a file of type: jpeg, png, jpg.',
            'max' => 'The fav icon may not be greater than 2MB.',
        ],
        'logo' => [
            'mimes' => 'The logo must be a file of type: jpeg, png, jpg.',
            'max' => 'The logo may not be greater than 2MB.',
        ],
    ],

    'social_media_form' => [
        'name' => [
            'required' => 'The name field is required.',
            'unique' => 'This name already exists.',
            'max' => 'The name may not be greater than 50 characters.',
        ],
        'link' => [
            'required' => 'The link field is required.',
            'url' => 'The link must be a valid URL.',
            'regex' => 'The link format is invalid.',
        ],
    ],

    //Email
    'custom' => [
        'password' => [
            'required_if' => 'The password field is required for the selected mail driver.',
        ],
        'port' => [
            'required_if' => 'The port field is required for SMTP.',
        ],
        'encryption' => [
            'required_if' => 'The encryption field is required for SMTP.',
        ],
        'host' => [
            'required_if' => 'The host field is required for SMTP.',
        ],
        'secret' => [
            'required_if' => 'The secret field is required for the selected mail driver.',
        ],
        'domain' => [
            'required_if' => 'The domain field is required for Mailgun.',
        ],
        'key' => [
            'required_if' => 'The key field is required for SES.',
        ],
        'region' => [
            'required_if' => 'The region field is required for SES.',
        ],
        'email' => [
            'required_if' => 'The email field is required for the selected mail driver.',
            'required' => 'The email field is required.',
            'email' => 'Please enter a valid email address.',
            'not_matching' => 'The email domain must match the current site domain.',
        ],
        'driver' => [
            'required' => 'The driver field is required.',
        ],
    ],

    'customer_form' => [
        'first_name' => [
            'required' => 'The first name field is required.',
        ],
        'last_name' => [
            'required' => 'The last name field is required.',
        ],
        'company' => [
            'required' => 'The company field is required.',
        ],
        'mobile' => [
            'regex' => 'The mobile number format is invalid.',
        ],
        'address' => [
            'required' => 'The address field is required.',
        ],
        'zip' => [
            'required' => 'The zip code field is required.',
            'min' => 'The zip code must be at least 5 digits.',
            'numeric' => 'The zip code must be numeric.',
        ],
        'email' => [
            'required' => 'The email field is required.',
            'email' => 'The email must be a valid email address.',
            'unique' => 'This email is already taken.',
        ],
    ],

    'contact_request' => [
        'conName' => 'The name field is required.',
        'email' => 'The email field is required.',
        'conmessage' => 'The message field is required.',
        'Mobile' => 'The Mobile field is required.',
        'country_code' => 'The Mobile field is required.',
        'demoname' => 'The name field is required.',
        'demomessage' => 'The message field is required.',
        'demoemail' => 'The email field is required.',
        'congg-recaptcha-response-1.required' => 'Robot Verification Failed. Please Try Again.',
        'demo-recaptcha-response-1.required' => 'Robot Verification Failed. Please Try Again.',
    ],

    'frontend_pages' => [
        'name' => [
            'required' => 'The name field is required.',
            'unique' => 'This name already exists.',
            'max' => 'The name must not exceed 20 characters.',
            'regex' => 'The name may only contain letters and spaces.',
        ],
        'publish' => [
            'required' => 'The publish field is required.',
        ],
        'slug' => [
            'required' => 'The slug field is required.',
        ],
        'url' => [
            'required' => 'The URL field is required.',
            'url' => 'The URL must be a valid link.',
            'regex' => 'The URL format is invalid.',
        ],
        'content' => [
            'required' => 'The content field is required.',
        ],
        'created_at' => [
            'required' => 'The created at field is required.',
        ],
    ],

    //Order form
    'order_form' => [
        'client' => [
            'required' => 'The client field is required.',
        ],
        'payment_method' => [
            'required' => 'The payment method field is required.',
        ],
        'promotion_code' => [
            'required' => 'The promotion code field is required.',
        ],
        'order_status' => [
            'required' => 'The order status field is required.',
        ],
        'product' => [
            'required' => 'The product field is required.',
        ],
        'subscription' => [
            'required' => 'The subscription field is required.',
        ],
        'price_override' => [
            'numeric' => 'The price override must be a number.',
        ],
        'qty' => [
            'integer' => 'The quantity must be an integer.',
        ],
    ],

    //Payment form
    'coupon_form' => [
        'code' => [
            'required' => 'The coupon code field is required.',
            'string' => 'The coupon code must be a string.',
            'max' => 'The coupon code must not exceed 255 characters.',
        ],
        'type' => [
            'required' => 'The type field is required.',
            'in' => 'Invalid type. Allowed values are: percentage, other_type.',
        ],
        'applied' => [
            'required' => 'The applied for a product field is required.',
            'date' => 'The applied for a product field must be a valid date.',
        ],
        'uses' => [
            'required' => 'The uses field is required.',
            'numeric' => 'The uses field must be a number.',
            'min' => 'The uses field must be at least :min.',
        ],
        'start' => [
            'required' => 'The start field is required.',
            'date' => 'The start field must be a valid date.',
        ],
        'expiry' => [
            'required' => 'The expiry field is required.',
            'date' => 'The expiry field must be a valid date.',
            'after' => 'The expiry date must be after the start date.',
        ],
        'value' => [
            'required' => 'The discount value field is required.',
            'numeric' => 'The discount value field must be a number.',
            'between' => 'The discount value field must be between :min and :max if the type is percentage.',
        ],
    ],

    'tax_form' => [
        'name' => [
            'required' => 'The name field is required.',
        ],
        'rate' => [
            'required' => 'The rate field is required.',
            'numeric' => 'The rate must be a number.',
        ],
        'level' => [
            'required' => 'The level field is required.',
            'integer' => 'The level must be an integer.',
        ],
        'country' => [
            'required' => 'The country field is required.',
            // 'exists' => 'The selected country is invalid.',
        ],
        'state' => [
            'required' => 'The state field is required.',
            // 'exists' => 'The selected state is invalid.',
        ],
    ],

    //Product
    'subscription_form' => [
        'name' => [
            'required' => 'The name field is required.',
        ],
        'subscription' => [
            'required' => 'The subscription field is required.',
        ],
        'regular_price' => [
            'required' => 'The regular price field is required.',
            'numeric' => 'The regular price must be a number.',
        ],
        'selling_price' => [
            'required' => 'The selling price field is required.',
            'numeric' => 'The selling price must be a number.',
        ],
        'products' => [
            'required' => 'The products field is required.',
        ],
    ],

    'bundle' => [
        'name' => [
            'required' => 'The name field is required.',
        ],
        'items' => [
            'required' => 'Each item is required.',
        ],
    ],

    'group' => [
        'name' => [
            'required' => 'The name is required',
        ],
        'features' => [
            'name' => [
                'required' => 'All Features Field Required',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => 'The price is required',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => 'The value is required',
            ],
        ],
        'type' => [
            'required_with' => 'The type is required',
        ],
        'title' => [
            'required_with' => 'The title is required',
        ],
    ],

    'product' => [
        'name' => [
            'required' => 'The name field is required.',
        ],
        'type' => [
            'required' => 'The type field is required.',
        ],
        'group' => [
            'required' => 'The group field is required.',
        ],
        'subscription' => [
            'required' => 'The subscription field is required.',
        ],
        'currency' => [
            'required' => 'The currency field is required.',
        ],
        // 'price' => [
        //     'required' => 'The price field is required.',
        // ],
        'file' => [
            'required_without_all' => 'The file field is required if none of github_owner or github_repository are provided.',
            'mimes' => 'The file must be a zip file.',
        ],
        'image' => [
            'required_without_all' => 'The image field is required if none of github_owner or github_repository are provided.',
            'mimes' => 'The image must be a PNG file.',
        ],
        'github_owner' => [
            'required_without_all' => 'The GitHub owner field is required if none of file or image are provided.',
        ],
        'github_repository' => [
            'required_without_all' => 'The GitHub repository field is required if none of file or image are provided.',
            'required_if' => 'The GitHub repository field is required if type is 2.',
        ],
    ],

    //User
    'users' => [
        'first_name' => [
            'required' => 'The first name field is required.',
        ],
        'last_name' => [
            'required' => 'The last name field is required.',
        ],
        'company' => [
            'required' => 'The company field is required.',
        ],
        'email' => [
            'required' => 'The email field is required.',
            'email' => 'The email must be a valid email address.',
            'unique' => 'The email has already been taken.',
        ],
        'address' => [
            'required' => 'The address field is required.',
        ],
        'mobile' => [
            'required' => 'The mobile field is required.',
        ],
        'country' => [
            'required' => 'The country field is required.',
            'exists' => 'The selected country is invalid.',
        ],
        'state' => [
            'required_if' => 'The state field is required when country is India.',
        ],
        'timezone_id' => [
            'required' => 'The timezone field is required.',
        ],
        'user_name' => [
            'required' => 'The user name field is required.',
            'unique' => 'The user name has already been taken.',
        ],
        'zip' => [
            'regex' => 'The state field is required when country is India.',
        ],
    ],

    'profile_form' => [
        'first_name' => [
            'required' => 'First name is required.',
            'min' => 'First name must be at least :min characters.',
            'max' => 'First name may not be greater than :max characters.',
        ],
        'last_name' => [
            'required' => 'Last name is required.',
            'max' => 'Last name may not be greater than :max characters.',
        ],
        'company' => [
            'required' => 'Company name is required.',
            'max' => 'Company name may not be greater than :max characters.',
        ],
        'email' => [
            'required' => 'Email is required.',
            'email' => 'Enter a valid email address.',
            'unique' => 'The email address has already been taken. Please choose a different email.',
        ],
        'mobile' => [
            'required' => 'Mobile number is required.',
            'regex' => 'Enter a valid mobile number.',
            'min' => 'Mobile number must be at least :min characters.',
            'max' => 'Mobile number may not be greater than :max characters.',
        ],
        'address' => [
            'required' => 'Address is required.',
        ],
        'user_name' => [
            'required' => 'Username is required.',
            'unique' => 'This username is already taken.',
        ],
        'timezone_id' => [
            'required' => 'Timezone is required.',
        ],
        'country' => [
            'required' => 'Country is required.',
            'exists' => 'Selected country is invalid.',
        ],
        'state' => [
            'required_if' => 'The state field is required when country is India.',
        ],
        'old_password' => [
            'required' => 'Old password is required.',
            'min' => 'Old password must be at least :min characters.',
        ],
        'new_password' => [
            'required' => 'New password is required.',
            'different' => 'The new password must be different from the old password.',
        ],
        'confirm_password' => [
            'required' => 'Confirm password is required.',
            'same' => 'Confirm password must match new password.',
        ],
        'terms' => [
            'required' => 'You must accept the terms.',
        ],
        'password' => [
            'required' => 'Password is required.',
        ],
        'password_confirmation' => [
            'required' => 'Password confirmation is required.',
            'same' => 'Passwords do not match.',
        ],
        'mobile_code' => [
            'required' => 'Enter Country code (mobile)',
        ],
    ],

    //Invoice form
    'invoice' => [
        'user' => [
            'required' => 'The clients field is required.',
        ],
        'date' => [
            'required' => 'The date field is required.',
            'date' => 'The date must be a valid date.',
        ],
        'domain' => [
            'regex' => 'The domain format is invalid.',
        ],
        'plan' => [
            'required_if' => 'The subscription field is required.',
        ],
        'price' => [
            'required' => 'The price field is required.',
        ],
        'product' => [
            'required' => 'The product field is required.',
        ],
    ],

    //LocalizedLicense form
    'domain_form' => [
        'domain' => [
            'required' => 'The domain field is required.',
            'url' => 'The domain must be a valid URL.',
        ],
    ],

    //Product Renewal form
    'product_renewal' => [
        'domain' => [
            'required' => 'The domain field is required.',
            'no_http' => 'The domain must not contain "http" or "https".',
        ],
    ],

    //Language form
    'language' => [
        'required' => 'The language field is required.',
        'invalid' => 'The selected language is invalid.',
    ],

    //UpdateSroragePathRequest form
    'storage_path' => [
        'disk' => [
            'required' => 'The storage disk field is required.',
            'string' => 'The disk must be a string.',
        ],
        'path' => [
            'string' => 'The path must be a string.',
            'nullable' => 'The path field is optional.',
        ],
    ],

    //ValidateSecretRequest form
    'validate_secret' => [
        'totp' => [
            'required' => 'Please enter code',
            'digits' => 'Please enter the valid 6 digit code',
        ],
    ],

    //VerifyOtp form
    'verify_email' => [
        'required' => 'The email field is required.',
        'email' => 'The email must be a valid email address.',
        'verify_email' => 'The email verification failed.', // Custom message for verify_email
    ],

    'verify_country_code' => [
        'required' => 'The country code is required.',
        'numeric' => 'The country code must be a valid number.',
        'verify_country_code' => 'The country code verification failed.', // Custom message for verify_country_code
    ],

    'verify_number' => [
        'required' => 'The number is required.',
        'numeric' => 'The number must be a valid number.',
        'verify_number' => 'The number verification failed.', // Custom message for verify_number
    ],

    'password_otp' => [
        'required' => 'The password field is required.',
        'password' => 'The password is incorrect.',
        'invalid' => 'Invalid password.',
    ],

    //AuthController file
    'auth_controller' => [
        'name_required' => 'Name is required.',
        'name_max' => 'Name may not be greater than 255 characters.',

        'email_required' => 'Email is required.',
        'email_email' => 'Enter a valid email address.',
        'email_max' => 'Email may not be greater than 255 characters.',
        'email_unique' => 'This email is already registered.',

        'password_required' => 'Password is required.',
        'password_confirmed' => 'Password confirmation does not match.',
        'password_min' => 'Password must be at least 6 characters.',
    ],

    'resend_otp' => [
        'eid_required' => 'The EID field is required.',
        'eid_string' => 'The EID must be a string.',
        'type_required' => 'The type field is required.',
        'type_string' => 'The type must be a string.',
        'type_in' => 'The selected type is invalid.',
    ],

    'verify_otp' => [
        'eid_required' => 'The employee ID is required.',
        'eid_string' => 'The employee ID must be a string.',
        'otp_required' => 'The OTP is required.',
        'otp_size' => 'The OTP must be exactly 6 characters.',
        'recaptcha_required' => 'Please complete the CAPTCHA.',
        'recaptcha_size' => 'The CAPTCHA response is invalid.',
    ],

    'company_validation' => [
        'company_required' => 'The Company name is required.',
        'company_string' => 'The Company must be text.',
        'address_required' => 'The Address is required.',
        'address_string' => 'The Address must be text.',
    ],

    'token_validation' => [
        'token_required' => 'The token is required.',
        'password_required' => 'The password field is required.',
        'password_confirmed' => 'The password confirmation does not match.',
    ],

    'custom_email' => [
        'required' => 'The email field is required.',
        'email' => 'Please enter a valid email address.',
        'exists' => 'This email is not registered with us.',
    ],

    'newsletterEmail' => [
        'required' => 'The newsletter email is required.',
        'email' => 'Please enter a valid email address for the newsletter.',
    ],

    'widget' => [
        'name_required' => 'Name is required.',
        'name_max' => 'Name may not be greater than 50 characters.',
        'publish_required' => 'Publish status is required.',
        'type_required' => 'Type is required.',
        'type_unique' => 'This type already exists.',
    ],

    'payment' => [
        'payment_date_required' => 'Payment date is required.',
        'payment_method_required' => 'Payment method is required.',
        'amount_required' => 'Amount is required.',
    ],

    'custom_date' => [
        'date_required' => 'The date field is required.',
        'total_required' => 'The total field is required.',
        'status_required' => 'The status field is required.',

    ],

    'plan_renewal' => [
        'plan_required' => 'The plan field is required.',
        'payment_method_required' => 'The payment method field is required.',
        'cost_required' => 'The cost field is required.',
        'code_not_valid' => 'The promotion code is not valid.',
    ],

    'rate' => [
        'required' => 'Rate is required.',
        'numeric' => 'Rate must be a number.',
    ],

    'product_validate' => [
        'producttitle_required' => 'Product title is required.',
        'version_required' => 'Version is required.',
        'filename_required' => 'Please upload a file.',
        'dependencies_required' => 'Dependencies field is required.',
    ],
    'product_sku_unique' => 'Product SKU should be unique',
    'product_name_unique' => 'Name should be unique',
    'product_show_agent_required' => 'Select you Cart Page Preference',
    'product_controller' => [
        'name_required' => 'The product name is required.',
        'name_unique' => 'Name should be unique.',
        'type_required' => 'The product type is required.',
        'description_required' => 'The product description is required.',
        'product_description_required' => 'The detailed product description is required.',
        'image_mimes' => 'The image must be a file of type: jpeg, png, jpg.',
        'image_max' => 'The image may not be greater than 2048 kilobytes.',
        'product_sku_required' => 'The product SKU is required.',
        'group_required' => 'The product group is required.',
        'show_agent_required' => 'Select your Cart Page Preference.',
    ],
    'current_domain_required' => 'Current domain is required.',
    'new_domain_required' => 'New domain is required.',
    'special_characters_not_allowed' => 'Special characters are not allowed in domain name',
    'orderno_required' => 'Order number is required',
    'cloud_central_domain_required' => 'Cloud central domain is required.',
    'cloud_cname_required' => 'Cloud CNAME is required.',
    'cloud_tenant' => [
        'cloud_top_message_required' => 'Cloud top message is required.',
        'cloud_label_field_required' => 'Cloud label field is required.',
        'cloud_label_radio_required' => 'Cloud label radio is required.',
        'cloud_product_required' => 'Cloud product is required.',
        'cloud_free_plan_required' => 'Cloud free plan is required.',
        'cloud_product_key_required' => 'Cloud product key is required.',
    ],
    'reg_till_after' => 'The registration till date must be after the registration from date.',
    'extend_product' => [
        'title_required' => 'The title field is required.',
        'version_required' => 'The version field is required.',
        'dependencies_required' => 'The dependencies field is required.',
    ],
    'please_enter_recovery_code' => 'Please enter recovery code',
    'social_login' => [
        'client_id_required' => 'Client ID is required for Google, Github, or Linkedin.',
        'client_secret_required' => 'Client Secret is required for Google, Github, or Linkedin.',
        'api_key_required' => 'API Key is required for Twitter.',
        'api_secret_required' => 'API Secret is required for Twitter.',
        'redirect_url_required' => 'Redirect URL is required.',
    ],
    'thirdparty_api' => [
        'app_name_required' => 'App name is required.',
        'app_key_required' => 'App key is required.',
        'app_key_size' => 'App key must be exactly 32 characters.',
        'app_secret_required' => 'App secret is required.',
    ],
    'plan_request' => [
        'name_required' => 'The name field is required',
        'product_quant_req' => 'The product quantity field is required when no of agents is not present.',
        'no_agent_req' => 'The no of agents field is required when product quantity is not present.',
        'pro_req' => 'The product field is required',
        'offer_price' => 'Offer prices  must not be greater than 100',
    ],

];
