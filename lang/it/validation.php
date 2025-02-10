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

    'custom' => [
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

];
