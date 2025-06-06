<?php

return [

    'accepted' => 'Il :attribute deve essere accettato.',
    'accepted_if' => 'Il :attribute deve essere accettato quando :other è :value.',
    'active_url' => 'Il :attribute non è un URL valido.',
    'after' => 'Il :attribute deve essere una data successiva a :date.',
    'after_or_equal' => 'Il :attribute deve essere una data successiva o uguale a :date.',
    'alpha' => 'Il :attribute deve contenere solo lettere.',
    'alpha_dash' => 'Il :attribute deve contenere solo lettere, numeri, trattini e underscore.',
    'alpha_num' => 'Il :attribute deve contenere solo lettere e numeri.',
    'array' => 'Il :attribute deve essere un array.',
    'before' => 'Il :attribute deve essere una data precedente a :date.',
    'before_or_equal' => 'Il :attribute deve essere una data precedente o uguale a :date.',
    'between' => [
        'array' => 'Il :attribute deve contenere tra :min e :max elementi.',
        'file' => 'Il :attribute deve essere tra :min e :max kilobyte.',
        'numeric' => 'Il :attribute deve essere tra :min e :max.',
        'string' => 'Il :attribute deve essere tra :min e :max caratteri.',
    ],
    'boolean' => 'Il campo :attribute deve essere vero o falso.',
    'confirmed' => 'La conferma di :attribute non corrisponde.',
    'current_password' => 'La password è errata.',
    'date' => 'Il :attribute non è una data valida.',
    'date_equals' => 'Il :attribute deve essere una data uguale a :date.',
    'date_format' => 'Il :attribute non corrisponde al formato :format.',
    'declined' => 'Il :attribute deve essere rifiutato.',
    'declined_if' => 'Il :attribute deve essere rifiutato quando :other è :value.',
    'different' => 'Il :attribute e :other devono essere diversi.',
    'digits' => 'Il :attribute deve essere composto da :digits cifre.',
    'digits_between' => 'Il :attribute deve avere tra :min e :max cifre.',
    'dimensions' => 'Le dimensioni dell\'immagine :attribute non sono valide.',
    'distinct' => 'Il campo :attribute ha un valore duplicato.',
    'doesnt_start_with' => 'Il :attribute non può iniziare con uno dei seguenti: :values.',
    'email' => 'Il :attribute deve essere un indirizzo email valido.',
    'ends_with' => 'Il :attribute deve terminare con uno dei seguenti: :values.',
    'enum' => 'Il :attribute selezionato non è valido.',
    'exists' => 'Il :attribute selezionato non è valido.',
    'file' => 'Il :attribute deve essere un file.',
    'filled' => 'Il campo :attribute deve avere un valore.',
    'gt' => [
        'array' => 'Il :attribute deve contenere più di :value elementi.',
        'file' => 'Il :attribute deve essere maggiore di :value kilobyte.',
        'numeric' => 'Il :attribute deve essere maggiore di :value.',
        'string' => 'Il :attribute deve essere maggiore di :value caratteri.',
    ],
    'gte' => [
        'array' => 'Il :attribute deve contenere almeno :value elementi.',
        'file' => 'Il :attribute deve essere maggiore o uguale a :value kilobyte.',
        'numeric' => 'Il :attribute deve essere maggiore o uguale a :value.',
        'string' => 'Il :attribute deve essere maggiore o uguale a :value caratteri.',
    ],
    'image' => 'Il :attribute deve essere un\'immagine.',
    'in' => 'Il :attribute selezionato non è valido.',
    'in_array' => 'Il campo :attribute non esiste in :other.',
    'integer' => 'Il :attribute deve essere un intero.',
    'ip' => 'Il :attribute deve essere un indirizzo IP valido.',
    'ipv4' => 'Il :attribute deve essere un indirizzo IPv4 valido.',
    'ipv6' => 'Il :attribute deve essere un indirizzo IPv6 valido.',
    'json' => 'Il :attribute deve essere una stringa JSON valida.',
    'lt' => [
        'array' => 'Il :attribute deve contenere meno di :value elementi.',
        'file' => 'Il :attribute deve essere minore di :value kilobyte.',
        'numeric' => 'Il :attribute deve essere minore di :value.',
        'string' => 'Il :attribute deve essere minore di :value caratteri.',
    ],
    'lte' => [
        'array' => 'Il :attribute non deve contenere più di :value elementi.',
        'file' => 'Il :attribute deve essere minore o uguale a :value kilobyte.',
        'numeric' => 'Il :attribute deve essere minore o uguale a :value.',
        'string' => 'Il :attribute deve essere minore o uguale a :value caratteri.',
    ],
    'mac_address' => 'Il :attribute deve essere un indirizzo MAC valido.',
    'max' => [
        'array' => 'Il :attribute non deve contenere più di :max elementi.',
        'file' => 'Il :attribute non deve essere maggiore di :max kilobyte.',
        'numeric' => 'Il :attribute non deve essere maggiore di :max.',
        'string' => 'Il :attribute non deve essere maggiore di :max caratteri.',
    ],
    'mimes' => 'Il :attribute deve essere un file di tipo: :values.',
    'mimetypes' => 'Il :attribute deve essere un file di tipo: :values.',
    'min' => [
        'array' => 'Il :attribute deve contenere almeno :min elementi.',
        'file' => 'Il :attribute deve essere almeno :min kilobyte.',
        'numeric' => 'Il :attribute deve essere almeno :min.',
        'string' => 'Il :attribute deve essere almeno :min caratteri.',
    ],
    'multiple_of' => 'Il :attribute deve essere un multiplo di :value.',
    'not_in' => 'Il :attribute selezionato non è valido.',
    'not_regex' => 'Il formato di :attribute non è valido.',
    'numeric' => 'Il :attribute deve essere un numero.',
    'password' => [
        'letters' => 'Il :attribute deve contenere almeno una lettera.',
        'mixed' => 'Il :attribute deve contenere almeno una lettera maiuscola e una minuscola.',
        'numbers' => 'Il :attribute deve contenere almeno un numero.',
        'symbols' => 'Il :attribute deve contenere almeno un simbolo.',
        'uncompromised' => 'Il :attribute fornito è apparso in una violazione dei dati. Scegli un altro :attribute.',
    ],
    'present' => 'Il campo :attribute deve essere presente.',
    'prohibited' => 'Il campo :attribute è vietato.',
    'prohibited_if' => 'Il campo :attribute è vietato quando :other è :value.',
    'prohibited_unless' => 'Il campo :attribute è vietato a meno che :other non sia in :values.',
    'prohibits' => 'Il campo :attribute proibisce che :other sia presente.',
    'regex' => 'Il formato di :attribute non è valido.',
    'required' => 'Il campo :attribute è obbligatorio.',
    'required_array_keys' => 'Il campo :attribute deve contenere voci per: :values.',
    'required_if' => 'Il campo :attribute è obbligatorio quando :other è :value.',
    'required_unless' => 'Il campo :attribute è obbligatorio a meno che :other non sia in :values.',
    'required_with' => 'Il campo :attribute è obbligatorio quando :values è presente.',
    'required_with_all' => 'Il campo :attribute è obbligatorio quando :values sono presenti.',
    'required_without' => 'Il campo :attribute è obbligatorio quando :values non è presente.',
    'required_without_all' => 'Il campo :attribute è obbligatorio quando nessuno di :values è presente.',
    'same' => 'Il :attribute e :other devono corrispondere.',
    'size' => [
        'array' => 'Il :attribute deve contenere :size elementi.',
        'file' => 'Il :attribute deve essere di :size kilobyte.',
        'numeric' => 'Il :attribute deve essere :size.',
        'string' => 'Il :attribute deve essere di :size caratteri.',
    ],
    'starts_with' => 'Il :attribute deve iniziare con uno dei seguenti: :values.',
    'string' => 'Il :attribute deve essere una stringa.',
    'timezone' => 'Il :attribute deve essere un fuso orario valido.',
    'unique' => 'Il :attribute è già stato preso.',
    'uploaded' => 'Il :attribute non è riuscito a caricarsi.',
    'url' => 'Il :attribute deve essere un URL valido.',
    'uuid' => 'Il :attribute deve essere un UUID valido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Qui puoi specificare messaggi di validazione personalizzati per gli
    | attributi utilizzando la convenzione "attribute.rule" per nominare le righe.
    | Questo ti consente di specificare rapidamente una linea di lingua personalizzata
    | per una determinata regola di attributo.
    |
    */

    'custom_dup' => [
        'attribute-name' => [
            'rule-name' => 'messaggio-personalizzato',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | Le seguenti linee di lingua vengono utilizzate per sostituire il segnaposto
    | dell'attributo con qualcosa di più leggibile, come "Indirizzo e-mail" al posto di "email".
    | Questo ci aiuta semplicemente a rendere i messaggi più espressivi.
    |
    */

    'attributes' => [],

    'publish_date_required' => 'La data di pubblicazione è obbligatoria',
    'price_numeric_value' => 'Il prezzo deve essere un valore numerico',
    'quantity_integer_value' => 'La quantità deve essere un valore intero',
    'order_has_Expired' => 'L\'ordine è scaduto',
    'expired' => 'Scaduto',
    'eid_required' => 'Il campo EID è obbligatorio.',
    'eid_string' => 'L\'EID deve essere una stringa.',
    'otp_required' => 'Il campo OTP è obbligatorio.',
    'amt_required' => 'Il campo importo è obbligatorio',
    'amt_numeric' => 'L\'importo deve essere un numero',
    'payment_date_required' => 'La data di pagamento è obbligatoria.',
    'payment_method_required' => 'Il metodo di pagamento è obbligatorio.',
    'total_amount_required' => 'L\'importo totale è obbligatorio.',
    'total_amount_numeric' => 'L\'importo totale deve essere un valore numerico.',
    'invoice_link_required' => 'Si prega di collegare l\'importo con almeno una fattura.',
    /*
    Request file custom validation messages
    */

    //Common
    'settings_form' => [
        'company' => [
            'required' => 'Il campo azienda è obbligatorio.',
        ],
        'website' => [
            'url' => 'Il sito web deve essere un URL valido.',
        ],
        'phone' => [
            'regex' => 'Il formato del numero di telefono è invalido.',
        ],
        'address' => [
            'required' => 'Il campo indirizzo è obbligatorio.',
            'max' => 'L\'indirizzo non può superare i 300 caratteri.',
        ],
        'logo' => [
            'mimes' => 'Il logo deve essere un file PNG.',
        ],
        'driver' => [
            'required' => 'Il campo conducente è obbligatorio.',
        ],
        'port' => [
            'integer' => 'La porta deve essere un numero intero.',
        ],
        'email' => [
            'required' => 'Il campo email è obbligatorio.',
            'email' => 'L\'email deve essere un indirizzo email valido.',
        ],
        'password' => [
            'required' => 'Il campo password è obbligatorio.',
        ],
        'error_email' => [
            'email' => 'L\'email di errore deve essere un indirizzo email valido.',
        ],
    ],

    'settings_forms' => [
        'company' => [
            'required' => 'Il nome dell\'azienda è obbligatorio.',
            'max' => 'Il nome dell\'azienda non può superare i 50 caratteri.',
        ],
        'company_email' => [
            'required' => 'L\'email dell\'azienda è obbligatoria.',
            'email' => 'L\'email dell\'azienda deve essere un indirizzo email valido.',
        ],
        'title' => [
            'max' => 'Il titolo non può superare i 50 caratteri.',
        ],
        'website' => [
            'required' => 'Il sito web è obbligatorio.',
            'url' => 'Il sito web deve essere un URL valido.',
            'regex' => 'Il formato del sito web è invalido.',
        ],
        'phone' => [
            'required' => 'Il numero di telefono è obbligatorio.',
        ],
        'address' => [
            'required' => 'L\'indirizzo è obbligatorio.',
        ],
        'state' => [
            'required' => 'Lo stato è obbligatorio.',
        ],
        'country' => [
            'required' => 'Il paese è obbligatorio.',
        ],
        'gstin' => [
            'max' => 'Il GSTIN non può superare i 15 caratteri.',
        ],
        'default_currency' => [
            'required' => 'La valuta predefinita è obbligatoria.',
        ],
        'admin_logo' => [
            'mimes' => 'Il logo dell\'amministratore deve essere un file di tipo: jpeg, png, jpg.',
            'max' => 'Il logo dell\'amministratore non può superare i 2MB.',
        ],
        'fav_icon' => [
            'mimes' => 'Il fav icon deve essere un file di tipo: jpeg, png, jpg.',
            'max' => 'Il fav icon non può superare i 2MB.',
        ],
        'logo' => [
            'mimes' => 'Il logo deve essere un file di tipo: jpeg, png, jpg.',
            'max' => 'Il logo non può superare i 2MB.',
        ],
    ],

    'social_media_form' => [
        'name' => [
            'required' => 'Il campo nome è obbligatorio.',
            'unique' => 'Questo nome esiste già.',
            'max' => 'Il nome non può superare i 50 caratteri.',
        ],
        'link' => [
            'required' => 'Il campo link è obbligatorio.',
            'url' => 'Il link deve essere un URL valido.',
            'regex' => 'Il formato del link è invalido.',
        ],
    ],

    //Email
    'custom' => [
        'password' => [
            'required_if' => 'Il campo password è obbligatorio per il driver di posta selezionato.',
        ],
        'port' => [
            'required_if' => 'Il campo porta è obbligatorio per SMTP.',
        ],
        'encryption' => [
            'required_if' => 'Il campo crittografia è obbligatorio per SMTP.',
        ],
        'host' => [
            'required_if' => 'Il campo host è obbligatorio per SMTP.',
        ],
        'secret' => [
            'required_if' => 'Il campo segreto è obbligatorio per il driver di posta selezionato.',
        ],
        'domain' => [
            'required_if' => 'Il campo dominio è obbligatorio per Mailgun.',
        ],
        'key' => [
            'required_if' => 'Il campo chiave è obbligatorio per SES.',
        ],
        'region' => [
            'required_if' => 'Il campo regione è obbligatorio per SES.',
        ],
        'email' => [
            'required_if' => 'Il campo email è obbligatorio per il driver di posta selezionato.',
            'required' => 'Il campo email è obbligatorio.',
            'email' => 'Si prega di inserire un indirizzo email valido.',
            'not_matching' => 'Il dominio email deve corrispondere al dominio del sito attuale.',
        ],
        'driver' => [
            'required' => 'Il campo driver è obbligatorio.',
        ],
    ],

    'customer_form' => [
        'first_name' => [
            'required' => 'Il campo nome è obbligatorio.',
        ],
        'last_name' => [
            'required' => 'Il campo cognome è obbligatorio.',
        ],
        'company' => [
            'required' => 'Il campo azienda è obbligatorio.',
        ],
        'mobile' => [
            'regex' => 'Il formato del numero di cellulare è invalido.',
        ],
        'address' => [
            'required' => 'Il campo indirizzo è obbligatorio.',
        ],
        'zip' => [
            'required' => 'Il campo CAP è obbligatorio.',
            'min' => 'Il CAP deve essere di almeno 5 cifre.',
            'numeric' => 'Il CAP deve essere numerico.',
        ],
        'email' => [
            'required' => 'Il campo email è obbligatorio.',
            'email' => 'L\'email deve essere un indirizzo email valido.',
            'unique' => 'Questa email è già stata presa.',
        ],
    ],
    'contact_request' => [
        'conName' => 'Il campo nome è obbligatorio.',
        'email' => 'Il campo email è obbligatorio.',
        'conmessage' => 'Il campo messaggio è obbligatorio.',
        'Mobile' => 'Il campo cellulare è obbligatorio.',
        'country_code' => 'Il campo cellulare è obbligatorio.',
        'demoname' => 'Il campo nome è obbligatorio.',
        'demomessage' => 'Il campo messaggio è obbligatorio.',
        'demoemail' => 'Il campo email è obbligatorio.',
        'congg-recaptcha-response-1.required' => 'Verifica del robot fallita. Per favore riprova.',
        'demo-recaptcha-response-1.required' => 'Verifica del robot fallita. Per favore riprova.',
    ],

    'frontend_pages' => [
        'name' => [
            'required' => 'Il campo nome è obbligatorio.',
            'unique' => 'Questo nome esiste già.',
            'max' => 'Il nome non deve superare i 20 caratteri.',
            'regex' => 'Il nome può contenere solo lettere e spazi.',
        ],
        'publish' => [
            'required' => 'Il campo pubblica è obbligatorio.',
        ],
        'slug' => [
            'required' => 'Il campo slug è obbligatorio.',
        ],
        'url' => [
            'required' => 'Il campo URL è obbligatorio.',
            'url' => 'L\'URL deve essere un link valido.',
            'regex' => 'Il formato dell\'URL è invalido.',
        ],
        'content' => [
            'required' => 'Il campo contenuto è obbligatorio.',
        ],
        'created_at' => [
            'required' => 'Il campo creato il è obbligatorio.',
        ],
    ],

    //Order form
    'order_form' => [
        'client' => [
            'required' => 'Il campo cliente è obbligatorio.',
        ],
        'payment_method' => [
            'required' => 'Il campo metodo di pagamento è obbligatorio.',
        ],
        'promotion_code' => [
            'required' => 'Il campo codice promozionale è obbligatorio.',
        ],
        'order_status' => [
            'required' => 'Il campo stato dell\'ordine è obbligatorio.',
        ],
        'product' => [
            'required' => 'Il campo prodotto è obbligatorio.',
        ],
        'subscription' => [
            'required' => 'Il campo abbonamento è obbligatorio.',
        ],
        'price_override' => [
            'numeric' => 'Il campo modifica del prezzo deve essere un numero.',
        ],
        'qty' => [
            'integer' => 'La quantità deve essere un numero intero.',
        ],
    ],

    //Payment form
    'coupon_form' => [
        'code' => [
            'required' => 'Il campo codice coupon è obbligatorio.',
            'string' => 'Il codice coupon deve essere una stringa.',
            'max' => 'Il codice coupon non deve superare i 255 caratteri.',
        ],
        'type' => [
            'required' => 'Il campo tipo è obbligatorio.',
            'in' => 'Tipo non valido. I valori consentiti sono: percentuale, altro_tipo.',
        ],
        'applied' => [
            'required' => 'Il campo applicato per un prodotto è obbligatorio.',
            'date' => 'Il campo applicato per un prodotto deve essere una data valida.',
        ],
        'uses' => [
            'required' => 'Il campo utilizzi è obbligatorio.',
            'numeric' => 'Il campo utilizzi deve essere un numero.',
            'min' => 'Il campo utilizzi deve essere almeno :min.',
        ],
        'start' => [
            'required' => 'Il campo inizio è obbligatorio.',
            'date' => 'Il campo inizio deve essere una data valida.',
        ],
        'expiry' => [
            'required' => 'Il campo scadenza è obbligatorio.',
            'date' => 'Il campo scadenza deve essere una data valida.',
            'after' => 'La data di scadenza deve essere successiva alla data di inizio.',
        ],
        'value' => [
            'required' => 'Il campo valore sconto è obbligatorio.',
            'numeric' => 'Il campo valore sconto deve essere un numero.',
            'between' => 'Il campo valore sconto deve essere tra :min e :max se il tipo è percentuale.',
        ],
    ],

    'tax_form' => [
        'name' => [
            'required' => 'Il campo nome è obbligatorio.',
        ],
        'rate' => [
            'required' => 'Il campo tasso è obbligatorio.',
            'numeric' => 'Il tasso deve essere un numero.',
        ],
        'level' => [
            'required' => 'Il campo livello è obbligatorio.',
            'integer' => 'Il livello deve essere un numero intero.',
        ],
        'country' => [
            'required' => 'Il campo paese è obbligatorio.',
            // 'exists' => 'Il paese selezionato non è valido.',
        ],
        'state' => [
            'required' => 'Il campo stato è obbligatorio.',
            // 'exists' => 'Lo stato selezionato non è valido.',
        ],
    ],

    //Product
    'subscription_form' => [
        'name' => [
            'required' => 'Il campo nome è obbligatorio.',
        ],
        'subscription' => [
            'required' => 'Il campo abbonamento è obbligatorio.',
        ],
        'regular_price' => [
            'required' => 'Il campo prezzo regolare è obbligatorio.',
            'numeric' => 'Il prezzo regolare deve essere un numero.',
        ],
        'selling_price' => [
            'required' => 'Il campo prezzo di vendita è obbligatorio.',
            'numeric' => 'Il prezzo di vendita deve essere un numero.',
        ],
        'products' => [
            'required' => 'Il campo prodotti è obbligatorio.',
        ],
    ],

    'bundle' => [
        'name' => [
            'required' => 'Il campo nome è obbligatorio.',
        ],
        'items' => [
            'required' => 'Ogni elemento è obbligatorio.',
        ],
    ],
    'group' => [
        'name' => [
            'required' => 'Il campo nome è obbligatorio',
        ],
        'features' => [
            'name' => [
                'required' => 'Tutti i campi delle caratteristiche sono obbligatori',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => 'Il campo prezzo è obbligatorio',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => 'Il campo valore è obbligatorio',
            ],
        ],
        'type' => [
            'required_with' => 'Il campo tipo è obbligatorio',
        ],
        'title' => [
            'required_with' => 'Il campo titolo è obbligatorio',
        ],
    ],

    'product' => [
        'name' => [
            'required' => 'Il campo nome è obbligatorio.',
        ],
        'type' => [
            'required' => 'Il campo tipo è obbligatorio.',
        ],
        'group' => [
            'required' => 'Il campo gruppo è obbligatorio.',
        ],
        'subscription' => [
            'required' => 'Il campo abbonamento è obbligatorio.',
        ],
        'currency' => [
            'required' => 'Il campo valuta è obbligatorio.',
        ],
        // 'price' => [
        //     'required' => 'Il campo prezzo è obbligatorio.',
        // ],
        'file' => [
            'required_without_all' => 'Il campo file è obbligatorio se non sono forniti né github_owner né github_repository.',
            'mimes' => 'Il file deve essere un file zip.',
        ],
        'image' => [
            'required_without_all' => 'Il campo immagine è obbligatorio se non sono forniti né github_owner né github_repository.',
            'mimes' => 'L\'immagine deve essere un file PNG.',
        ],
        'github_owner' => [
            'required_without_all' => 'Il campo GitHub owner è obbligatorio se non sono forniti né file né immagine.',
        ],
        'github_repository' => [
            'required_without_all' => 'Il campo GitHub repository è obbligatorio se non sono forniti né file né immagine.',
            'required_if' => 'Il campo GitHub repository è obbligatorio se il tipo è 2.',
        ],
    ],

    'users' => [
        'first_name' => [
            'required' => 'Il campo nome è obbligatorio.',
        ],
        'last_name' => [
            'required' => 'Il campo cognome è obbligatorio.',
        ],
        'company' => [
            'required' => 'Il campo azienda è obbligatorio.',
        ],
        'email' => [
            'required' => 'Il campo email è obbligatorio.',
            'email' => 'L\'email deve essere un indirizzo email valido.',
            'unique' => 'L\'email è già stata presa.',
        ],
        'address' => [
            'required' => 'Il campo indirizzo è obbligatorio.',
        ],
        'mobile' => [
            'required' => 'Il campo mobile è obbligatorio.',
        ],
        'country' => [
            'required' => 'Il campo paese è obbligatorio.',
            'exists' => 'Il paese selezionato non è valido.',
        ],
        'state' => [
            'required_if' => 'Il campo stato è obbligatorio quando il paese è l\'India.',
        ],
        'timezone_id' => [
            'required' => 'Il campo fuso orario è obbligatorio.',
        ],
        'user_name' => [
            'required' => 'Il campo nome utente è obbligatorio.',
            'unique' => 'Il nome utente è già stato preso.',
        ],
        'zip' => [
            'regex' => 'Il campo stato è obbligatorio quando il paese è l\'India.',
        ],
    ],

    'profile_form' => [
        'first_name' => [
            'required' => 'Il nome è obbligatorio.',
            'min' => 'Il nome deve contenere almeno :min caratteri.',
            'max' => 'Il nome non può superare i :max caratteri.',
        ],
        'last_name' => [
            'required' => 'Il cognome è obbligatorio.',
            'max' => 'Il cognome non può superare i :max caratteri.',
        ],
        'company' => [
            'required' => 'Il nome dell\'azienda è obbligatorio.',
            'max' => 'Il nome dell\'azienda non può superare i :max caratteri.',
        ],
        'email' => [
            'required' => 'L\'email è obbligatoria.',
            'email' => 'Inserisci un indirizzo email valido.',
            'unique' => 'L\'indirizzo email è già stato preso. Per favore scegli un\'altra email.',
        ],
        'mobile' => [
            'required' => 'Il numero di cellulare è obbligatorio.',
            'regex' => 'Inserisci un numero di cellulare valido.',
            'min' => 'Il numero di cellulare deve contenere almeno :min caratteri.',
            'max' => 'Il numero di cellulare non può superare i :max caratteri.',
        ],
        'address' => [
            'required' => 'L\'indirizzo è obbligatorio.',
        ],
        'user_name' => [
            'required' => 'Il nome utente è obbligatorio.',
            'unique' => 'Questo nome utente è già stato preso.',
        ],
        'timezone_id' => [
            'required' => 'Il fuso orario è obbligatorio.',
        ],
        'country' => [
            'required' => 'Il paese è obbligatorio.',
            'exists' => 'Il paese selezionato non è valido.',
        ],
        'state' => [
            'required_if' => 'Il campo stato è obbligatorio quando il paese è l\'India.',
        ],
        'old_password' => [
            'required' => 'La vecchia password è obbligatoria.',
            'min' => 'La vecchia password deve contenere almeno :min caratteri.',
        ],
        'new_password' => [
            'required' => 'La nuova password è obbligatoria.',
            'different' => 'La nuova password deve essere diversa dalla vecchia password.',
        ],
        'confirm_password' => [
            'required' => 'La conferma della password è obbligatoria.',
            'same' => 'La conferma della password deve corrispondere alla nuova password.',
        ],
        'terms' => [
            'required' => 'Devi accettare i termini.',
        ],
        'password' => [
            'required' => 'La password è obbligatoria.',
        ],
        'password_confirmation' => [
            'required' => 'La conferma della password è obbligatoria.',
            'same' => 'Le password non corrispondono.',
        ],
        'mobile_code' => [
            'required' => 'Inserisci il prefisso del paese (mobile)',
        ],
    ],
    //Modulo Fattura
    'invoice' => [
        'user' => [
            'required' => 'Il campo clienti è obbligatorio.',
        ],
        'date' => [
            'required' => 'Il campo data è obbligatorio.',
            'date' => 'La data deve essere una data valida.',
        ],
        'domain' => [
            'regex' => 'Il formato del dominio non è valido.',
        ],
        'plan' => [
            'required_if' => 'Il campo abbonamento è obbligatorio.',
        ],
        'price' => [
            'required' => 'Il campo prezzo è obbligatorio.',
        ],
        'product' => [
            'required' => 'Il campo prodotto è obbligatorio.',
        ],
    ],

    //Modulo Licenza Localizzata
    'domain_form' => [
        'domain' => [
            'required' => 'Il campo dominio è obbligatorio.',
            'url' => 'Il dominio deve essere un URL valido.',
        ],
    ],

    //Modulo Rinnovo Prodotto
    'product_renewal' => [
        'domain' => [
            'required' => 'Il campo dominio è obbligatorio.',
            'no_http' => 'Il dominio non deve contenere "http" o "https".',
        ],
    ],

    //Modulo Lingua
    'language' => [
        'required' => 'Il campo lingua è obbligatorio.',
        'invalid' => 'La lingua selezionata non è valida.',
    ],

    //Modulo Aggiornamento Percorso di Memorizzazione
    'storage_path' => [
        'disk' => [
            'required' => 'Il campo disco di memorizzazione è obbligatorio.',
            'string' => 'Il disco deve essere una stringa.',
        ],
        'path' => [
            'string' => 'Il percorso deve essere una stringa.',
            'nullable' => 'Il campo percorso è facoltativo.',
        ],
    ],

    //Modulo Validazione Codice Segreto
    'validate_secret' => [
        'totp' => [
            'required' => 'Inserisci il codice.',
            'digits' => 'Inserisci un codice valido di 6 cifre.',
        ],
    ],

    //Modulo Verifica OTP
    'verify_email' => [
        'required' => 'Il campo email è obbligatorio.',
        'email' => 'L\'email deve essere un indirizzo email valido.',
        'verify_email' => 'La verifica dell\'email è fallita.',
    ],

    'verify_country_code' => [
        'required' => 'Il campo prefisso paese è obbligatorio.',
        'numeric' => 'Il prefisso paese deve essere un numero valido.',
        'verify_country_code' => 'La verifica del prefisso paese è fallita.',
    ],

    'verify_number' => [
        'required' => 'Il numero è obbligatorio.',
        'numeric' => 'Il numero deve essere un numero valido.',
        'verify_number' => 'La verifica del numero è fallita.',
    ],

    'password_otp' => [
        'required' => 'Il campo password è obbligatorio.',
        'password' => 'La password è errata.',
        'invalid' => 'Password non valida.',
    ],

    //Controller di Autenticazione
    'auth_controller' => [
        'name_required' => 'Il nome è obbligatorio.',
        'name_max' => 'Il nome non può essere più lungo di 255 caratteri.',

        'email_required' => 'L\'email è obbligatoria.',
        'email_email' => 'Inserisci un indirizzo email valido.',
        'email_max' => 'L\'email non può essere più lunga di 255 caratteri.',
        'email_unique' => 'Questa email è già registrata.',

        'password_required' => 'La password è obbligatoria.',
        'password_confirmed' => 'La conferma della password non corrisponde.',
        'password_min' => 'La password deve contenere almeno 6 caratteri.',
    ],

    'resend_otp' => [
        'eid_required' => 'Il campo EID è obbligatorio.',
        'eid_string' => 'L\'EID deve essere una stringa.',
        'type_required' => 'Il campo tipo è obbligatorio.',
        'type_string' => 'Il tipo deve essere una stringa.',
        'type_in' => 'Il tipo selezionato non è valido.',
    ],

    'verify_otp' => [
        'eid_required' => 'Il campo ID dipendente è obbligatorio.',
        'eid_string' => 'L\'ID dipendente deve essere una stringa.',
        'otp_required' => 'Il campo OTP è obbligatorio.',
        'otp_size' => 'L\'OTP deve essere di esattamente 6 caratteri.',
        'recaptcha_required' => 'Completa il CAPTCHA.',
        'recaptcha_size' => 'La risposta al CAPTCHA non è valida.',
    ],

    'company_validation' => [
        'company_required' => 'Il nome dell\'azienda è obbligatorio.',
        'company_string' => 'Il nome dell\'azienda deve essere un testo.',
        'address_required' => 'Il campo indirizzo è obbligatorio.',
        'address_string' => 'L\'indirizzo deve essere un testo.',
    ],

    'token_validation' => [
        'token_required' => 'Il token è obbligatorio.',
        'password_required' => 'Il campo password è obbligatorio.',
        'password_confirmed' => 'La conferma della password non corrisponde.',
    ],

    'custom_email' => [
        'required' => 'Il campo email è obbligatorio.',
        'email' => 'Inserisci un indirizzo email valido.',
        'exists' => 'Questa email non è registrata con noi.',
    ],

    'newsletterEmail' => [
        'required' => 'Il campo email della newsletter è obbligatorio.',
        'email' => 'Inserisci un indirizzo email valido per la newsletter.',
    ],

    'widget' => [
        'name_required' => 'Il nome è obbligatorio.',
        'name_max' => 'Il nome non può essere più lungo di 50 caratteri.',
        'publish_required' => 'Il campo stato di pubblicazione è obbligatorio.',
        'type_required' => 'Il campo tipo è obbligatorio.',
        'type_unique' => 'Questo tipo esiste già.',
    ],

    'payment' => [
        'payment_date_required' => 'La data di pagamento è obbligatoria.',
        'payment_method_required' => 'Il metodo di pagamento è obbligatorio.',
        'amount_required' => 'L\'importo è obbligatorio.',
    ],

    'custom_date' => [
        'date_required' => 'Il campo data è obbligatorio.',
        'total_required' => 'Il campo totale è obbligatorio.',
        'status_required' => 'Il campo stato è obbligatorio.',
    ],

    'plan_renewal' => [
        'plan_required' => 'Il campo piano è obbligatorio.',
        'payment_method_required' => 'Il campo metodo di pagamento è obbligatorio.',
        'cost_required' => 'Il campo costo è obbligatorio.',
        'code_not_valid' => 'Il codice promozionale non è valido.',
    ],

    'rate' => [
        'required' => 'Il campo tasso è obbligatorio.',
        'numeric' => 'Il tasso deve essere un numero.',
    ],

    'product_validate' => [
        'producttitle_required' => 'Il titolo del prodotto è richiesto.',
        'version_required' => 'La versione è richiesta.',
        'filename_required' => 'Carica un file.',
        'dependencies_required' => 'Il campo dipendenze è richiesto.',
    ],
    'product_sku_unique' => 'Lo SKU del prodotto deve essere unico.',
    'product_name_unique' => 'Il nome deve essere unico.',
    'product_show_agent_required' => 'Seleziona la tua preferenza per la pagina del carrello.',
    'product_controller' => [
        'name_required' => 'Il nome del prodotto è richiesto.',
        'name_unique' => 'Il nome deve essere unico.',
        'type_required' => 'Il tipo di prodotto è richiesto.',
        'description_required' => 'La descrizione del prodotto è richiesta.',
        'product_description_required' => 'La descrizione dettagliata del prodotto è richiesta.',
        'image_mimes' => 'L\'immagine deve essere un file di tipo: jpeg, png, jpg.',
        'image_max' => 'L\'immagine non può essere più grande di 2048 kilobyte.',
        'product_sku_required' => 'Lo SKU del prodotto è richiesto.',
        'group_required' => 'Il gruppo di prodotto è richiesto.',
        'show_agent_required' => 'Seleziona la tua preferenza per la pagina del carrello.',
    ],
    'current_domain_required' => 'Il dominio corrente è richiesto.',
    'new_domain_required' => 'Il nuovo dominio è richiesto.',
    'special_characters_not_allowed' => 'I caratteri speciali non sono consentiti nel nome del dominio.',
    'orderno_required' => 'Il numero dell\'ordine è richiesto.',
    'cloud_central_domain_required' => 'Il dominio centrale del cloud è richiesto.',
    'cloud_cname_required' => 'Il CNAME del cloud è richiesto.',
    'cloud_tenant' => [
        'cloud_top_message_required' => 'Il messaggio principale del cloud è richiesto.',
        'cloud_label_field_required' => 'Il campo etichetta del cloud è richiesto.',
        'cloud_label_radio_required' => 'Il radio etichetta del cloud è richiesto.',
        'cloud_product_required' => 'Il prodotto cloud è richiesto.',
        'cloud_free_plan_required' => 'Il piano gratuito del cloud è richiesto.',
        'cloud_product_key_required' => 'La chiave del prodotto cloud è richiesta.',
    ],
    'reg_till_after' => 'La data di registrazione fino deve essere dopo la data di registrazione da.',
    'extend_product' => [
        'title_required' => 'Il campo del titolo è richiesto.',
        'version_required' => 'Il campo della versione è richiesto.',
        'dependencies_required' => 'Il campo delle dipendenze è richiesto.',
    ],
    'please_enter_recovery_code' => 'Per favore inserisci il codice di recupero.',
    'social_login' => [
        'client_id_required' => 'Il Client ID è richiesto per Google, Github o Linkedin.',
        'client_secret_required' => 'Il Client Secret è richiesto per Google, Github o Linkedin.',
        'api_key_required' => 'La chiave API è richiesta per Twitter.',
        'api_secret_required' => 'Il segreto API è richiesto per Twitter.',
        'redirect_url_required' => 'L\'URL di reindirizzamento è richiesto.',
    ],
    'thirdparty_api' => [
        'app_name_required' => 'Il nome dell\'app è richiesto.',
        'app_key_required' => 'La chiave dell\'app è richiesta.',
        'app_key_size' => 'La chiave dell\'app deve essere esattamente di 32 caratteri.',
        'app_secret_required' => 'Il segreto dell\'app è richiesto.',
    ],
    'plan_request' => [
        'name_required' => 'Il campo nome è obbligatorio',
        'product_quant_req' => 'Il campo quantità prodotto è obbligatorio quando il numero di agenti non è presente.',
        'no_agent_req' => 'Il campo numero di agenti è obbligatorio quando la quantità prodotto non è presente.',
        'pro_req' => 'Il campo prodotto è obbligatorio',
        'offer_price' => 'Il prezzo dell\'offerta non deve essere superiore a 100',
    ],
    'razorpay_val' => [
        'business_required' => 'Il campo azienda è obbligatorio.',
        'cmd_required' => 'Il campo comando è obbligatorio.',
        'paypal_url_required' => 'L\'URL di PayPal è obbligatorio.',
        'paypal_url_invalid' => 'L\'URL di PayPal deve essere un URL valido.',
        'success_url_invalid' => 'L\'URL di successo deve essere un URL valido.',
        'cancel_url_invalid' => 'L\'URL di annullamento deve essere un URL valido.',
        'notify_url_invalid' => 'L\'URL di notifica deve essere un URL valido.',
        'currencies_required' => 'Il campo valute è obbligatorio.',
    ],

];
