<?php

return [

    'accepted' => 'El :attribute debe ser aceptado.',
    'accepted_if' => 'El :attribute debe ser aceptado cuando :other sea :value.',
    'active_url' => 'El :attribute no es una URL válida.',
    'after' => 'El :attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => 'El :attribute debe ser una fecha posterior o igual a :date.',
    'alpha' => 'El :attribute solo debe contener letras.',
    'alpha_dash' => 'El :attribute solo debe contener letras, números, guiones y guiones bajos.',
    'alpha_num' => 'El :attribute solo debe contener letras y números.',
    'array' => 'El :attribute debe ser un array.',
    'before' => 'El :attribute debe ser una fecha anterior a :date.',
    'before_or_equal' => 'El :attribute debe ser una fecha anterior o igual a :date.',
    'between' => [
        'array' => 'El :attribute debe tener entre :min y :max elementos.',
        'file' => 'El :attribute debe tener entre :min y :max kilobytes.',
        'numeric' => 'El :attribute debe estar entre :min y :max.',
        'string' => 'El :attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación del :attribute no coincide.',
    'current_password' => 'La contraseña es incorrecta.',
    'date' => 'El :attribute no es una fecha válida.',
    'date_equals' => 'El :attribute debe ser una fecha igual a :date.',
    'date_format' => 'El :attribute no coincide con el formato :format.',
    'declined' => 'El :attribute debe ser rechazado.',
    'declined_if' => 'El :attribute debe ser rechazado cuando :other sea :value.',
    'different' => 'El :attribute y :other deben ser diferentes.',
    'digits' => 'El :attribute debe tener :digits dígitos.',
    'digits_between' => 'El :attribute debe tener entre :min y :max dígitos.',
    'dimensions' => 'El :attribute tiene dimensiones de imagen no válidas.',
    'distinct' => 'El campo :attribute tiene un valor duplicado.',
    'doesnt_start_with' => 'El :attribute no puede comenzar con uno de los siguientes: :values.',
    'email' => 'El :attribute debe ser una dirección de correo electrónico válida.',
    'ends_with' => 'El :attribute debe terminar con uno de los siguientes: :values.',
    'enum' => 'El :attribute seleccionado no es válido.',
    'exists' => 'El :attribute seleccionado no es válido.',
    'file' => 'El :attribute debe ser un archivo.',
    'filled' => 'El campo :attribute debe tener un valor.',
    'gt' => [
        'array' => 'El :attribute debe tener más de :value elementos.',
        'file' => 'El :attribute debe ser mayor que :value kilobytes.',
        'numeric' => 'El :attribute debe ser mayor que :value.',
        'string' => 'El :attribute debe tener más de :value caracteres.',
    ],
    'gte' => [
        'array' => 'El :attribute debe tener :value elementos o más.',
        'file' => 'El :attribute debe ser mayor o igual a :value kilobytes.',
        'numeric' => 'El :attribute debe ser mayor o igual a :value.',
        'string' => 'El :attribute debe tener al menos :value caracteres.',
    ],
    'image' => 'El :attribute debe ser una imagen.',
    'in' => 'El :attribute seleccionado no es válido.',
    'in_array' => 'El campo :attribute no existe en :other.',
    'integer' => 'El :attribute debe ser un número entero.',
    'ip' => 'El :attribute debe ser una dirección IP válida.',
    'ipv4' => 'El :attribute debe ser una dirección IPv4 válida.',
    'ipv6' => 'El :attribute debe ser una dirección IPv6 válida.',
    'json' => 'El :attribute debe ser una cadena JSON válida.',
    'lt' => [
        'array' => 'El :attribute debe tener menos de :value elementos.',
        'file' => 'El :attribute debe ser menor que :value kilobytes.',
        'numeric' => 'El :attribute debe ser menor que :value.',
        'string' => 'El :attribute debe tener menos de :value caracteres.',
    ],
    'lte' => [
        'array' => 'El :attribute no debe tener más de :value elementos.',
        'file' => 'El :attribute debe ser menor o igual a :value kilobytes.',
        'numeric' => 'El :attribute debe ser menor o igual a :value.',
        'string' => 'El :attribute debe tener menos o igual a :value caracteres.',
    ],
    'mac_address' => 'El :attribute debe ser una dirección MAC válida.',
    'max' => [
        'array' => 'El :attribute no debe tener más de :max elementos.',
        'file' => 'El :attribute no debe ser mayor que :max kilobytes.',
        'numeric' => 'El :attribute no debe ser mayor que :max.',
        'string' => 'El :attribute no debe ser mayor que :max caracteres.',
    ],
    'mimes' => 'El :attribute debe ser un archivo de tipo: :values.',
    'mimetypes' => 'El :attribute debe ser un archivo de tipo: :values.',
    'min' => [
        'array' => 'El :attribute debe tener al menos :min elementos.',
        'file' => 'El :attribute debe tener al menos :min kilobytes.',
        'numeric' => 'El :attribute debe ser al menos :min.',
        'string' => 'El :attribute debe tener al menos :min caracteres.',
    ],
    'multiple_of' => 'El :attribute debe ser un múltiplo de :value.',
    'not_in' => 'El :attribute seleccionado no es válido.',
    'not_regex' => 'El formato de :attribute no es válido.',
    'numeric' => 'El :attribute debe ser un número.',
    'password' => [
        'letters' => 'El :attribute debe contener al menos una letra.',
        'mixed' => 'El :attribute debe contener al menos una letra mayúscula y una minúscula.',
        'numbers' => 'El :attribute debe contener al menos un número.',
        'symbols' => 'El :attribute debe contener al menos un símbolo.',
        'uncompromised' => 'El :attribute proporcionado ha aparecido en una filtración de datos. Por favor, elige otro :attribute.',
    ],
    'present' => 'El campo :attribute debe estar presente.',
    'prohibited' => 'El campo :attribute está prohibido.',
    'prohibited_if' => 'El campo :attribute está prohibido cuando :other sea :value.',
    'prohibited_unless' => 'El campo :attribute está prohibido a menos que :other esté en :values.',
    'prohibits' => 'El campo :attribute prohíbe que :other esté presente.',
    'regex' => 'El formato de :attribute no es válido.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_array_keys' => 'El campo :attribute debe contener entradas para: :values.',
    'required_if' => 'El campo :attribute es obligatorio cuando :other sea :value.',
    'required_unless' => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
    'required_with' => 'El campo :attribute es obligatorio cuando :values esté presente.',
    'required_with_all' => 'El campo :attribute es obligatorio cuando :values estén presentes.',
    'required_without' => 'El campo :attribute es obligatorio cuando :values no esté presente.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de :values esté presente.',
    'same' => 'El :attribute y :other deben coincidir.',
    'size' => [
        'array' => 'El :attribute debe contener :size elementos.',
        'file' => 'El :attribute debe ser de :size kilobytes.',
        'numeric' => 'El :attribute debe ser :size.',
        'string' => 'El :attribute debe tener :size caracteres.',
    ],
    'starts_with' => 'El :attribute debe comenzar con uno de los siguientes: :values.',
    'string' => 'El :attribute debe ser una cadena de texto.',
    'timezone' => 'El :attribute debe ser una zona horaria válida.',
    'unique' => 'El :attribute ya ha sido tomado.',
    'uploaded' => 'El :attribute no se pudo cargar.',
    'url' => 'El :attribute debe ser una URL válida.',
    'uuid' => 'El :attribute debe ser un UUID válido.',
    'attributes' => [],

    'publish_date_required' => 'La fecha de publicación es obligatoria',
    'price_numeric_value' => 'El precio debe ser un valor numérico',
    'quantity_integer_value' => 'La cantidad debe ser un valor entero',
    'order_has_Expired' => 'El pedido ha expirado',
    'expired' => 'Expirado',
    'eid_required' => 'El campo EID es obligatorio.',
    'eid_string' => 'El EID debe ser una cadena de caracteres.',
    'otp_required' => 'El campo OTP es obligatorio.',
    'amt_required' => 'El campo de monto es obligatorio',
    'amt_numeric' => 'El monto debe ser un número',
    'payment_date_required' => 'La fecha de pago es obligatoria.',
    'payment_method_required' => 'El método de pago es obligatorio.',
    'total_amount_required' => 'El monto total es obligatorio.',
    'total_amount_numeric' => 'El monto total debe ser un valor numérico.',
    'invoice_link_required' => 'Por favor, vincule el monto con al menos una factura.',

    /*
Request file custom validation messages
*/

    // Common
    'settings_form' => [
        'company' => [
            'required' => 'El campo de la empresa es obligatorio.',
        ],
        'website' => [
            'url' => 'El sitio web debe ser una URL válida.',
        ],
        'phone' => [
            'regex' => 'El formato del número de teléfono no es válido.',
        ],
        'address' => [
            'required' => 'El campo de dirección es obligatorio.',
            'max' => 'La dirección no debe superar los 300 caracteres.',
        ],
        'logo' => [
            'mimes' => 'El logo debe ser un archivo PNG.',
        ],
        'driver' => [
            'required' => 'El campo del controlador es obligatorio.',
        ],
        'port' => [
            'integer' => 'El puerto debe ser un número entero.',
        ],
        'email' => [
            'required' => 'El campo de correo electrónico es obligatorio.',
            'email' => 'El correo electrónico debe ser una dirección válida.',
        ],
        'password' => [
            'required' => 'El campo de la contraseña es obligatorio.',
        ],
        'error_email' => [
            'email' => 'El correo electrónico de error debe ser una dirección válida.',
        ],
    ],

    'settings_forms' => [
        'company' => [
            'required' => 'El nombre de la empresa es obligatorio.',
            'max' => 'El nombre de la empresa no debe superar los 50 caracteres.',
        ],
        'company_email' => [
            'required' => 'El correo electrónico de la empresa es obligatorio.',
            'email' => 'El correo electrónico de la empresa debe ser una dirección válida.',
        ],
        'title' => [
            'max' => 'El título no debe superar los 50 caracteres.',
        ],
        'website' => [
            'required' => 'La URL del sitio web es obligatoria.',
            'url' => 'El sitio web debe ser una URL válida.',
            'regex' => 'El formato del sitio web no es válido.',
        ],
        'phone' => [
            'required' => 'El número de teléfono es obligatorio.',
        ],
        'address' => [
            'required' => 'La dirección es obligatoria.',
        ],
        'state' => [
            'required' => 'El estado es obligatorio.',
        ],
        'country' => [
            'required' => 'El país es obligatorio.',
        ],
        'gstin' => [
            'max' => 'El GSTIN no debe superar los 15 caracteres.',
        ],
        'default_currency' => [
            'required' => 'La moneda predeterminada es obligatoria.',
        ],
        'admin_logo' => [
            'mimes' => 'El logo del administrador debe ser un archivo de tipo: jpeg, png, jpg.',
            'max' => 'El logo del administrador no debe superar los 2MB.',
        ],
        'fav_icon' => [
            'mimes' => 'El ícono debe ser un archivo de tipo: jpeg, png, jpg.',
            'max' => 'El ícono no debe superar los 2MB.',
        ],
        'logo' => [
            'mimes' => 'El logo debe ser un archivo de tipo: jpeg, png, jpg.',
            'max' => 'El logo no debe superar los 2MB.',
        ],
    ],

    'social_media_form' => [
        'name' => [
            'required' => 'El campo de nombre es obligatorio.',
            'unique' => 'Este nombre ya existe.',
            'max' => 'El nombre no debe superar los 50 caracteres.',
        ],
        'link' => [
            'required' => 'El campo de enlace es obligatorio.',
            'url' => 'El enlace debe ser una URL válida.',
            'regex' => 'El formato del enlace no es válido.',
        ],
    ],
    // Email
    'custom' => [
        'password' => [
            'required_if' => 'El campo de contraseña es obligatorio para el controlador de correo seleccionado.',
        ],
        'port' => [
            'required_if' => 'El campo de puerto es obligatorio para SMTP.',
        ],
        'encryption' => [
            'required_if' => 'El campo de encriptación es obligatorio para SMTP.',
        ],
        'host' => [
            'required_if' => 'El campo de host es obligatorio para SMTP.',
        ],
        'secret' => [
            'required_if' => 'El campo de secreto es obligatorio para el controlador de correo seleccionado.',
        ],
        'domain' => [
            'required_if' => 'El campo de dominio es obligatorio para Mailgun.',
        ],
        'key' => [
            'required_if' => 'El campo de clave es obligatorio para SES.',
        ],
        'region' => [
            'required_if' => 'El campo de región es obligatorio para SES.',
        ],
        'email' => [
            'required_if' => 'El campo de correo electrónico es obligatorio para el controlador de correo seleccionado.',
            'required' => 'El campo de correo electrónico es obligatorio.',
            'email' => 'Por favor, ingresa una dirección de correo electrónico válida.',
            'not_matching' => 'El dominio del correo electrónico debe coincidir con el dominio actual del sitio.',
        ],
        'driver' => [
            'required' => 'El campo del controlador es obligatorio.',
        ],
    ],

    'customer_form' => [
        'first_name' => [
            'required' => 'El campo de nombre es obligatorio.',
        ],
        'last_name' => [
            'required' => 'El campo de apellido es obligatorio.',
        ],
        'company' => [
            'required' => 'El campo de empresa es obligatorio.',
        ],
        'mobile' => [
            'regex' => 'El formato del número móvil no es válido.',
        ],
        'address' => [
            'required' => 'El campo de dirección es obligatorio.',
        ],
        'zip' => [
            'required' => 'El campo de código postal es obligatorio.',
            'min' => 'El código postal debe tener al menos 5 dígitos.',
            'numeric' => 'El código postal debe ser numérico.',
        ],
        'email' => [
            'required' => 'El campo de correo electrónico es obligatorio.',
            'email' => 'El correo electrónico debe ser una dirección válida.',
            'unique' => 'Este correo electrónico ya está en uso.',
        ],
    ],

    'contact_request' => [
        'conName' => 'El campo de nombre es obligatorio.',
        'email' => 'El campo de correo electrónico es obligatorio.',
        'conmessage' => 'El campo de mensaje es obligatorio.',
        'Mobile' => 'El campo de teléfono móvil es obligatorio.',
        'country_code' => 'El campo de teléfono móvil es obligatorio.',
        'demoname' => 'El campo de nombre es obligatorio.',
        'demomessage' => 'El campo de mensaje es obligatorio.',
        'demoemail' => 'El campo de correo electrónico es obligatorio.',
        'congg-recaptcha-response-1.required' => 'Verificación de robot fallida. Por favor, inténtalo de nuevo.',
        'demo-recaptcha-response-1.required' => 'Verificación de robot fallida. Por favor, inténtalo de nuevo.',
    ],

    'frontend_pages' => [
        'name' => [
            'required' => 'El campo de nombre es obligatorio.',
            'unique' => 'Este nombre ya existe.',
            'max' => 'El nombre no debe exceder los 20 caracteres.',
            'regex' => 'El nombre solo puede contener letras y espacios.',
        ],
        'publish' => [
            'required' => 'El campo de publicación es obligatorio.',
        ],
        'slug' => [
            'required' => 'El campo de slug es obligatorio.',
        ],
        'url' => [
            'required' => 'El campo de URL es obligatorio.',
            'url' => 'La URL debe ser un enlace válido.',
            'regex' => 'El formato de la URL no es válido.',
        ],
        'content' => [
            'required' => 'El campo de contenido es obligatorio.',
        ],
        'created_at' => [
            'required' => 'El campo de creación es obligatorio.',
        ],
    ],

    // Order form
    'order_form' => [
        'client' => [
            'required' => 'El campo de cliente es obligatorio.',
        ],
        'payment_method' => [
            'required' => 'El campo de método de pago es obligatorio.',
        ],
        'promotion_code' => [
            'required' => 'El campo de código promocional es obligatorio.',
        ],
        'order_status' => [
            'required' => 'El campo de estado del pedido es obligatorio.',
        ],
        'product' => [
            'required' => 'El campo de producto es obligatorio.',
        ],
        'subscription' => [
            'required' => 'El campo de suscripción es obligatorio.',
        ],
        'price_override' => [
            'numeric' => 'El precio anulado debe ser un número.',
        ],
        'qty' => [
            'integer' => 'La cantidad debe ser un número entero.',
        ],
    ],
    // Payment form
    'coupon_form' => [
        'code' => [
            'required' => 'El campo del código de cupón es obligatorio.',
            'string' => 'El código de cupón debe ser una cadena de texto.',
            'max' => 'El código de cupón no debe exceder los 255 caracteres.',
        ],
        'type' => [
            'required' => 'El campo de tipo es obligatorio.',
            'in' => 'Tipo inválido. Los valores permitidos son: porcentaje, otro_tipo.',
        ],
        'applied' => [
            'required' => 'El campo aplicado para un producto es obligatorio.',
            'date' => 'El campo aplicado para un producto debe ser una fecha válida.',
        ],
        'uses' => [
            'required' => 'El campo de usos es obligatorio.',
            'numeric' => 'El campo de usos debe ser un número.',
            'min' => 'El campo de usos debe ser al menos :min.',
        ],
        'start' => [
            'required' => 'El campo de inicio es obligatorio.',
            'date' => 'El campo de inicio debe ser una fecha válida.',
        ],
        'expiry' => [
            'required' => 'El campo de vencimiento es obligatorio.',
            'date' => 'El campo de vencimiento debe ser una fecha válida.',
            'after' => 'La fecha de vencimiento debe ser posterior a la fecha de inicio.',
        ],
        'value' => [
            'required' => 'El campo de valor de descuento es obligatorio.',
            'numeric' => 'El campo de valor de descuento debe ser un número.',
            'between' => 'El campo de valor de descuento debe estar entre :min y :max si el tipo es porcentaje.',
        ],
    ],

    'tax_form' => [
        'name' => [
            'required' => 'El campo de nombre es obligatorio.',
        ],
        'rate' => [
            'required' => 'El campo de tasa es obligatorio.',
            'numeric' => 'La tasa debe ser un número.',
        ],
        'level' => [
            'required' => 'El campo de nivel es obligatorio.',
            'integer' => 'El nivel debe ser un número entero.',
        ],
        'country' => [
            'required' => 'El campo de país es obligatorio.',
            // 'exists' => 'El país seleccionado no es válido.',
        ],
        'state' => [
            'required' => 'El campo de estado es obligatorio.',
            // 'exists' => 'El estado seleccionado no es válido.',
        ],
    ],

    // Product
    'subscription_form' => [
        'name' => [
            'required' => 'El campo de nombre es obligatorio.',
        ],
        'subscription' => [
            'required' => 'El campo de suscripción es obligatorio.',
        ],
        'regular_price' => [
            'required' => 'El campo de precio regular es obligatorio.',
            'numeric' => 'El precio regular debe ser un número.',
        ],
        'selling_price' => [
            'required' => 'El campo de precio de venta es obligatorio.',
            'numeric' => 'El precio de venta debe ser un número.',
        ],
        'products' => [
            'required' => 'El campo de productos es obligatorio.',
        ],
    ],

    'bundle' => [
        'name' => [
            'required' => 'El campo de nombre es obligatorio.',
        ],
        'items' => [
            'required' => 'Cada elemento es obligatorio.',
        ],
    ],

    'group' => [
        'name' => [
            'required' => 'El nombre es obligatorio',
        ],
        'features' => [
            'name' => [
                'required' => 'Todos los campos de características son obligatorios',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => 'El precio es obligatorio',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => 'El valor es obligatorio',
            ],
        ],
        'type' => [
            'required_with' => 'El tipo es obligatorio',
        ],
        'title' => [
            'required_with' => 'El título es obligatorio',
        ],
    ],

    'product' => [
        'name' => [
            'required' => 'El campo de nombre es obligatorio.',
        ],
        'type' => [
            'required' => 'El campo de tipo es obligatorio.',
        ],
        'group' => [
            'required' => 'El campo de grupo es obligatorio.',
        ],
        'subscription' => [
            'required' => 'El campo de suscripción es obligatorio.',
        ],
        'currency' => [
            'required' => 'El campo de moneda es obligatorio.',
        ],
    ],
    // 'price' => [
    //     'required' => 'The price field is required.',
    // ],
    'file' => [
        'required_without_all' => 'El campo de archivo es obligatorio si no se proporcionan github_owner ni github_repository.',
        'mimes' => 'El archivo debe ser un archivo zip.',
    ],
    'image' => [
        'required_without_all' => 'El campo de imagen es obligatorio si no se proporcionan github_owner ni github_repository.',
        'mimes' => 'La imagen debe ser un archivo PNG.',
    ],
    'github_owner' => [
        'required_without_all' => 'El campo de propietario de GitHub es obligatorio si no se proporcionan archivo ni imagen.',
    ],
    'github_repository' => [
        'required_without_all' => 'El campo del repositorio de GitHub es obligatorio si no se proporcionan archivo ni imagen.',
        'required_if' => 'El campo del repositorio de GitHub es obligatorio si el tipo es 2.',
    ],

    // User
    'users' => [
        'first_name' => [
            'required' => 'El campo de nombre es obligatorio.',
        ],
        'last_name' => [
            'required' => 'El campo de apellido es obligatorio.',
        ],
        'company' => [
            'required' => 'El campo de empresa es obligatorio.',
        ],
        'email' => [
            'required' => 'El campo de correo electrónico es obligatorio.',
            'email' => 'El correo electrónico debe ser una dirección válida.',
            'unique' => 'El correo electrónico ya está en uso.',
        ],
        'address' => [
            'required' => 'El campo de dirección es obligatorio.',
        ],
        'mobile' => [
            'required' => 'El campo de teléfono móvil es obligatorio.',
        ],
        'country' => [
            'required' => 'El campo de país es obligatorio.',
            'exists' => 'El país seleccionado no es válido.',
        ],
        'state' => [
            'required_if' => 'El campo de estado es obligatorio cuando el país es India.',
        ],
        'timezone_id' => [
            'required' => 'El campo de zona horaria es obligatorio.',
        ],
        'user_name' => [
            'required' => 'El campo de nombre de usuario es obligatorio.',
            'unique' => 'El nombre de usuario ya está en uso.',
        ],
        'zip' => [
            'regex' => 'El campo de estado es obligatorio cuando el país es India.',
        ],
    ],

    'profile_form' => [
        'first_name' => [
            'required' => 'El nombre es obligatorio.',
            'min' => 'El nombre debe tener al menos :min caracteres.',
            'max' => 'El nombre no puede tener más de :max caracteres.',
        ],
        'last_name' => [
            'required' => 'El apellido es obligatorio.',
            'max' => 'El apellido no puede tener más de :max caracteres.',
        ],
        'company' => [
            'required' => 'El nombre de la empresa es obligatorio.',
            'max' => 'El nombre de la empresa no puede tener más de :max caracteres.',
        ],
        'email' => [
            'required' => 'El correo electrónico es obligatorio.',
            'email' => 'Introduce una dirección de correo electrónico válida.',
            'unique' => 'La dirección de correo ya está en uso. Por favor, elige otra.',
        ],
        'mobile' => [
            'required' => 'El número móvil es obligatorio.',
            'regex' => 'Introduce un número móvil válido.',
            'min' => 'El número móvil debe tener al menos :min caracteres.',
            'max' => 'El número móvil no puede tener más de :max caracteres.',
        ],
        'address' => [
            'required' => 'La dirección es obligatoria.',
        ],
        'user_name' => [
            'required' => 'El nombre de usuario es obligatorio.',
            'unique' => 'Este nombre de usuario ya está en uso.',
        ],
        'timezone_id' => [
            'required' => 'La zona horaria es obligatoria.',
        ],
        'country' => [
            'required' => 'El país es obligatorio.',
            'exists' => 'El país seleccionado no es válido.',
        ],
        'state' => [
            'required_if' => 'El campo de estado es obligatorio cuando el país es India.',
        ],
        'old_password' => [
            'required' => 'La contraseña anterior es obligatoria.',
            'min' => 'La contraseña anterior debe tener al menos :min caracteres.',
        ],
        'new_password' => [
            'required' => 'La nueva contraseña es obligatoria.',
            'different' => 'La nueva contraseña debe ser diferente de la anterior.',
        ],
        'confirm_password' => [
            'required' => 'La confirmación de la contraseña es obligatoria.',
            'same' => 'La confirmación debe coincidir con la nueva contraseña.',
        ],
        'terms' => [
            'required' => 'Debes aceptar los términos.',
        ],
        'password' => [
            'required' => 'La contraseña es obligatoria.',
        ],
        'password_confirmation' => [
            'required' => 'La confirmación de la contraseña es obligatoria.',
            'same' => 'Las contraseñas no coinciden.',
        ],
        'mobile_code' => [
            'required' => 'Introduce el código de país (móvil).',
        ],
    ],

    // Invoice form
    'invoice' => [
        'user' => [
            'required' => 'El campo de clientes es obligatorio.',
        ],
        'date' => [
            'required' => 'El campo de fecha es obligatorio.',
            'date' => 'La fecha debe ser una fecha válida.',
        ],
        'domain' => [
            'regex' => 'El formato del dominio no es válido.',
        ],
        'plan' => [
            'required_if' => 'El campo de suscripción es obligatorio.',
        ],
        'price' => [
            'required' => 'El campo de precio es obligatorio.',
        ],
        'product' => [
            'required' => 'El campo de producto es obligatorio.',
        ],
    ],

    // LocalizedLicense form
    'domain_form' => [
        'domain' => [
            'required' => 'El campo de dominio es obligatorio.',
            'url' => 'El dominio debe ser una URL válida.',
        ],
    ],
    //Product Renewal form
    'product_renewal' => [
        'domain' => [
            'required' => 'El campo de dominio es obligatorio.',
            'no_http' => 'El dominio no debe contener "http" o "https".',
        ],
    ],

    //Language form
    'language' => [
        'required' => 'El campo de idioma es obligatorio.',
        'invalid' => 'El idioma seleccionado no es válido.',
    ],

    //UpdateSroragePathRequest form
    'storage_path' => [
        'disk' => [
            'required' => 'El campo de disco de almacenamiento es obligatorio.',
            'string' => 'El disco debe ser una cadena de texto.',
        ],
        'path' => [
            'string' => 'La ruta debe ser una cadena de texto.',
            'nullable' => 'El campo de ruta es opcional.',
        ],
    ],

    //ValidateSecretRequest form
    'validate_secret' => [
        'totp' => [
            'required' => 'Por favor, introduce el código',
            'digits' => 'Por favor, introduce un código válido de 6 dígitos',
        ],
    ],

    //VerifyOtp form
    'verify_email' => [
        'required' => 'El campo de correo electrónico es obligatorio.',
        'email' => 'El correo electrónico debe ser una dirección válida.',
        'verify_email' => 'La verificación del correo electrónico ha fallado.',
    ],

    'verify_country_code' => [
        'required' => 'El código de país es obligatorio.',
        'numeric' => 'El código de país debe ser un número válido.',
        'verify_country_code' => 'La verificación del código de país ha fallado.',
    ],

    'verify_number' => [
        'required' => 'El número es obligatorio.',
        'numeric' => 'El número debe ser un número válido.',
        'verify_number' => 'La verificación del número ha fallado.',
    ],

    'password_otp' => [
        'required' => 'El campo de contraseña es obligatorio.',
        'password' => 'La contraseña es incorrecta.',
        'invalid' => 'Contraseña inválida.',
    ],

    //AuthController file
    'auth_controller' => [
        'name_required' => 'El nombre es obligatorio.',
        'name_max' => 'El nombre no puede tener más de 255 caracteres.',

        'email_required' => 'El correo electrónico es obligatorio.',
        'email_email' => 'Introduce una dirección de correo electrónico válida.',
        'email_max' => 'El correo electrónico no puede tener más de 255 caracteres.',
        'email_unique' => 'Este correo ya está registrado.',

        'password_required' => 'La contraseña es obligatoria.',
        'password_confirmed' => 'La confirmación de la contraseña no coincide.',
        'password_min' => 'La contraseña debe tener al menos 6 caracteres.',
    ],

    'resend_otp' => [
        'eid_required' => 'El campo EID es obligatorio.',
        'eid_string' => 'El EID debe ser una cadena de texto.',
        'type_required' => 'El campo de tipo es obligatorio.',
        'type_string' => 'El tipo debe ser una cadena de texto.',
        'type_in' => 'El tipo seleccionado no es válido.',
    ],

    'verify_otp' => [
        'eid_required' => 'El ID del empleado es obligatorio.',
        'eid_string' => 'El ID del empleado debe ser una cadena de texto.',
        'otp_required' => 'El OTP es obligatorio.',
        'otp_size' => 'El OTP debe tener exactamente 6 caracteres.',
        'recaptcha_required' => 'Por favor, completa el CAPTCHA.',
        'recaptcha_size' => 'La respuesta del CAPTCHA no es válida.',
    ],

    'company_validation' => [
        'company_required' => 'El nombre de la empresa es obligatorio.',
        'company_string' => 'La empresa debe ser texto.',
        'address_required' => 'La dirección es obligatoria.',
        'address_string' => 'La dirección debe ser texto.',
    ],

    'token_validation' => [
        'token_required' => 'El token es obligatorio.',
        'password_required' => 'El campo de contraseña es obligatorio.',
        'password_confirmed' => 'La confirmación de la contraseña no coincide.',
    ],

    'custom_email' => [
        'required' => 'El campo de correo electrónico es obligatorio.',
        'email' => 'Por favor, introduce una dirección de correo electrónico válida.',
        'exists' => 'Este correo no está registrado con nosotros.',
    ],

    'newsletterEmail' => [
        'required' => 'El correo del boletín es obligatorio.',
        'email' => 'Por favor, introduce un correo válido para el boletín.',
    ],

    'widget' => [
        'name_required' => 'El nombre es obligatorio.',
        'name_max' => 'El nombre no puede tener más de 50 caracteres.',
        'publish_required' => 'El estado de publicación es obligatorio.',
        'type_required' => 'El tipo es obligatorio.',
        'type_unique' => 'Este tipo ya existe.',
    ],

    'payment' => [
        'payment_date_required' => 'La fecha de pago es obligatoria.',
        'payment_method_required' => 'El método de pago es obligatorio.',
        'amount_required' => 'El monto es obligatorio.',
    ],

    'custom_date' => [
        'date_required' => 'El campo de fecha es obligatorio.',
        'total_required' => 'El campo total es obligatorio.',
        'status_required' => 'El estado es obligatorio.',
    ],

    'plan_renewal' => [
        'plan_required' => 'El campo de plan es obligatorio.',
        'payment_method_required' => 'El campo de método de pago es obligatorio.',
        'cost_required' => 'El campo de coste es obligatorio.',
        'code_not_valid' => 'El código de promoción no es válido.',
    ],

    'rate' => [
        'required' => 'La tarifa es obligatoria.',
        'numeric' => 'La tarifa debe ser un número.',
    ],
    'product_validate' => [
        'producttitle_required' => 'Se requiere el título del producto.',
        'version_required' => 'Se requiere la versión.',
        'filename_required' => 'Por favor, sube un archivo.',
        'dependencies_required' => 'Se requiere el campo de dependencias.',
    ],
    'product_sku_unique' => 'El SKU del producto debe ser único.',
    'product_name_unique' => 'El nombre debe ser único.',
    'product_show_agent_required' => 'Selecciona tu preferencia de página del carrito.',
    'product_controller' => [
        'name_required' => 'Se requiere el nombre del producto.',
        'name_unique' => 'El nombre debe ser único.',
        'type_required' => 'Se requiere el tipo de producto.',
        'description_required' => 'Se requiere la descripción del producto.',
        'product_description_required' => 'Se requiere una descripción detallada del producto.',
        'image_mimes' => 'La imagen debe ser un archivo de tipo: jpeg, png, jpg.',
        'image_max' => 'La imagen no debe ser mayor a 2048 kilobytes.',
        'product_sku_required' => 'Se requiere el SKU del producto.',
        'group_required' => 'Se requiere el grupo del producto.',
        'show_agent_required' => 'Selecciona tu preferencia de página del carrito.',
    ],
    'current_domain_required' => 'Se requiere el dominio actual.',
    'new_domain_required' => 'Se requiere el nuevo dominio.',
    'special_characters_not_allowed' => 'No se permiten caracteres especiales en el nombre del dominio.',
    'orderno_required' => 'Se requiere el número de orden.',
    'cloud_central_domain_required' => 'Se requiere el dominio central de la nube.',
    'cloud_cname_required' => 'Se requiere el CNAME de la nube.',
    'cloud_tenant' => [
        'cloud_top_message_required' => 'Se requiere el mensaje superior de la nube.',
        'cloud_label_field_required' => 'Se requiere el campo de etiqueta de la nube.',
        'cloud_label_radio_required' => 'Se requiere la opción de radio de la nube.',
        'cloud_product_required' => 'Se requiere el producto de la nube.',
        'cloud_free_plan_required' => 'Se requiere el plan gratuito de la nube.',
        'cloud_product_key_required' => 'Se requiere la clave del producto en la nube.',
    ],
    'reg_till_after' => 'La fecha de registro hasta debe ser posterior a la fecha de registro desde.',
    'extend_product' => [
        'title_required' => 'Se requiere el campo de título.',
        'version_required' => 'Se requiere el campo de versión.',
        'dependencies_required' => 'Se requiere el campo de dependencias.',
    ],
    'please_enter_recovery_code' => 'Por favor, introduce el código de recuperación.',
    'social_login' => [
        'client_id_required' => 'Se requiere el ID de cliente para Google, Github o Linkedin.',
        'client_secret_required' => 'Se requiere el secreto de cliente para Google, Github o Linkedin.',
        'api_key_required' => 'Se requiere la clave API para Twitter.',
        'api_secret_required' => 'Se requiere el secreto de API para Twitter.',
        'redirect_url_required' => 'Se requiere la URL de redirección.',
    ],
    'thirdparty_api' => [
        'app_name_required' => 'Se requiere el nombre de la aplicación.',
        'app_key_required' => 'Se requiere la clave de la aplicación.',
        'app_key_size' => 'La clave de la aplicación debe tener exactamente 32 caracteres.',
        'app_secret_required' => 'Se requiere el secreto de la aplicación.',
    ],
    'plan_request' => [
        'name_required' => 'El campo nombre es obligatorio',
        'product_quant_req' => 'El campo cantidad de producto es obligatorio cuando el número de agentes no está presente.',
        'no_agent_req' => 'El campo número de agentes es obligatorio cuando la cantidad de producto no está presente.',
        'pro_req' => 'El campo producto es obligatorio',
        'offer_price' => 'Los precios de oferta no deben ser mayores a 100',
    ],
    'razorpay_val' => [
        'business_required' => 'El campo de negocio es obligatorio.',
        'cmd_required' => 'El campo de comando es obligatorio.',
        'paypal_url_required' => 'La URL de PayPal es obligatoria.',
        'paypal_url_invalid' => 'La URL de PayPal debe ser una URL válida.',
        'success_url_invalid' => 'La URL de éxito debe ser una URL válida.',
        'cancel_url_invalid' => 'La URL de cancelación debe ser una URL válida.',
        'notify_url_invalid' => 'La URL de notificación debe ser una URL válida.',
        'currencies_required' => 'El campo de monedas es obligatorio.',
    ],
    'login_failed' => 'Error de inicio de sesión, por favor verifica el correo electrónico/nombre de usuario y la contraseña que ingresaste.',
    'forgot_email_validation' => 'Si el correo electrónico que proporcionaste está registrado, recibirás un correo con las instrucciones para restablecer la contraseña en breve.',
    'too_many_login_attempts' => 'Has sido bloqueado de la aplicación debido a demasiados intentos fallidos de inicio de sesión. Por favor, inténtalo nuevamente después de :time.',

];
