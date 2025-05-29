<?php

return [

    'accepted' => ':attribute må godtas.',
    'accepted_if' => ':attribute må godtas når :other er :value.',
    'active_url' => ':attribute er ikke en gyldig URL.',
    'after' => ':attribute må være en dato etter :date.',
    'after_or_equal' => ':attribute må være en dato etter eller lik :date.',
    'alpha' => ':attribute må bare inneholde bokstaver.',
    'alpha_dash' => ':attribute må bare inneholde bokstaver, tall, bindestreker og understreker.',
    'alpha_num' => ':attribute må bare inneholde bokstaver og tall.',
    'array' => ':attribute må være et array.',
    'before' => ':attribute må være en dato før :date.',
    'before_or_equal' => ':attribute må være en dato før eller lik :date.',
    'between' => [
        'array' => ':attribute må ha mellom :min og :max elementer.',
        'file' => ':attribute må være mellom :min og :max kilobyte.',
        'numeric' => ':attribute må være mellom :min og :max.',
        'string' => ':attribute må være mellom :min og :max tegn.',
    ],
    'boolean' => ':attribute-feltet må være sann eller falsk.',
    'confirmed' => ':attribute bekreftelsen stemmer ikke.',
    'current_password' => 'Passordet er feil.',
    'date' => ':attribute er ikke en gyldig dato.',
    'date_equals' => ':attribute må være en dato lik :date.',
    'date_format' => ':attribute samsvarer ikke med formatet :format.',
    'declined' => ':attribute må avslås.',
    'declined_if' => ':attribute må avslås når :other er :value.',
    'different' => ':attribute og :other må være forskjellige.',
    'digits' => ':attribute må være :digits sifre.',
    'digits_between' => ':attribute må være mellom :min og :max sifre.',
    'dimensions' => ':attribute har ugyldige bildedimensjoner.',
    'distinct' => ':attribute-feltet har en duplisert verdi.',
    'doesnt_start_with' => ':attribute kan ikke begynne med ett av følgende: :values.',
    'email' => ':attribute må være en gyldig e-postadresse.',
    'ends_with' => ':attribute må slutte med ett av følgende: :values.',
    'enum' => ':attribute som er valgt, er ugyldig.',
    'exists' => ':attribute som er valgt, er ugyldig.',
    'file' => ':attribute må være en fil.',
    'filled' => ':attribute-feltet må ha en verdi.',
    'gt' => [
        'array' => ':attribute må ha mer enn :value elementer.',
        'file' => ':attribute må være større enn :value kilobyte.',
        'numeric' => ':attribute må være større enn :value.',
        'string' => ':attribute må være større enn :value tegn.',
    ],
    'gte' => [
        'array' => ':attribute må ha :value elementer eller flere.',
        'file' => ':attribute må være større enn eller lik :value kilobyte.',
        'numeric' => ':attribute må være større enn eller lik :value.',
        'string' => ':attribute må være større enn eller lik :value tegn.',
    ],
    'image' => ':attribute må være et bilde.',
    'in' => ':attribute som er valgt, er ugyldig.',
    'in_array' => ':attribute-feltet finnes ikke i :other.',
    'integer' => ':attribute må være et heltall.',
    'ip' => ':attribute må være en gyldig IP-adresse.',
    'ipv4' => ':attribute må være en gyldig IPv4-adresse.',
    'ipv6' => ':attribute må være en gyldig IPv6-adresse.',
    'json' => ':attribute må være en gyldig JSON-streng.',
    'lt' => [
        'array' => ':attribute må ha færre enn :value elementer.',
        'file' => ':attribute må være mindre enn :value kilobyte.',
        'numeric' => ':attribute må være mindre enn :value.',
        'string' => ':attribute må være mindre enn :value tegn.',
    ],
    'lte' => [
        'array' => ':attribute må ikke ha mer enn :value elementer.',
        'file' => ':attribute må være mindre enn eller lik :value kilobyte.',
        'numeric' => ':attribute må være mindre enn eller lik :value.',
        'string' => ':attribute må være mindre enn eller lik :value tegn.',
    ],
    'mac_address' => ':attribute må være en gyldig MAC-adresse.',
    'max' => [
        'array' => ':attribute må ikke ha mer enn :max elementer.',
        'file' => ':attribute må ikke være større enn :max kilobyte.',
        'numeric' => ':attribute må ikke være større enn :max.',
        'string' => ':attribute må ikke være større enn :max tegn.',
    ],
    'mimes' => ':attribute må være en fil av typen: :values.',
    'mimetypes' => ':attribute må være en fil av typen: :values.',
    'min' => [
        'array' => ':attribute må ha minst :min elementer.',
        'file' => ':attribute må være minst :min kilobyte.',
        'numeric' => ':attribute må være minst :min.',
        'string' => ':attribute må være minst :min tegn.',
    ],
    'multiple_of' => ':attribute må være et multiplum av :value.',
    'not_in' => ':attribute som er valgt, er ugyldig.',
    'not_regex' => ':attribute formatet er ugyldig.',
    'numeric' => ':attribute må være et tall.',
    'password' => [
        'letters' => ':attribute må inneholde minst ett bokstav.',
        'mixed' => ':attribute må inneholde minst en stor bokstav og en liten bokstav.',
        'numbers' => ':attribute må inneholde minst ett tall.',
        'symbols' => ':attribute må inneholde minst ett symbol.',
        'uncompromised' => 'Den gitte :attribute har dukket opp i et datalekkasjearkiv. Velg et annet :attribute.',
    ],
    'present' => ':attribute-feltet må være til stede.',
    'prohibited' => ':attribute-feltet er forbudt.',
    'prohibited_if' => ':attribute-feltet er forbudt når :other er :value.',
    'prohibited_unless' => ':attribute-feltet er forbudt med mindre :other er i :values.',
    'prohibits' => ':attribute-feltet forhindrer at :other er til stede.',
    'regex' => ':attribute formatet er ugyldig.',
    'required' => ':attribute-feltet er påkrevd.',
    'required_array_keys' => ':attribute-feltet må inneholde oppføringer for: :values.',
    'required_if' => ':attribute-feltet er påkrevd når :other er :value.',
    'required_unless' => ':attribute-feltet er påkrevd med mindre :other er i :values.',
    'required_with' => ':attribute-feltet er påkrevd når :values er til stede.',
    'required_with_all' => ':attribute-feltet er påkrevd når :values er til stede.',
    'required_without' => ':attribute-feltet er påkrevd når :values ikke er til stede.',
    'required_without_all' => ':attribute-feltet er påkrevd når ingen av :values er til stede.',
    'same' => ':attribute og :other må stemme overens.',
    'size' => [
        'array' => ':attribute må inneholde :size elementer.',
        'file' => ':attribute må være :size kilobyte.',
        'numeric' => ':attribute må være :size.',
        'string' => ':attribute må være :size tegn.',
    ],
    'starts_with' => ':attribute må starte med ett av de følgende: :values.',
    'string' => ':attribute må være en streng.',
    'timezone' => ':attribute må være en gyldig tidssone.',
    'unique' => ':attribute er allerede tatt.',
    'uploaded' => ':attribute feilet under opplastingen.',
    'url' => ':attribute må være en gyldig URL.',
    'uuid' => ':attribute må være et gyldig UUID.',
    'attributes' => [],
    'publish_date_required' => 'Publiseringsdato er påkrevd',
    'price_numeric_value' => 'Prisen må være et numerisk verdi',
    'quantity_integer_value' => 'Mengden må være et heltall',
    'order_has_Expired' => 'Bestillingen har utløpt',
    'expired' => 'Utløpt',
    'eid_required' => 'EID-feltet er påkrevd.',
    'eid_string' => 'EID må være en streng.',
    'otp_required' => 'OTP-feltet er påkrevd.',
    'amt_required' => 'Beløpsfeltet er påkrevd',
    'amt_numeric' => 'Beløpet må være et tall',
    'payment_date_required' => 'Betalingsdato er påkrevd.',
    'payment_method_required' => 'Betalingsmetode er påkrevd.',
    'total_amount_required' => 'Totalbeløp er påkrevd.',
    'total_amount_numeric' => 'Totalbeløp må være et numerisk verdi.',
    'invoice_link_required' => 'Vennligst koble beløpet med minst én faktura.',
    /*
    Request file custom validation messages
    */

    //Common
    'settings_form' => [
        'company' => [
            'required' => 'Firmafeltet er påkrevd.',
        ],
        'website' => [
            'url' => 'Nettstedet må være en gyldig URL.',
        ],
        'phone' => [
            'regex' => 'Telefonnummerformatet er ugyldig.',
        ],
        'address' => [
            'required' => 'Adressefeltet er påkrevd.',
            'max' => 'Adressen kan ikke være lengre enn 300 tegn.',
        ],
        'logo' => [
            'mimes' => 'Logoen må være en PNG-fil.',
        ],
        'driver' => [
            'required' => 'Feltet for sjåfør er påkrevd.',
        ],
        'port' => [
            'integer' => 'Porten må være et heltall.',
        ],
        'email' => [
            'required' => 'E-postfeltet er påkrevd.',
            'email' => 'E-posten må være en gyldig e-postadresse.',
        ],
        'password' => [
            'required' => 'Passordfeltet er påkrevd.',
        ],
        'error_email' => [
            'email' => 'Feil e-post må være en gyldig e-postadresse.',
        ],
    ],

    'settings_forms' => [
        'company' => [
            'required' => 'Firmanavnet er påkrevd.',
            'max' => 'Firmanavnet kan ikke overstige 50 tegn.',
        ],
        'company_email' => [
            'required' => 'Firmaets e-post er påkrevd.',
            'email' => 'Firmaets e-post må være en gyldig e-postadresse.',
        ],
        'title' => [
            'max' => 'Tittelen kan ikke overstige 50 tegn.',
        ],
        'website' => [
            'required' => 'Nettstedets URL er påkrevd.',
            'url' => 'Nettstedet må være en gyldig URL.',
            'regex' => 'Formatet på nettstedet er ugyldig.',
        ],
        'phone' => [
            'required' => 'Telefonnummeret er påkrevd.',
        ],
        'address' => [
            'required' => 'Adressen er påkrevd.',
        ],
        'state' => [
            'required' => 'Statens navn er påkrevd.',
        ],
        'country' => [
            'required' => 'Landet er påkrevd.',
        ],
        'gstin' => [
            'max' => 'GSTIN kan ikke overstige 15 tegn.',
        ],
        'default_currency' => [
            'required' => 'Standardvalutaen er påkrevd.',
        ],
        'admin_logo' => [
            'mimes' => 'Admin-logoen må være en filtype: jpeg, png, jpg.',
            'max' => 'Admin-logoen kan ikke være større enn 2 MB.',
        ],
        'fav_icon' => [
            'mimes' => 'Favorittikonet må være en filtype: jpeg, png, jpg.',
            'max' => 'Favorittikonet kan ikke være større enn 2 MB.',
        ],
        'logo' => [
            'mimes' => 'Logoen må være en filtype: jpeg, png, jpg.',
            'max' => 'Logoen kan ikke være større enn 2 MB.',
        ],
    ],

    'social_media_form' => [
        'name' => [
            'required' => 'Navnfeltet er påkrevd.',
            'unique' => 'Dette navnet eksisterer allerede.',
            'max' => 'Navnet kan ikke være lengre enn 50 tegn.',
        ],
        'link' => [
            'required' => 'Lenkefeltet er påkrevd.',
            'url' => 'Lenken må være en gyldig URL.',
            'regex' => 'Lenkeformat er ugyldig.',
        ],
    ],

    //Email
    'custom' => [
        'password' => [
            'required_if' => 'Passordfeltet er påkrevd for den valgte e-postdriveren.',
        ],
        'port' => [
            'required_if' => 'Portfeltet er påkrevd for SMTP.',
        ],
        'encryption' => [
            'required_if' => 'Krypteringsfeltet er påkrevd for SMTP.',
        ],
        'host' => [
            'required_if' => 'Vertsfeltet er påkrevd for SMTP.',
        ],
        'secret' => [
            'required_if' => 'Hemmelighetfeltet er påkrevd for den valgte e-postdriveren.',
        ],
        'domain' => [
            'required_if' => 'Domene-feltet er påkrevd for Mailgun.',
        ],
        'key' => [
            'required_if' => 'Nøkkelfeltet er påkrevd for SES.',
        ],
        'region' => [
            'required_if' => 'Region-feltet er påkrevd for SES.',
        ],
        'email' => [
            'required_if' => 'E-postfeltet er påkrevd for den valgte e-postdriveren.',
            'required' => 'E-postfeltet er påkrevd.',
            'email' => 'Vennligst oppgi en gyldig e-postadresse.',
            'not_matching' => 'E-postdomenet må matche det nåværende domene.',
        ],
        'driver' => [
            'required' => 'Driver-feltet er påkrevd.',
        ],
    ],

    'customer_form' => [
        'first_name' => [
            'required' => 'Fornavnfeltet er påkrevd.',
        ],
        'last_name' => [
            'required' => 'Etternavnfeltet er påkrevd.',
        ],
        'company' => [
            'required' => 'Firmafeltet er påkrevd.',
        ],
        'mobile' => [
            'regex' => 'Mobilnummerformatet er ugyldig.',
        ],
        'address' => [
            'required' => 'Adressefeltet er påkrevd.',
        ],
        'zip' => [
            'required' => 'Postnummerfeltet er påkrevd.',
            'min' => 'Postnummeret må være minst 5 sifre.',
            'numeric' => 'Postnummeret må være numerisk.',
        ],
        'email' => [
            'required' => 'E-postfeltet er påkrevd.',
            'email' => 'E-posten må være en gyldig e-postadresse.',
            'unique' => 'Denne e-posten er allerede tatt.',
        ],
    ],
    'contact_request' => [
        'conName' => 'Navnfeltet er påkrevd.',
        'email' => 'E-postfeltet er påkrevd.',
        'conmessage' => 'Meldingsfeltet er påkrevd.',
        'Mobile' => 'Mobilfeltet er påkrevd.',
        'country_code' => 'Mobilfeltet er påkrevd.',
        'demoname' => 'Navnfeltet er påkrevd.',
        'demomessage' => 'Meldingsfeltet er påkrevd.',
        'demoemail' => 'E-postfeltet er påkrevd.',
        'congg-recaptcha-response-1.required' => 'Robotverifisering mislyktes. Vennligst prøv igjen.',
        'demo-recaptcha-response-1.required' => 'Robotverifisering mislyktes. Vennligst prøv igjen.',
    ],

    'frontend_pages' => [
        'name' => [
            'required' => 'Navnfeltet er påkrevd.',
            'unique' => 'Dette navnet eksisterer allerede.',
            'max' => 'Navnet kan ikke overstige 20 tegn.',
            'regex' => 'Navnet kan bare inneholde bokstaver og mellomrom.',
        ],
        'publish' => [
            'required' => 'Publiseringsfeltet er påkrevd.',
        ],
        'slug' => [
            'required' => 'Slug-feltet er påkrevd.',
        ],
        'url' => [
            'required' => 'URL-feltet er påkrevd.',
            'url' => 'URL-en må være en gyldig lenke.',
            'regex' => 'URL-formatet er ugyldig.',
        ],
        'content' => [
            'required' => 'Innholdsfeltet er påkrevd.',
        ],
        'created_at' => [
            'required' => 'Opprettelsesfeltet er påkrevd.',
        ],
    ],

    'order_form' => [
        'client' => [
            'required' => 'Kunde-feltet er påkrevd.',
        ],
        'payment_method' => [
            'required' => 'Betalingsmetode-feltet er påkrevd.',
        ],
        'promotion_code' => [
            'required' => 'Feltet for kampanjekode er påkrevd.',
        ],
        'order_status' => [
            'required' => 'Bestillingsstatus-feltet er påkrevd.',
        ],
        'product' => [
            'required' => 'Produkt-feltet er påkrevd.',
        ],
        'subscription' => [
            'required' => 'Abonnements-feltet er påkrevd.',
        ],
        'price_override' => [
            'numeric' => 'Prisoverstyringen må være et tall.',
        ],
        'qty' => [
            'integer' => 'Antallet må være et heltall.',
        ],
    ],

    'coupon_form' => [
        'code' => [
            'required' => 'Kampanjekode-feltet er påkrevd.',
            'string' => 'Kampanjekoden må være en streng.',
            'max' => 'Kampanjekoden kan ikke overstige 255 tegn.',
        ],
        'type' => [
            'required' => 'Type-feltet er påkrevd.',
            'in' => 'Ugyldig type. Tillatte verdier er: prosentandel, annet_type.',
        ],
        'applied' => [
            'required' => 'Feltet for brukt på produkt er påkrevd.',
            'date' => 'Feltet for brukt på produkt må være en gyldig dato.',
        ],
        'uses' => [
            'required' => 'Feltet for antall bruk er påkrevd.',
            'numeric' => 'Feltet for antall bruk må være et tall.',
            'min' => 'Feltet for antall bruk må være minst :min.',
        ],
        'start' => [
            'required' => 'Start-feltet er påkrevd.',
            'date' => 'Start-feltet må være en gyldig dato.',
        ],
        'expiry' => [
            'required' => 'Utløpsfeltet er påkrevd.',
            'date' => 'Utløpsfeltet må være en gyldig dato.',
            'after' => 'Utløpsdatoen må være etter startdatoen.',
        ],
        'value' => [
            'required' => 'Feltet for rabattverdi er påkrevd.',
            'numeric' => 'Feltet for rabattverdi må være et tall.',
            'between' => 'Feltet for rabattverdi må være mellom :min og :max hvis typen er prosentandel.',
        ],
    ],

    'tax_form' => [
        'name' => [
            'required' => 'Navnfeltet er påkrevd.',
        ],
        'rate' => [
            'required' => 'Satsfeltet er påkrevd.',
            'numeric' => 'Satsen må være et tall.',
        ],
        'level' => [
            'required' => 'Nivåfeltet er påkrevd.',
            'integer' => 'Nivået må være et heltall.',
        ],
        'country' => [
            'required' => 'Land-feltet er påkrevd.',
            // 'exists' => 'Det valgte landet er ugyldig.',
        ],
        'state' => [
            'required' => 'Stat-feltet er påkrevd.',
            // 'exists' => 'Det valgte staten er ugyldig.',
        ],
    ],
    //Product
    'subscription_form' => [
        'name' => [
            'required' => 'Navnfeltet er påkrevd.',
        ],
        'subscription' => [
            'required' => 'Abonnementsfeltet er påkrevd.',
        ],
        'regular_price' => [
            'required' => 'Feltet for vanlig pris er påkrevd.',
            'numeric' => 'Den vanlige prisen må være et tall.',
        ],
        'selling_price' => [
            'required' => 'Feltet for salgspris er påkrevd.',
            'numeric' => 'Salgsprisen må være et tall.',
        ],
        'products' => [
            'required' => 'Produktfeltet er påkrevd.',
        ],
    ],

    'bundle' => [
        'name' => [
            'required' => 'Navnfeltet er påkrevd.',
        ],
        'items' => [
            'required' => 'Hvert element er påkrevd.',
        ],
    ],

    'group' => [
        'name' => [
            'required' => 'Navnet er påkrevd.',
        ],
        'features' => [
            'name' => [
                'required' => 'Alle funksjonsfelt er påkrevd.',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => 'Prisen er påkrevd.',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => 'Verdien er påkrevd.',
            ],
        ],
        'type' => [
            'required_with' => 'Type-feltet er påkrevd.',
        ],
        'title' => [
            'required_with' => 'Tittel-feltet er påkrevd.',
        ],
    ],

    'product' => [
        'name' => [
            'required' => 'Navnfeltet er påkrevd.',
        ],
        'type' => [
            'required' => 'Type-feltet er påkrevd.',
        ],
        'group' => [
            'required' => 'Gruppefeltet er påkrevd.',
        ],
        'subscription' => [
            'required' => 'Abonnementsfeltet er påkrevd.',
        ],
        'currency' => [
            'required' => 'Valutafeltet er påkrevd.',
        ],
        // 'price' => [
        //     'required' => 'Feltet for pris er påkrevd.',
        // ],
        'file' => [
            'required_without_all' => 'Fil-feltet er påkrevd hvis ingen av github_owner eller github_repository er oppgitt.',
            'mimes' => 'Filen må være en zip-fil.',
        ],
        'image' => [
            'required_without_all' => 'Bildet-feltet er påkrevd hvis ingen av github_owner eller github_repository er oppgitt.',
            'mimes' => 'Bildet må være en PNG-fil.',
        ],
        'github_owner' => [
            'required_without_all' => 'GitHub-eier-feltet er påkrevd hvis ingen av fil eller bilde er oppgitt.',
        ],
        'github_repository' => [
            'required_without_all' => 'GitHub-repositoriefeltet er påkrevd hvis ingen av fil eller bilde er oppgitt.',
            'required_if' => 'GitHub-repositoriefeltet er påkrevd hvis typen er 2.',
        ],
    ],

    //User
    'users' => [
        'first_name' => [
            'required' => 'Fornavn-feltet er påkrevd.',
        ],
        'last_name' => [
            'required' => 'Etternavn-feltet er påkrevd.',
        ],
        'company' => [
            'required' => 'Firma-feltet er påkrevd.',
        ],
        'email' => [
            'required' => 'E-postfeltet er påkrevd.',
            'email' => 'E-posten må være en gyldig e-postadresse.',
            'unique' => 'E-posten er allerede tatt.',
        ],
        'address' => [
            'required' => 'Adresse-feltet er påkrevd.',
        ],
        'mobile' => [
            'required' => 'Mobilfeltet er påkrevd.',
        ],
        'country' => [
            'required' => 'Land-feltet er påkrevd.',
            'exists' => 'Det valgte landet er ugyldig.',
        ],
        'state' => [
            'required_if' => 'Stat-feltet er påkrevd når landet er India.',
        ],
        'timezone_id' => [
            'required' => 'Tidszone-feltet er påkrevd.',
        ],
        'user_name' => [
            'required' => 'Brukernavn-feltet er påkrevd.',
            'unique' => 'Brukernavnet er allerede tatt.',
        ],
        'zip' => [
            'regex' => 'Postnummer-feltet er påkrevd når landet er India.',
        ],
    ],

    'profile_form' => [
        'first_name' => [
            'required' => 'Fornavn er påkrevd.',
            'min' => 'Fornavn må være minst :min tegn.',
            'max' => 'Fornavn kan ikke være mer enn :max tegn.',
        ],
        'last_name' => [
            'required' => 'Etternavn er påkrevd.',
            'max' => 'Etternavn kan ikke være mer enn :max tegn.',
        ],
        'company' => [
            'required' => 'Firmanavn er påkrevd.',
            'max' => 'Firmanavn kan ikke være mer enn :max tegn.',
        ],
        'email' => [
            'required' => 'E-post er påkrevd.',
            'email' => 'Vennligst skriv inn en gyldig e-postadresse.',
            'unique' => 'E-postadressen er allerede tatt. Vennligst velg en annen e-post.',
        ],
        'mobile' => [
            'required' => 'Mobilnummer er påkrevd.',
            'regex' => 'Vennligst skriv inn et gyldig mobilnummer.',
            'min' => 'Mobilnummeret må være minst :min tegn.',
            'max' => 'Mobilnummeret kan ikke være mer enn :max tegn.',
        ],
        'address' => [
            'required' => 'Adresse er påkrevd.',
        ],
        'user_name' => [
            'required' => 'Brukernavn er påkrevd.',
            'unique' => 'Dette brukernavnet er allerede tatt.',
        ],
        'timezone_id' => [
            'required' => 'Tidszone er påkrevd.',
        ],
        'country' => [
            'required' => 'Land er påkrevd.',
            'exists' => 'Valgt land er ugyldig.',
        ],
        'state' => [
            'required_if' => 'Stat-feltet er påkrevd når landet er India.',
        ],
    ],
    'old_password' => [
        'required' => 'Gamle passord er påkrevd.',
        'min' => 'Det gamle passordet må være minst :min tegn.',
    ],
    'new_password' => [
        'required' => 'Nytt passord er påkrevd.',
        'different' => 'Det nye passordet må være forskjellig fra det gamle passordet.',
    ],
    'confirm_password' => [
        'required' => 'Bekreft passord er påkrevd.',
        'same' => 'Bekreft passord må matche nytt passord.',
    ],
    'terms' => [
        'required' => 'Du må godta vilkårene.',
    ],
    'password' => [
        'required' => 'Passord er påkrevd.',
    ],
    'password_confirmation' => [
        'required' => 'Bekreftelse av passord er påkrevd.',
        'same' => 'Passordene samsvarer ikke.',
    ],
    'mobile_code' => [
        'required' => 'Oppgi landskode (mobil).',
    ],

    //Invoice form
    'invoice' => [
        'user' => [
            'required' => 'Klientfeltet er påkrevd.',
        ],
        'date' => [
            'required' => 'Dato-feltet er påkrevd.',
            'date' => 'Datoen må være en gyldig dato.',
        ],
        'domain' => [
            'regex' => 'Domeneformatet er ugyldig.',
        ],
        'plan' => [
            'required_if' => 'Abonnementsfeltet er påkrevd.',
        ],
        'price' => [
            'required' => 'Pris-feltet er påkrevd.',
        ],
        'product' => [
            'required' => 'Produkt-feltet er påkrevd.',
        ],
    ],

    //LocalizedLicense form
    'domain_form' => [
        'domain' => [
            'required' => 'Domene-feltet er påkrevd.',
            'url' => 'Domene må være en gyldig URL.',
        ],
    ],

    //Product Renewal form
    'product_renewal' => [
        'domain' => [
            'required' => 'Domene-feltet er påkrevd.',
            'no_http' => 'Domene må ikke inneholde "http" eller "https".',
        ],
    ],

    //Language form
    'language' => [
        'required' => 'Språk-feltet er påkrevd.',
        'invalid' => 'Det valgte språket er ugyldig.',
    ],

    //UpdateStoragePathRequest form
    'storage_path' => [
        'disk' => [
            'required' => 'Lagringsdisk-feltet er påkrevd.',
            'string' => 'Disken må være en streng.',
        ],
        'path' => [
            'string' => 'Stien må være en streng.',
            'nullable' => 'Sti-feltet er valgfritt.',
        ],
    ],

    //ValidateSecretRequest form
    'validate_secret' => [
        'totp' => [
            'required' => 'Vennligst oppgi kode.',
            'digits' => 'Vennligst oppgi en gyldig 6-sifret kode.',
        ],
    ],

    //VerifyOtp form
    'verify_email' => [
        'required' => 'E-post-feltet er påkrevd.',
        'email' => 'E-posten må være en gyldig e-postadresse.',
        'verify_email' => 'E-postverifiseringen feilet.', // Custom message for verify_email
    ],

    'verify_country_code' => [
        'required' => 'Landskode er påkrevd.',
        'numeric' => 'Landskode må være et gyldig nummer.',
        'verify_country_code' => 'Verifisering av landskode feilet.', // Custom message for verify_country_code
    ],

    'verify_number' => [
        'required' => 'Nummeret er påkrevd.',
        'numeric' => 'Nummeret må være et gyldig nummer.',
        'verify_number' => 'Verifisering av nummeret feilet.', // Custom message for verify_number
    ],

    'password_otp' => [
        'required' => 'Passord-feltet er påkrevd.',
        'password' => 'Passordet er feil.',
        'invalid' => 'Ugyldig passord.',
    ],

    //AuthController file
    'auth_controller' => [
        'name_required' => 'Navn er påkrevd.',
        'name_max' => 'Navn kan ikke være lengre enn 255 tegn.',

        'email_required' => 'E-post er påkrevd.',
        'email_email' => 'Vennligst skriv inn en gyldig e-postadresse.',
        'email_max' => 'E-post kan ikke være lengre enn 255 tegn.',
        'email_unique' => 'Denne e-posten er allerede registrert.',

        'password_required' => 'Passord er påkrevd.',
        'password_confirmed' => 'Passordbekreftelsen samsvarer ikke.',
        'password_min' => 'Passordet må være minst 6 tegn.',
    ],

    'resend_otp' => [
        'eid_required' => 'EID-feltet er påkrevd.',
        'eid_string' => 'EID må være en streng.',
        'type_required' => 'Type-feltet er påkrevd.',
        'type_string' => 'Type må være en streng.',
        'type_in' => 'Den valgte typen er ugyldig.',
    ],

    'verify_otp' => [
        'eid_required' => 'Ansatt-ID er påkrevd.',
        'eid_string' => 'Ansatt-ID må være en streng.',
        'otp_required' => 'OTP er påkrevd.',
        'otp_size' => 'OTP må være nøyaktig 6 tegn.',
        'recaptcha_required' => 'Vennligst fullfør CAPTCHA.',
        'recaptcha_size' => 'CAPTCHA-responsen er ugyldig.',
    ],

    'company_validation' => [
        'company_required' => 'Firmanavn er påkrevd.',
        'company_string' => 'Firma må være tekst.',
        'address_required' => 'Adresse er påkrevd.',
        'address_string' => 'Adresse må være tekst.',
    ],

    'token_validation' => [
        'token_required' => 'Token er påkrevd.',
        'password_required' => 'Passord-feltet er påkrevd.',
        'password_confirmed' => 'Passordbekreftelsen samsvarer ikke.',
    ],

    'custom_email' => [
        'required' => 'E-post-feltet er påkrevd.',
        'email' => 'Vennligst skriv inn en gyldig e-postadresse.',
        'exists' => 'Denne e-posten er ikke registrert hos oss.',
    ],

    'newsletterEmail' => [
        'required' => 'Nyhetsbrev-e-post er påkrevd.',
        'email' => 'Vennligst skriv inn en gyldig e-postadresse for nyhetsbrevet.',
    ],

    'widget' => [
        'name_required' => 'Navn er påkrevd.',
        'name_max' => 'Navn kan ikke være lengre enn 50 tegn.',
        'publish_required' => 'Publiseringsstatus er påkrevd.',
        'type_required' => 'Type er påkrevd.',
        'type_unique' => 'Denne typen finnes allerede.',
    ],

    'payment' => [
        'payment_date_required' => 'Betalingsdato er påkrevd.',
        'payment_method_required' => 'Betalingsmetode er påkrevd.',
        'amount_required' => 'Beløp er påkrevd.',
    ],

    'custom_date' => [
        'date_required' => 'Dato-feltet er påkrevd.',
        'total_required' => 'Totalt-feltet er påkrevd.',
        'status_required' => 'Status-feltet er påkrevd.',
    ],

    'plan_renewal' => [
        'plan_required' => 'Abonnement-feltet er påkrevd.',
        'payment_method_required' => 'Betalingsmetode-feltet er påkrevd.',
        'cost_required' => 'Kostnad-feltet er påkrevd.',
        'code_not_valid' => 'Rabattkoden er ugyldig.',
    ],

    'rate' => [
        'required' => 'Rente er påkrevd.',
        'numeric' => 'Rente må være et tall.',
    ],

    'product_validate' => [
        'producttitle_required' => 'Produkttittel er påkrevd.',
        'version_required' => 'Versjon er påkrevd.',
        'filename_required' => 'Vennligst last opp en fil.',
        'dependencies_required' => 'Avhengigheter-feltet er påkrevd.',
    ],
    'product_sku_unique' => 'Produktets SKU må være unikt.',
    'product_name_unique' => 'Navnet må være unikt.',
    'product_show_agent_required' => 'Velg preferanse for handlekurvside.',
    'product_controller' => [
        'name_required' => 'Produktnavnet er påkrevd.',
        'name_unique' => 'Navnet må være unikt.',
        'type_required' => 'Produkttypen er påkrevd.',
        'description_required' => 'Produktbeskrivelsen er påkrevd.',
        'product_description_required' => 'Detaljert produktbeskrivelse er påkrevd.',
        'image_mimes' => 'Bildet må være en fil av typen: jpeg, png, jpg.',
        'image_max' => 'Bildet må ikke være større enn 2048 kilobyte.',
        'product_sku_required' => 'Produktets SKU er påkrevd.',
        'group_required' => 'Produktgruppen er påkrevd.',
        'show_agent_required' => 'Velg preferanse for handlekurvside.',
    ],
    'current_domain_required' => 'Nåværende domene er påkrevd.',
    'new_domain_required' => 'Nytt domene er påkrevd.',
    'special_characters_not_allowed' => 'Spesialtegn er ikke tillatt i domenenavn.',
    'orderno_required' => 'Ordrenummer er påkrevd.',
    'cloud_central_domain_required' => 'Cloud sentraldomene er påkrevd.',
    'cloud_cname_required' => 'Cloud CNAME er påkrevd.',
    'cloud_tenant' => [
        'cloud_top_message_required' => 'Melding på toppen er påkrevd.',
        'cloud_label_field_required' => 'Etikettfeltet er påkrevd.',
        'cloud_label_radio_required' => 'Radioknappetiketten er påkrevd.',
        'cloud_product_required' => 'Cloud-produkt er påkrevd.',
        'cloud_free_plan_required' => 'Gratisplan for Cloud er påkrevd.',
        'cloud_product_key_required' => 'Produktnøkkel for Cloud er påkrevd.',
    ],
    'reg_till_after' => 'Registrering til-dato må være etter registrering fra-dato.',
    'extend_product' => [
        'title_required' => 'Tittelfeltet er påkrevd.',
        'version_required' => 'Versjonsfeltet er påkrevd.',
        'dependencies_required' => 'Avhengighetsfeltet er påkrevd.',
    ],
    'please_enter_recovery_code' => 'Vennligst skriv inn gjenopprettingskode.',
    'social_login' => [
        'client_id_required' => 'Klient-ID er påkrevd for Google, Github eller LinkedIn.',
        'client_secret_required' => 'Klienthemmelighet er påkrevd for Google, Github eller LinkedIn.',
        'api_key_required' => 'API-nøkkel er påkrevd for Twitter.',
        'api_secret_required' => 'API-hemmelighet er påkrevd for Twitter.',
        'redirect_url_required' => 'Omdirigerings-URL er påkrevd.',
    ],
    'thirdparty_api' => [
        'app_name_required' => 'Appnavn er påkrevd.',
        'app_key_required' => 'Appnøkkel er påkrevd.',
        'app_key_size' => 'Appnøkkelen må være nøyaktig 32 tegn.',
        'app_secret_required' => 'Apphemmelighet er påkrevd.',
    ],
    'plan_request' => [
        'name_required' => 'Feltet navn er påkrevd',
        'product_quant_req' => 'Produktmengdefeltet er påkrevd når antall agenter ikke er til stede.',
        'no_agent_req' => 'Antall agenter-feltet er påkrevd når produktmengde ikke er til stede.',
        'pro_req' => 'Produktfeltet er påkrevd',
        'offer_price' => 'Tilbudspriser må ikke være større enn 100',
    ],

];
