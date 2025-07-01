<?php

return [

    'accepted' => 'Das :attribute muss akzeptiert werden.',
    'accepted_if' => 'Das :attribute muss akzeptiert werden, wenn :other :value ist.',
    'active_url' => 'Das :attribute ist keine gültige URL.',
    'after' => 'Das :attribute muss ein Datum nach dem :date sein.',
    'after_or_equal' => 'Das :attribute muss ein Datum nach oder gleich :date sein.',
    'alpha' => 'Das :attribute darf nur Buchstaben enthalten.',
    'alpha_dash' => 'Das :attribute darf nur Buchstaben, Zahlen, Bindestriche und Unterstriche enthalten.',
    'alpha_num' => 'Das :attribute darf nur Buchstaben und Zahlen enthalten.',
    'array' => 'Das :attribute muss ein Array sein.',
    'before' => 'Das :attribute muss ein Datum vor :date sein.',
    'before_or_equal' => 'Das :attribute muss ein Datum vor oder gleich :date sein.',
    'between' => [
        'array' => 'Das :attribute muss zwischen :min und :max Elemente haben.',
        'file' => 'Das :attribute muss zwischen :min und :max Kilobyte groß sein.',
        'numeric' => 'Das :attribute muss zwischen :min und :max liegen.',
        'string' => 'Das :attribute muss zwischen :min und :max Zeichen lang sein.',
    ],
    'boolean' => 'Das :attribute-Feld muss wahr oder falsch sein.',
    'confirmed' => 'Die :attribute-Bestätigung stimmt nicht überein.',
    'current_password' => 'Das Passwort ist falsch.',
    'date' => 'Das :attribute ist kein gültiges Datum.',
    'date_equals' => 'Das :attribute muss ein Datum gleich :date sein.',
    'date_format' => 'Das :attribute entspricht nicht dem Format :format.',
    'declined' => 'Das :attribute muss abgelehnt werden.',
    'declined_if' => 'Das :attribute muss abgelehnt werden, wenn :other :value ist.',
    'different' => 'Das :attribute und :other müssen unterschiedlich sein.',
    'digits' => 'Das :attribute muss :digits Ziffern haben.',
    'digits_between' => 'Das :attribute muss zwischen :min und :max Ziffern haben.',
    'dimensions' => 'Das :attribute hat ungültige Bildabmessungen.',
    'distinct' => 'Das :attribute-Feld hat einen doppelten Wert.',
    'doesnt_start_with' => 'Das :attribute darf nicht mit einem der folgenden Werte beginnen: :values.',
    'email' => 'Das :attribute muss eine gültige E-Mail-Adresse sein.',
    'ends_with' => 'Das :attribute muss mit einem der folgenden Werte enden: :values.',
    'enum' => 'Das gewählte :attribute ist ungültig.',
    'exists' => 'Das gewählte :attribute ist ungültig.',
    'file' => 'Das :attribute muss eine Datei sein.',
    'filled' => 'Das :attribute-Feld muss einen Wert enthalten.',
    'gt' => [
        'array' => 'Das :attribute muss mehr als :value Elemente enthalten.',
        'file' => 'Das :attribute muss größer als :value Kilobyte sein.',
        'numeric' => 'Das :attribute muss größer als :value sein.',
        'string' => 'Das :attribute muss länger als :value Zeichen sein.',
    ],
    'gte' => [
        'array' => 'Das :attribute muss mindestens :value Elemente haben.',
        'file' => 'Das :attribute muss größer oder gleich :value Kilobyte sein.',
        'numeric' => 'Das :attribute muss größer oder gleich :value sein.',
        'string' => 'Das :attribute muss mindestens :value Zeichen lang sein.',
    ],
    'image' => 'Das :attribute muss ein Bild sein.',
    'in' => 'Das gewählte :attribute ist ungültig.',
    'in_array' => 'Das :attribute-Feld existiert nicht in :other.',
    'integer' => 'Das :attribute muss eine ganze Zahl sein.',
    'ip' => 'Das :attribute muss eine gültige IP-Adresse sein.',
    'ipv4' => 'Das :attribute muss eine gültige IPv4-Adresse sein.',
    'ipv6' => 'Das :attribute muss eine gültige IPv6-Adresse sein.',
    'json' => 'Das :attribute muss ein gültiger JSON-String sein.',
    'lt' => [
        'array' => 'Das :attribute muss weniger als :value Elemente enthalten.',
        'file' => 'Das :attribute muss kleiner als :value Kilobyte sein.',
        'numeric' => 'Das :attribute muss kleiner als :value sein.',
        'string' => 'Das :attribute muss kürzer als :value Zeichen sein.',
    ],
    'lte' => [
        'array' => 'Das :attribute darf nicht mehr als :value Elemente enthalten.',
        'file' => 'Das :attribute muss kleiner oder gleich :value Kilobyte sein.',
        'numeric' => 'Das :attribute muss kleiner oder gleich :value sein.',
        'string' => 'Das :attribute muss höchstens :value Zeichen lang sein.',
    ],
    'unique' => 'Das :attribute wurde bereits vergeben.',
    'uploaded' => 'Das :attribute konnte nicht hochgeladen werden.',
    'url' => 'Das :attribute muss eine gültige URL sein.',
    'uuid' => 'Das :attribute muss eine gültige UUID sein.',
    'custom_dup' => [
        'attribute-name' => [
            'rule-name' => 'benutzerdefinierte Nachricht',
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
    'publish_date_required' => 'Veröffentlichungsdatum ist erforderlich',
    'price_numeric_value' => 'Der Preis muss ein numerischer Wert sein',
    'quantity_integer_value' => 'Die Menge muss eine ganze Zahl sein',
    'order_has_Expired' => 'Die Bestellung ist abgelaufen',
    'expired' => 'Abgelaufen',
    'eid_required' => 'Das EID-Feld ist erforderlich.',
    'eid_string' => 'Die EID muss eine Zeichenkette sein.',
    'otp_required' => 'Das OTP-Feld ist erforderlich.',
    'amt_required' => 'Das Betragsfeld ist erforderlich',
    'amt_numeric' => 'Der Betrag muss eine Zahl sein',
    'payment_date_required' => 'Zahlungsdatum ist erforderlich.',
    'payment_method_required' => 'Zahlungsmethode ist erforderlich.',
    'total_amount_required' => 'Gesamtbetrag ist erforderlich.',
    'total_amount_numeric' => 'Der Gesamtbetrag muss ein numerischer Wert sein.',
    'invoice_link_required' => 'Bitte verknüpfen Sie den Betrag mit mindestens einer Rechnung.',

    /*
    Request file custom validation messages
    */

    // Common
    'settings_form' => [
        'company' => [
            'required' => 'Das Firmenfeld ist erforderlich.',
        ],
        'website' => [
            'url' => 'Die Website muss eine gültige URL sein.',
        ],
        'phone' => [
            'regex' => 'Das Telefonnummernformat ist ungültig.',
        ],
        'address' => [
            'required' => 'Das Adressfeld ist erforderlich.',
            'max' => 'Die Adresse darf nicht länger als 300 Zeichen sein.',
        ],
        'logo' => [
            'mimes' => 'Das Logo muss eine PNG-Datei sein.',
        ],
        'driver' => [
            'required' => 'Das Treiberfeld ist erforderlich.',
        ],
        'port' => [
            'integer' => 'Der Port muss eine ganze Zahl sein.',
        ],
        'email' => [
            'required' => 'Das E-Mail-Feld ist erforderlich.',
            'email' => 'Die E-Mail-Adresse muss gültig sein.',
        ],
        'password' => [
            'required' => 'Das Passwortfeld ist erforderlich.',
        ],
        'error_email' => [
            'email' => 'Die Fehler-E-Mail muss eine gültige E-Mail-Adresse sein.',
        ],
    ],

    'settings_forms' => [
        'company' => [
            'required' => 'Der Firmenname ist erforderlich.',
            'max' => 'Der Firmenname darf 50 Zeichen nicht überschreiten.',
        ],
        'company_email' => [
            'required' => 'Die Firmen-E-Mail ist erforderlich.',
            'email' => 'Die Firmen-E-Mail muss eine gültige E-Mail-Adresse sein.',
        ],
        'title' => [
            'max' => 'Der Titel darf 50 Zeichen nicht überschreiten.',
        ],
        'website' => [
            'required' => 'Die Website-URL ist erforderlich.',
            'url' => 'Die Website muss eine gültige URL sein.',
            'regex' => 'Das Website-Format ist ungültig.',
        ],
        'phone' => [
            'required' => 'Die Telefonnummer ist erforderlich.',
        ],
        'address' => [
            'required' => 'Die Adresse ist erforderlich.',
        ],
        'state' => [
            'required' => 'Das Bundesland ist erforderlich.',
        ],
        'country' => [
            'required' => 'Das Land ist erforderlich.',
        ],
        'gstin' => [
            'max' => 'Die GSTIN darf 15 Zeichen nicht überschreiten.',
        ],
        'default_currency' => [
            'required' => 'Die Standardwährung ist erforderlich.',
        ],
        'admin_logo' => [
            'mimes' => 'Das Admin-Logo muss eine Datei vom Typ: jpeg, png, jpg sein.',
            'max' => 'Das Admin-Logo darf nicht größer als 2 MB sein.',
        ],
        'fav_icon' => [
            'mimes' => 'Das Fav-Icon muss eine Datei vom Typ: jpeg, png, jpg sein.',
            'max' => 'Das Fav-Icon darf nicht größer als 2 MB sein.',
        ],
        'logo' => [
            'mimes' => 'Das Logo muss eine Datei vom Typ: jpeg, png, jpg sein.',
            'max' => 'Das Logo darf nicht größer als 2 MB sein.',
        ],
    ],

    'social_media_form' => [
        'name' => [
            'required' => 'Das Namensfeld ist erforderlich.',
            'unique' => 'Dieser Name existiert bereits.',
            'max' => 'Der Name darf nicht länger als 50 Zeichen sein.',
        ],
        'link' => [
            'required' => 'Das Linkfeld ist erforderlich.',
            'url' => 'Der Link muss eine gültige URL sein.',
            'regex' => 'Das Linkformat ist ungültig.',
        ],
    ],
    // Email
    'custom' => [
        'password' => [
            'required_if' => 'Das Passwortfeld ist für den ausgewählten Mail-Treiber erforderlich.',
        ],
        'port' => [
            'required_if' => 'Das Portfeld ist für SMTP erforderlich.',
        ],
        'encryption' => [
            'required_if' => 'Das Verschlüsselungsfeld ist für SMTP erforderlich.',
        ],
        'host' => [
            'required_if' => 'Das Hostfeld ist für SMTP erforderlich.',
        ],
        'secret' => [
            'required_if' => 'Das Geheimnisfeld ist für den ausgewählten Mail-Treiber erforderlich.',
        ],
        'domain' => [
            'required_if' => 'Das Domainfeld ist für Mailgun erforderlich.',
        ],
        'key' => [
            'required_if' => 'Das Schlüsselfeld ist für SES erforderlich.',
        ],
        'region' => [
            'required_if' => 'Das Regionsfeld ist für SES erforderlich.',
        ],
        'email' => [
            'required_if' => 'Das E-Mail-Feld ist für den ausgewählten Mail-Treiber erforderlich.',
            'required' => 'Das E-Mail-Feld ist erforderlich.',
            'email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
            'not_matching' => 'Die E-Mail-Domain muss mit der aktuellen Website-Domain übereinstimmen.',
        ],
        'driver' => [
            'required' => 'Das Treiberfeld ist erforderlich.',
        ],
    ],

    'customer_form' => [
        'first_name' => [
            'required' => 'Das Vornamefeld ist erforderlich.',
        ],
        'last_name' => [
            'required' => 'Das Nachnamefeld ist erforderlich.',
        ],
        'company' => [
            'required' => 'Das Firmenfeld ist erforderlich.',
        ],
        'mobile' => [
            'regex' => 'Das Mobilnummernformat ist ungültig.',
        ],
        'address' => [
            'required' => 'Das Adressfeld ist erforderlich.',
        ],
        'zip' => [
            'required' => 'Das Postleitzahlenfeld ist erforderlich.',
            'min' => 'Die Postleitzahl muss mindestens 5 Ziffern haben.',
            'numeric' => 'Die Postleitzahl muss numerisch sein.',
        ],
        'email' => [
            'required' => 'Das E-Mail-Feld ist erforderlich.',
            'email' => 'Die E-Mail-Adresse muss gültig sein.',
            'unique' => 'Diese E-Mail ist bereits vergeben.',
        ],
    ],

    'contact_request' => [
        'conName' => 'Das Namensfeld ist erforderlich.',
        'email' => 'Das E-Mail-Feld ist erforderlich.',
        'conmessage' => 'Das Nachrichtenfeld ist erforderlich.',
        'Mobile' => 'Das Mobiltelefonfeld ist erforderlich.',
        'country_code' => 'Das Mobiltelefonfeld ist erforderlich.',
        'demoname' => 'Das Namensfeld ist erforderlich.',
        'demomessage' => 'Das Nachrichtenfeld ist erforderlich.',
        'demoemail' => 'Das E-Mail-Feld ist erforderlich.',
        'congg-recaptcha-response-1.required' => 'Roboterüberprüfung fehlgeschlagen. Bitte versuchen Sie es erneut.',
        'demo-recaptcha-response-1.required' => 'Roboterüberprüfung fehlgeschlagen. Bitte versuchen Sie es erneut.',
    ],

    'frontend_pages' => [
        'name' => [
            'required' => 'Das Namensfeld ist erforderlich.',
            'unique' => 'Dieser Name existiert bereits.',
            'max' => 'Der Name darf 20 Zeichen nicht überschreiten.',
            'regex' => 'Der Name darf nur Buchstaben und Leerzeichen enthalten.',
        ],
        'publish' => [
            'required' => 'Das Veröffentlichungsfeld ist erforderlich.',
        ],
        'slug' => [
            'required' => 'Das Slug-Feld ist erforderlich.',
        ],
        'url' => [
            'required' => 'Das URL-Feld ist erforderlich.',
            'url' => 'Die URL muss ein gültiger Link sein.',
            'regex' => 'Das URL-Format ist ungültig.',
        ],
        'content' => [
            'required' => 'Das Inhaltsfeld ist erforderlich.',
        ],
        'created_at' => [
            'required' => 'Das Erstellungsdatumfeld ist erforderlich.',
        ],
    ],

    // Order form
    'order_form' => [
        'client' => [
            'required' => 'Das Kundenfeld ist erforderlich.',
        ],
        'payment_method' => [
            'required' => 'Das Zahlungsmethodenfeld ist erforderlich.',
        ],
        'promotion_code' => [
            'required' => 'Das Aktionscodefeld ist erforderlich.',
        ],
        'order_status' => [
            'required' => 'Das Bestellstatusfeld ist erforderlich.',
        ],
        'product' => [
            'required' => 'Das Produktfeld ist erforderlich.',
        ],
        'subscription' => [
            'required' => 'Das Abonnementfeld ist erforderlich.',
        ],
        'price_override' => [
            'numeric' => 'Der Preisüberschreibungswert muss eine Zahl sein.',
        ],
        'qty' => [
            'integer' => 'Die Menge muss eine ganze Zahl sein.',
        ],
    ],
    // Payment form
    'coupon_form' => [
        'code' => [
            'required' => 'Das Gutscheincode-Feld ist erforderlich.',
            'string' => 'Der Gutscheincode muss eine Zeichenkette sein.',
            'max' => 'Der Gutscheincode darf 255 Zeichen nicht überschreiten.',
        ],
        'type' => [
            'required' => 'Das Typfeld ist erforderlich.',
            'in' => 'Ungültiger Typ. Erlaubte Werte sind: percentage, other_type.',
        ],
        'applied' => [
            'required' => 'Das Anwendungsdatum für ein Produkt ist erforderlich.',
            'date' => 'Das Anwendungsdatum muss ein gültiges Datum sein.',
        ],
        'uses' => [
            'required' => 'Das Verwendungsfeld ist erforderlich.',
            'numeric' => 'Das Verwendungsfeld muss eine Zahl sein.',
            'min' => 'Das Verwendungsfeld muss mindestens :min betragen.',
        ],
        'start' => [
            'required' => 'Das Startfeld ist erforderlich.',
            'date' => 'Das Startdatum muss ein gültiges Datum sein.',
        ],
        'expiry' => [
            'required' => 'Das Ablaufdatumfeld ist erforderlich.',
            'date' => 'Das Ablaufdatum muss ein gültiges Datum sein.',
            'after' => 'Das Ablaufdatum muss nach dem Startdatum liegen.',
        ],
        'value' => [
            'required' => 'Das Rabattwert-Feld ist erforderlich.',
            'numeric' => 'Das Rabattwert-Feld muss eine Zahl sein.',
            'between' => 'Der Rabattwert muss zwischen :min und :max liegen, wenn der Typ "percentage" ist.',
        ],
    ],

    'tax_form' => [
        'name' => [
            'required' => 'Das Namensfeld ist erforderlich.',
        ],
        'rate' => [
            'required' => 'Das Steuersatzfeld ist erforderlich.',
            'numeric' => 'Der Steuersatz muss eine Zahl sein.',
        ],
        'level' => [
            'required' => 'Das Ebenenfeld ist erforderlich.',
            'integer' => 'Die Ebene muss eine ganze Zahl sein.',
        ],
        'country' => [
            'required' => 'Das Länderauswahlfeld ist erforderlich.',
            // 'exists' => 'Das ausgewählte Land ist ungültig.',
        ],
        'state' => [
            'required' => 'Das Bundeslandfeld ist erforderlich.',
            // 'exists' => 'Das ausgewählte Bundesland ist ungültig.',
        ],
    ],

    // Product
    'subscription_form' => [
        'name' => [
            'required' => 'Das Namensfeld ist erforderlich.',
        ],
        'subscription' => [
            'required' => 'Das Abonnementfeld ist erforderlich.',
        ],
        'regular_price' => [
            'required' => 'Das reguläre Preisfeld ist erforderlich.',
            'numeric' => 'Der reguläre Preis muss eine Zahl sein.',
        ],
        'selling_price' => [
            'required' => 'Das Verkaufspreisfeld ist erforderlich.',
            'numeric' => 'Der Verkaufspreis muss eine Zahl sein.',
        ],
        'products' => [
            'required' => 'Das Produktfeld ist erforderlich.',
        ],
    ],

    'bundle' => [
        'name' => [
            'required' => 'Das Namensfeld ist erforderlich.',
        ],
        'items' => [
            'required' => 'Jeder Artikel ist erforderlich.',
        ],
    ],

    'group' => [
        'name' => [
            'required' => 'Der Name ist erforderlich.',
        ],
        'features' => [
            'name' => [
                'required' => 'Alle Funktionsfelder sind erforderlich.',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => 'Der Preis ist erforderlich.',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => 'Der Wert ist erforderlich.',
            ],
        ],
        'type' => [
            'required_with' => 'Der Typ ist erforderlich.',
        ],
        'title' => [
            'required_with' => 'Der Titel ist erforderlich.',
        ],
    ],

    'product' => [
        'name' => [
            'required' => 'Das Namensfeld ist erforderlich.',
        ],
        'type' => [
            'required' => 'Das Typfeld ist erforderlich.',
        ],
        'group' => [
            'required' => 'Das Gruppierungsfeld ist erforderlich.',
        ],
        'subscription' => [
            'required' => 'Das Abonnementfeld ist erforderlich.',
        ],
        'currency' => [
            'required' => 'Das Währungsfeld ist erforderlich.',
        ],
    ],
    // 'price' => [
    //     'required' => 'The price field is required.',
    // ],
    'file' => [
        'required_without_all' => 'Das Dateifeld ist erforderlich, wenn weder github_owner noch github_repository angegeben sind.',
        'mimes' => 'Die Datei muss eine ZIP-Datei sein.',
    ],
    'image' => [
        'required_without_all' => 'Das Bildfeld ist erforderlich, wenn weder github_owner noch github_repository angegeben sind.',
        'mimes' => 'Das Bild muss eine PNG-Datei sein.',
    ],
    'github_owner' => [
        'required_without_all' => 'Das GitHub-Eigentümerfeld ist erforderlich, wenn weder Datei noch Bild angegeben sind.',
    ],
    'github_repository' => [
        'required_without_all' => 'Das GitHub-Repository-Feld ist erforderlich, wenn weder Datei noch Bild angegeben sind.',
        'required_if' => 'Das GitHub-Repository-Feld ist erforderlich, wenn der Typ 2 ist.',
    ],

    // User
    'users' => [
        'first_name' => [
            'required' => 'Das Vornamefeld ist erforderlich.',
        ],
        'last_name' => [
            'required' => 'Das Nachnamefeld ist erforderlich.',
        ],
        'company' => [
            'required' => 'Das Firmenfeld ist erforderlich.',
        ],
        'email' => [
            'required' => 'Das E-Mail-Feld ist erforderlich.',
            'email' => 'Die E-Mail-Adresse muss gültig sein.',
            'unique' => 'Die E-Mail-Adresse ist bereits vergeben.',
        ],
        'address' => [
            'required' => 'Das Adressfeld ist erforderlich.',
        ],
        'mobile' => [
            'required' => 'Das Mobilnummerfeld ist erforderlich.',
        ],
        'country' => [
            'required' => 'Das Länderfeld ist erforderlich.',
            'exists' => 'Das ausgewählte Land ist ungültig.',
        ],
        'state' => [
            'required_if' => 'Das Bundeslandfeld ist erforderlich, wenn das Land Indien ist.',
        ],
        'timezone_id' => [
            'required' => 'Das Zeitzonenfeld ist erforderlich.',
        ],
        'user_name' => [
            'required' => 'Das Benutzernamefeld ist erforderlich.',
            'unique' => 'Der Benutzername ist bereits vergeben.',
        ],
        'zip' => [
            'regex' => 'Das Bundeslandfeld ist erforderlich, wenn das Land Indien ist.',
        ],
    ],

    'profile_form' => [
        'first_name' => [
            'required' => 'Vorname ist erforderlich.',
            'min' => 'Vorname muss mindestens :min Zeichen lang sein.',
            'max' => 'Vorname darf nicht länger als :max Zeichen sein.',
        ],
        'last_name' => [
            'required' => 'Nachname ist erforderlich.',
            'max' => 'Nachname darf nicht länger als :max Zeichen sein.',
        ],
        'company' => [
            'required' => 'Firmenname ist erforderlich.',
            'max' => 'Firmenname darf nicht länger als :max Zeichen sein.',
        ],
        'email' => [
            'required' => 'E-Mail ist erforderlich.',
            'email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
            'unique' => 'Die E-Mail-Adresse ist bereits vergeben. Bitte wählen Sie eine andere.',
        ],
        'mobile' => [
            'required' => 'Mobilnummer ist erforderlich.',
            'regex' => 'Bitte geben Sie eine gültige Mobilnummer ein.',
            'min' => 'Mobilnummer muss mindestens :min Zeichen lang sein.',
            'max' => 'Mobilnummer darf nicht länger als :max Zeichen sein.',
        ],
        'address' => [
            'required' => 'Adresse ist erforderlich.',
        ],
        'user_name' => [
            'required' => 'Benutzername ist erforderlich.',
            'unique' => 'Dieser Benutzername ist bereits vergeben.',
        ],
        'timezone_id' => [
            'required' => 'Zeitzone ist erforderlich.',
        ],
        'country' => [
            'required' => 'Land ist erforderlich.',
            'exists' => 'Das ausgewählte Land ist ungültig.',
        ],
        'state' => [
            'required_if' => 'Das Bundeslandfeld ist erforderlich, wenn das Land Indien ist.',
        ],
        'old_password' => [
            'required' => 'Altes Passwort ist erforderlich.',
            'min' => 'Das alte Passwort muss mindestens :min Zeichen lang sein.',
        ],
        'new_password' => [
            'required' => 'Neues Passwort ist erforderlich.',
            'different' => 'Das neue Passwort muss sich vom alten unterscheiden.',
        ],
        'confirm_password' => [
            'required' => 'Passwortbestätigung ist erforderlich.',
            'same' => 'Passwortbestätigung muss mit dem neuen Passwort übereinstimmen.',
        ],
        'terms' => [
            'required' => 'Sie müssen die Bedingungen akzeptieren.',
        ],
        'password' => [
            'required' => 'Passwort ist erforderlich.',
        ],
        'password_confirmation' => [
            'required' => 'Passwortbestätigung ist erforderlich.',
            'same' => 'Passwörter stimmen nicht überein.',
        ],
        'mobile_code' => [
            'required' => 'Bitte geben Sie den Ländercode (Mobil) ein.',
        ],
    ],

    // Invoice form
    'invoice' => [
        'user' => [
            'required' => 'Das Kundenfeld ist erforderlich.',
        ],
        'date' => [
            'required' => 'Das Datumsfeld ist erforderlich.',
            'date' => 'Das Datum muss ein gültiges Datum sein.',
        ],
        'domain' => [
            'regex' => 'Das Domain-Format ist ungültig.',
        ],
        'plan' => [
            'required_if' => 'Das Abonnementfeld ist erforderlich.',
        ],
        'price' => [
            'required' => 'Das Preisfeld ist erforderlich.',
        ],
        'product' => [
            'required' => 'Das Produktfeld ist erforderlich.',
        ],
    ],

    // LocalizedLicense form
    'domain_form' => [
        'domain' => [
            'required' => 'Das Domainfeld ist erforderlich.',
            'url' => 'Die Domain muss eine gültige URL sein.',
        ],
    ],
    //Product Renewal form
    'product_renewal' => [
        'domain' => [
            'required' => 'Das Domainfeld ist erforderlich.',
            'no_http' => 'Die Domain darf kein "http" oder "https" enthalten.',
        ],
    ],

    //Language form
    'language' => [
        'required' => 'Das Sprachfeld ist erforderlich.',
        'invalid' => 'Die ausgewählte Sprache ist ungültig.',
    ],

    //UpdateStoragePathRequest form
    'storage_path' => [
        'disk' => [
            'required' => 'Das Speicherlaufwerksfeld ist erforderlich.',
            'string' => 'Das Laufwerk muss ein String sein.',
        ],
        'path' => [
            'string' => 'Der Pfad muss ein String sein.',
            'nullable' => 'Das Pfadfeld ist optional.',
        ],
    ],

    //ValidateSecretRequest form
    'validate_secret' => [
        'totp' => [
            'required' => 'Bitte Code eingeben',
            'digits' => 'Bitte geben Sie einen gültigen 6-stelligen Code ein',
        ],
    ],

    //VerifyOtp form
    'verify_email' => [
        'required' => 'Das E-Mail-Feld ist erforderlich.',
        'email' => 'Die E-Mail muss eine gültige E-Mail-Adresse sein.',
        'verify_email' => 'Die E-Mail-Verifizierung ist fehlgeschlagen.',
    ],

    'verify_country_code' => [
        'required' => 'Der Ländercode ist erforderlich.',
        'numeric' => 'Der Ländercode muss eine gültige Zahl sein.',
        'verify_country_code' => 'Die Verifizierung des Ländercodes ist fehlgeschlagen.',
    ],

    'verify_number' => [
        'required' => 'Die Nummer ist erforderlich.',
        'numeric' => 'Die Nummer muss eine gültige Zahl sein.',
        'verify_number' => 'Die Nummernverifizierung ist fehlgeschlagen.',
    ],

    'password_otp' => [
        'required' => 'Das Passwortfeld ist erforderlich.',
        'password' => 'Das Passwort ist falsch.',
        'invalid' => 'Ungültiges Passwort.',
    ],

    //AuthController file
    'auth_controller' => [
        'name_required' => 'Name ist erforderlich.',
        'name_max' => 'Name darf nicht länger als 255 Zeichen sein.',

        'email_required' => 'E-Mail ist erforderlich.',
        'email_email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
        'email_max' => 'E-Mail darf nicht länger als 255 Zeichen sein.',
        'email_unique' => 'Diese E-Mail ist bereits registriert.',

        'password_required' => 'Passwort ist erforderlich.',
        'password_confirmed' => 'Die Passwortbestätigung stimmt nicht überein.',
        'password_min' => 'Das Passwort muss mindestens 6 Zeichen lang sein.',
    ],

    'resend_otp' => [
        'eid_required' => 'Das EID-Feld ist erforderlich.',
        'eid_string' => 'Die EID muss ein String sein.',
        'type_required' => 'Das Typfeld ist erforderlich.',
        'type_string' => 'Der Typ muss ein String sein.',
        'type_in' => 'Der ausgewählte Typ ist ungültig.',
    ],

    'verify_otp' => [
        'eid_required' => 'Die Mitarbeiter-ID ist erforderlich.',
        'eid_string' => 'Die Mitarbeiter-ID muss ein String sein.',
        'otp_required' => 'Der OTP ist erforderlich.',
        'otp_size' => 'Der OTP muss genau 6 Zeichen lang sein.',
        'recaptcha_required' => 'Bitte vervollständigen Sie das CAPTCHA.',
        'recaptcha_size' => 'Die CAPTCHA-Antwort ist ungültig.',
    ],

    'company_validation' => [
        'company_required' => 'Der Firmenname ist erforderlich.',
        'company_string' => 'Das Unternehmen muss ein Text sein.',
        'address_required' => 'Die Adresse ist erforderlich.',
        'address_string' => 'Die Adresse muss ein Text sein.',
    ],

    'token_validation' => [
        'token_required' => 'Das Token ist erforderlich.',
        'password_required' => 'Das Passwortfeld ist erforderlich.',
        'password_confirmed' => 'Die Passwortbestätigung stimmt nicht überein.',
    ],

    'custom_email' => [
        'required' => 'Das E-Mail-Feld ist erforderlich.',
        'email' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
        'exists' => 'Diese E-Mail ist bei uns nicht registriert.',
    ],

    'newsletterEmail' => [
        'required' => 'Die Newsletter-E-Mail ist erforderlich.',
        'email' => 'Bitte geben Sie eine gültige E-Mail-Adresse für den Newsletter ein.',
    ],

    'widget' => [
        'name_required' => 'Name ist erforderlich.',
        'name_max' => 'Name darf nicht länger als 50 Zeichen sein.',
        'publish_required' => 'Der Veröffentlichungsstatus ist erforderlich.',
        'type_required' => 'Typ ist erforderlich.',
        'type_unique' => 'Dieser Typ existiert bereits.',
    ],

    'payment' => [
        'payment_date_required' => 'Zahlungsdatum ist erforderlich.',
        'payment_method_required' => 'Zahlungsmethode ist erforderlich.',
        'amount_required' => 'Betrag ist erforderlich.',
    ],

    'custom_date' => [
        'date_required' => 'Das Datumsfeld ist erforderlich.',
        'total_required' => 'Das Gesamtsummenfeld ist erforderlich.',
        'status_required' => 'Das Statusfeld ist erforderlich.',
    ],

    'plan_renewal' => [
        'plan_required' => 'Das Abonnementfeld ist erforderlich.',
        'payment_method_required' => 'Das Zahlungsmethodenfeld ist erforderlich.',
        'cost_required' => 'Das Kostenfeld ist erforderlich.',
        'code_not_valid' => 'Der Aktionscode ist ungültig.',
    ],

    'rate' => [
        'required' => 'Der Satz ist erforderlich.',
        'numeric' => 'Der Satz muss eine Zahl sein.',
    ],

    'product_validate' => [
        'producttitle_required' => 'Produktbezeichnung ist erforderlich.',
        'version_required' => 'Version ist erforderlich.',
        'filename_required' => 'Bitte laden Sie eine Datei hoch.',
        'dependencies_required' => 'Das Abhängigkeitsfeld ist erforderlich.',
    ],
    'product_sku_unique' => 'Die Produkt-SKU muss eindeutig sein.',
    'product_name_unique' => 'Der Name muss eindeutig sein.',
    'product_show_agent_required' => 'Wählen Sie Ihre Cart-Seitenpräferenz.',
    'product_controller' => [
        'name_required' => 'Der Produktname ist erforderlich.',
        'name_unique' => 'Der Name muss eindeutig sein.',
        'type_required' => 'Der Produkttyp ist erforderlich.',
        'description_required' => 'Die Produktbeschreibung ist erforderlich.',
        'product_description_required' => 'Die detaillierte Produktbeschreibung ist erforderlich.',
        'image_mimes' => 'Das Bild muss eine Datei vom Typ: jpeg, png, jpg sein.',
        'image_max' => 'Das Bild darf nicht größer als 2048 Kilobytes sein.',
        'product_sku_required' => 'Die Produkt-SKU ist erforderlich.',
        'group_required' => 'Die Produktgruppe ist erforderlich.',
        'show_agent_required' => 'Wählen Sie Ihre Cart-Seitenpräferenz.',
    ],
    'current_domain_required' => 'Aktuelle Domain ist erforderlich.',
    'new_domain_required' => 'Neue Domain ist erforderlich.',
    'special_characters_not_allowed' => 'Sonderzeichen sind im Domainnamen nicht erlaubt.',
    'orderno_required' => 'Bestellnummer ist erforderlich.',
    'cloud_central_domain_required' => 'Cloud-Zentral-Domain ist erforderlich.',
    'cloud_cname_required' => 'Cloud CNAME ist erforderlich.',
    'cloud_tenant' => [
        'cloud_top_message_required' => 'Cloud-Hauptnachricht ist erforderlich.',
        'cloud_label_field_required' => 'Cloud-Beschriftungsfeld ist erforderlich.',
        'cloud_label_radio_required' => 'Cloud-Beschriftungsradio ist erforderlich.',
        'cloud_product_required' => 'Cloud-Produkt ist erforderlich.',
        'cloud_free_plan_required' => 'Cloud Free Plan ist erforderlich.',
        'cloud_product_key_required' => 'Cloud-Produkt-Schlüssel ist erforderlich.',
    ],
    'reg_till_after' => 'Das Registrierungs-Bis-Datum muss nach dem Registrierungs-Von-Datum liegen.',
    'extend_product' => [
        'title_required' => 'Das Titel-Feld ist erforderlich.',
        'version_required' => 'Das Versions-Feld ist erforderlich.',
        'dependencies_required' => 'Das Abhängigkeits-Feld ist erforderlich.',
    ],
    'please_enter_recovery_code' => 'Bitte geben Sie den Wiederherstellungscode ein.',
    'social_login' => [
        'client_id_required' => 'Client-ID wird für Google, Github oder LinkedIn benötigt.',
        'client_secret_required' => 'Client Secret wird für Google, Github oder LinkedIn benötigt.',
        'api_key_required' => 'API-Schlüssel wird für Twitter benötigt.',
        'api_secret_required' => 'API-Geheimnis wird für Twitter benötigt.',
        'redirect_url_required' => 'Weiterleitungs-URL ist erforderlich.',
    ],
    'thirdparty_api' => [
        'app_name_required' => 'App-Name ist erforderlich.',
        'app_key_required' => 'App-Schlüssel ist erforderlich.',
        'app_key_size' => 'Der App-Schlüssel muss genau 32 Zeichen lang sein.',
        'app_secret_required' => 'App-Geheimnis ist erforderlich.',
    ],
    'plan_request' => [
        'name_required' => 'Das Namensfeld ist erforderlich',
        'product_quant_req' => 'Das Feld Produktmenge ist erforderlich, wenn keine Agentenanzahl vorhanden ist.',
        'no_agent_req' => 'Das Feld Agentenanzahl ist erforderlich, wenn keine Produktmenge vorhanden ist.',
        'pro_req' => 'Das Produktfeld ist erforderlich',
        'offer_price' => 'Der Angebotspreis darf 100 nicht überschreiten',
    ],
    'razorpay_val' => [
        'business_required' => 'Das Feld Geschäft ist erforderlich.',
        'cmd_required' => 'Das Befehlsfeld ist erforderlich.',
        'paypal_url_required' => 'Die PayPal-URL ist erforderlich.',
        'paypal_url_invalid' => 'Die PayPal-URL muss eine gültige URL sein.',
        'success_url_invalid' => 'Die Erfolgs-URL muss eine gültige URL sein.',
        'cancel_url_invalid' => 'Die Abbruch-URL muss eine gültige URL sein.',
        'notify_url_invalid' => 'Die Benachrichtigungs-URL muss eine gültige URL sein.',
        'currencies_required' => 'Das Währungsfeld ist erforderlich.',
    ],
    'login_failed' => 'Anmeldung fehlgeschlagen, bitte überprüfen Sie die eingegebene E-Mail/Benutzername und das Passwort.',
    'forgot_email_validation' => 'Wenn die von Ihnen angegebene E-Mail registriert ist, erhalten Sie in Kürze eine E-Mail mit Anweisungen zum Zurücksetzen des Passworts.',
    'too_many_login_attempts' => 'Sie wurden aufgrund zu vieler fehlgeschlagener Anmeldeversuche aus der Anwendung ausgesperrt. Bitte versuchen Sie es nach :time erneut.',

];
