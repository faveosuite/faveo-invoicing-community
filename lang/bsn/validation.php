<?php

return [
    'accepted' => ':attribute mora biti prihvaćen.',
    'accepted_if' => ':attribute mora biti prihvaćen kada je :other :value.',
    'active_url' => ':attribute nije validan URL.',
    'after' => ':attribute mora biti datum poslije :date.',
    'after_or_equal' => ':attribute mora biti datum poslije ili jednak :date.',
    'alpha' => ':attribute smije sadržavati samo slova.',
    'alpha_dash' => ':attribute smije sadržavati samo slova, brojeve, crte i donje crte.',
    'alpha_num' => ':attribute smije sadržavati samo slova i brojeve.',
    'array' => ':attribute mora biti niz.',
    'before' => ':attribute mora biti datum prije :date.',
    'before_or_equal' => ':attribute mora biti datum prije ili jednak :date.',
    'between' => [
        'array' => ':attribute mora imati između :min i :max stavki.',
        'file' => ':attribute mora biti između :min i :max kilobajta.',
        'numeric' => ':attribute mora biti između :min i :max.',
        'string' => ':attribute mora biti između :min i :max karaktera.',
    ],
    'boolean' => ':attribute polje mora biti true ili false.',
    'confirmed' => 'Potvrda za :attribute se ne poklapa.',
    'current_password' => 'Lozinka je pogrešna.',
    'date' => ':attribute nije validan datum.',
    'date_equals' => ':attribute mora biti datum jednak :date.',
    'date_format' => ':attribute se ne poklapa sa formatom :format.',
    'decimal' => 'Polje :attribute mora imati :decimal decimalnih mjesta.',
    'declined' => ':attribute mora biti odbijen.',
    'declined_if' => ':attribute mora biti odbijen kada je :other :value.',
    'different' => ':attribute i :other moraju biti različiti.',
    'digits' => ':attribute mora biti :digits cifara.',
    'digits_between' => ':attribute mora biti između :min i :max cifara.',
    'dimensions' => ':attribute ima nevalidne dimenzije slike.',
    'distinct' => ':attribute polje ima dupliranu vrijednost.',
    'doesnt_start_with' => ':attribute ne može početi sa jednim od sljedećih: :values.',
    'email' => ':attribute mora biti validna email adresa.',
    'ends_with' => ':attribute mora završiti sa jednim od sljedećih: :values.',
    'enum' => 'Odabrani :attribute je nevalidan.',
    'exists' => 'Odabrani :attribute je nevalidan.',
    'file' => ':attribute mora biti fajl.',
    'filled' => ':attribute polje mora imati vrijednost.',
    'gt' => [
        'array' => ':attribute mora imati više od :value stavki.',
        'file' => ':attribute mora biti veći od :value kilobajta.',
        'numeric' => ':attribute mora biti veći od :value.',
        'string' => ':attribute mora biti veći od :value karaktera.',
    ],
    'gte' => [
        'array' => ':attribute mora imati :value stavki ili više.',
        'file' => ':attribute mora biti veći ili jednak :value kilobajta.',
        'numeric' => ':attribute mora biti veći ili jednak :value.',
        'string' => ':attribute mora biti veći ili jednak :value karaktera.',
    ],
    'image' => ':attribute mora biti slika.',
    'in' => 'Odabrani :attribute je nevalidan.',
    'in_array' => ':attribute polje ne postoji u :other.',
    'integer' => ':attribute mora biti cijeli broj.',
    'ip' => ':attribute mora biti validna IP adresa.',
    'ipv4' => ':attribute mora biti validna IPv4 adresa.',
    'ipv6' => ':attribute mora biti validna IPv6 adresa.',
    'json' => ':attribute mora biti validan JSON string.',
    'lt' => [
        'array' => ':attribute mora imati manje od :value stavki.',
        'file' => ':attribute mora biti manji od :value kilobajta.',
        'numeric' => ':attribute mora biti manji od :value.',
        'string' => ':attribute mora biti manji od :value karaktera.',
    ],
    'lte' => [
        'array' => ':attribute ne smije imati više od :value stavki.',
        'file' => ':attribute mora biti manji ili jednak :value kilobajta.',
        'numeric' => ':attribute mora biti manji ili jednak :value.',
        'string' => ':attribute mora biti manji ili jednak :value karaktera.',
    ],
    'mac_address' => ':attribute mora biti validna MAC adresa.',
    'max' => [
        'array' => ':attribute ne smije imati više od :max stavki.',
        'file' => ':attribute ne smije biti veći od :max kilobajta.',
        'numeric' => ':attribute ne smije biti veći od :max.',
        'string' => ':attribute ne smije biti veći od :max karaktera.',
    ],
    'mimes' => ':attribute mora biti fajl tipa: :values.',
    'mimetypes' => ':attribute mora biti fajl tipa: :values.',
    'min' => [
        'array' => ':attribute mora imati najmanje :min stavki.',
        'file' => ':attribute mora biti najmanje :min kilobajta.',
        'numeric' => ':attribute mora biti najmanje :min.',
        'string' => ':attribute mora biti najmanje :min karaktera.',
    ],
    'multiple_of' => ':attribute mora biti višekratnik od :value.',
    'not_in' => 'Odabrani :attribute je nevalidan.',
    'not_regex' => 'Format :attribute je nevalidan.',
    'numeric' => ':attribute mora biti broj.',
    'password' => [
        'letters' => ':attribute mora sadržavati barem jedno slovo.',
        'mixed' => ':attribute mora sadržavati barem jedno veliko i jedno malo slovo.',
        'numbers' => ':attribute mora sadržavati barem jedan broj.',
        'symbols' => ':attribute mora sadržavati barem jedan simbol.',
        'uncompromised' => 'Dati :attribute je pojavio u curenju podataka. Molimo izaberite drugi :attribute.',
    ],
    'present' => ':attribute polje mora biti prisutno.',
    'prohibited' => ':attribute polje je zabranjeno.',
    'prohibited_if' => ':attribute polje je zabranjeno kada je :other :value.',
    'prohibited_unless' => ':attribute polje je zabranjeno osim ako :other nije u :values.',
    'prohibits' => ':attribute polje zabranjuje prisutnost :other.',
    'regex' => 'Format :attribute je nevalidan.',
    'required' => ':attribute polje je obavezno.',
    'required_array_keys' => ':attribute polje mora sadržavati unose za: :values.',
    'required_if' => ':attribute polje je obavezno kada je :other :value.',
    'required_unless' => ':attribute polje je obavezno osim ako :other nije u :values.',
    'required_with' => ':attribute polje je obavezno kada :values je prisutno.',
    'required_with_all' => ':attribute polje je obavezno kada su :values prisutni.',
    'required_without' => ':attribute polje je obavezno kada :values nije prisutno.',
    'required_without_all' => ':attribute polje je obavezno kada nijedno od :values nije prisutno.',
    'same' => ':attribute i :other moraju biti isti.',
    'size' => [
        'array' => ':attribute mora sadržavati :size stavki.',
        'file' => ':attribute mora biti :size kilobajta.',
        'numeric' => ':attribute mora biti :size.',
        'string' => ':attribute mora biti :size karaktera.',
    ],
    'starts_with' => ':attribute mora početi sa jednim od sljedećih: :values.',
    'string' => ':attribute mora biti string.',
    'timezone' => ':attribute mora biti validna vremenska zona.',
    'unique' => ':attribute je već zauzet.',
    'uploaded' => ':attribute nije uspjelo da se otpremi.',
    'url' => ':attribute mora biti validan URL.',
    'uuid' => ':attribute mora biti validan UUID.',
    'custom_dup' => [
        'attribute-name' => [
            'rule-name' => 'prilagođena poruka',
        ],
    ],
    'attributes' => [],
    'publish_date_required' => 'Datum objave je obavezan',
    'price_numeric_value' => 'Cijena mora biti numerička vrijednost',
    'quantity_integer_value' => 'Količina mora biti cijeli broj',
    'order_has_Expired' => 'Narudžba je istekla',
    'expired' => 'Isteklo',
    'eid_required' => 'EID polje je obavezno',
    'eid_string' => 'EID mora biti tekstualna vrijednost',
    'otp_required' => 'OTP polje je obavezno',
    'amt_required' => 'Iznos je obavezan',
    'amt_numeric' => 'Iznos mora biti broj',
    'payment_date_required' => 'Datum plaćanja je obavezan',
    'payment_method_required' => 'Način plaćanja je obavezan',
    'total_amount_required' => 'Ukupan iznos je obavezan',
    'total_amount_numeric' => 'Ukupan iznos mora biti numerička vrijednost',
    'invoice_link_required' => 'Molimo povežite iznos sa barem jednom fakturom',
    'settings_form' => [
        'company' => [
            'required' => 'Polje za naziv kompanije je obavezno.',
        ],
        'website' => [
            'url' => 'Web sajt mora biti validna URL adresa.',
        ],
        'phone' => [
            'regex' => 'Format broja telefona nije validan.',
        ],
        'address' => [
            'required' => 'Polje za adresu je obavezno.',
            'max' => 'Adresa ne smije biti duža od 300 karaktera.',
        ],
        'logo' => [
            'mimes' => 'Logo mora biti PNG fajl.',
        ],
        'driver' => [
            'required' => 'Polje za vozača je obavezno.',
        ],
        'port' => [
            'integer' => 'Port mora biti broj.',
        ],
        'email' => [
            'required' => 'Polje za email je obavezno.',
            'email' => 'Email mora biti validna email adresa.',
        ],
        'password' => [
            'required' => 'Polje za lozinku je obavezno.',
        ],
        'error_email' => [
            'email' => 'Email za greške mora biti validna email adresa.',
        ],
    ],
    'settings_forms' => [
        'company' => [
            'required' => 'Naziv kompanije je obavezan.',
            'max' => 'Naziv kompanije ne smije biti duži od 50 karaktera.',
        ],
        'company_email' => [
            'required' => 'Email kompanije je obavezan.',
            'email' => 'Email kompanije mora biti validna email adresa.',
        ],
        'title' => [
            'max' => 'Naziv ne smije biti duži od 50 karaktera.',
        ],
        'website' => [
            'required' => 'Web sajt URL je obavezan.',
            'url' => 'Web sajt mora biti validna URL adresa.',
            'regex' => 'Format web sajta je nevalidan.',
        ],
        'phone' => [
            'required' => 'Broj telefona je obavezan.',
        ],
        'address' => [
            'required' => 'Adresa je obavezna.',
        ],
        'state' => [
            'required' => 'Država je obavezna.',
        ],
        'country' => [
            'required' => 'Zemlja je obavezna.',
        ],
        'gstin' => [
            'regex' => 'GSTIN format je nevažeći.',
        ],
        'default_currency' => [
            'required' => 'Podrazumijevana valuta je obavezna.',
        ],
        'admin_logo' => [
            'mimes' => 'Logo administratora mora biti fajl tipa: jpeg, png, jpg.',
            'max' => 'Logo administratora ne smije biti veći od 2MB.',
        ],
        'fav_icon' => [
            'mimes' => 'Favicon mora biti fajl tipa: jpeg, png, jpg.',
            'max' => 'Favicon ne smije biti veći od 2MB.',
        ],
        'logo' => [
            'mimes' => 'Logo mora biti fajl tipa: jpeg, png, jpg.',
            'max' => 'Logo ne smije biti veći od 2MB.',
        ],
    ],
    'og_image' => [
        'mimes' => 'OG slika mora biti datoteka tipa:jpeg, png, jpg, webp.',
        'max' => 'OG slika ne smije biti veća od 2MB.',
    ],
    'social_media_form' => [
        'name' => [
            'required' => 'Polje za naziv je obavezno.',
            'unique' => 'Ovaj naziv već postoji.',
            'max' => 'Naziv ne smije biti duži od 50 karaktera.',
        ],
        'link' => [
            'required' => 'Polje za link je obavezno.',
            'url' => 'Link mora biti validna URL adresa.',
            'regex' => 'Format linka je nevalidan.',
        ],
        'class' => [
            'required' => 'Polje klase ikone je obavezno.',
        ],
        'fa_class' => [
            'required' => 'Polje klase ikone je obavezno.',
        ],
    ],
    'custom' => [
        'password' => [
            'required_if' => 'Polje za lozinku je obavezno za odabrani mail drajver.',
        ],
        'port' => [
            'required_if' => 'Polje za port je obavezno za SMTP.',
        ],
        'encryption' => [
            'required_if' => 'Polje za enkripciju je obavezno za SMTP.',
        ],
        'host' => [
            'required_if' => 'Polje za host je obavezno za SMTP.',
        ],
        'secret' => [
            'required_if' => 'Polje za tajnu je obavezno za odabrani mail drajver.',
        ],
        'domain' => [
            'required_if' => 'Polje za domen je obavezno za Mailgun.',
        ],
        'key' => [
            'required_if' => 'Polje za ključ je obavezno za SES.',
        ],
        'region' => [
            'required_if' => 'Polje za region je obavezno za SES.',
        ],
        'email' => [
            'required_if' => 'Polje za email je obavezno za odabrani mail drajver.',
            'required' => 'Polje za email je obavezno.',
            'email' => 'Molimo unesite validnu email adresu.',
            'not_matching' => 'Domen emaila mora odgovarati domeni trenutne stranice.',
        ],
        'driver' => [
            'required' => 'Polje za drajver je obavezno.',
        ],
    ],
    'customer_form' => [
        'first_name' => [
            'required' => 'Polje za ime je obavezno.',
        ],
        'last_name' => [
            'required' => 'Polje za prezime je obavezno.',
        ],
        'company' => [
            'required' => 'Polje za kompaniju je obavezno.',
        ],
        'mobile' => [
            'regex' => 'Format broja mobilnog telefona nije validan.',
        ],
        'address' => [
            'required' => 'Polje za adresu je obavezno.',
        ],
        'zip' => [
            'required' => 'Polje za poštanski broj je obavezno.',
            'min' => 'Poštanski broj mora imati najmanje 5 cifara.',
            'numeric' => 'Poštanski broj mora biti numerički.',
        ],
        'email' => [
            'required' => 'Polje za email je obavezno.',
            'email' => 'Email mora biti validna email adresa.',
            'unique' => 'Ovaj email je već zauzet.',
        ],
    ],
    'contact_request' => [
        'conName' => 'Polje za ime je obavezno.',
        'email' => 'Polje za email je obavezno.',
        'conmessage' => 'Polje za poruku je obavezno.',
        'Mobile' => 'Polje za mobilni broj je obavezno.',
        'country_code' => 'Polje za pozivni broj zemlje je obavezno.',
        'demoname' => 'Polje za ime je obavezno.',
        'demomessage' => 'Polje za poruku je obavezno.',
        'demoemail' => 'Polje za email je obavezno.',
        'congg-recaptcha-response-1.required' => 'Verifikacija robota nije uspjela. Molimo pokušajte ponovo.',
        'demo-recaptcha-response-1.required' => 'Verifikacija robota nije uspjela. Molimo pokušajte ponovo.',
    ],
    'frontend_pages' => [
        'name' => [
            'required' => 'Polje za ime je obavezno.',
            'unique' => 'Ovo ime već postoji.',
            'max' => 'Ime ne smije biti duže od 20 karaktera.',
            'regex' => 'Ime može sadržavati samo slova i razmake.',
        ],
        'publish' => [
            'required' => 'Polje za objavljivanje je obavezno.',
        ],
        'slug' => [
            'required' => 'Polje za slug je obavezno.',
            'unique' => 'Ovaj puž već postoji.',
        ],
        'url' => [
            'required' => 'Polje za URL je obavezno.',
            'url' => 'URL mora biti validan link.',
            'regex' => 'Format URL-a je nevažeći.',
        ],
        'content' => [
            'required' => 'Polje za sadržaj je obavezno.',
        ],
        'created_at' => [
            'required' => 'Polje za datum kreiranja je obavezno.',
        ],
        'parent_page_id' => [
            'exists' => 'Odabrana roditeljska stranica ne postoji.',
            'self' => 'Stranica ne može biti vlastiti roditelj.',
            'nested' => 'Odabrana stranica je već podstranica i ne može se koristiti kao nadređena.',
        ],
    ],
    'order_form' => [
        'client' => [
            'required' => 'Polje za klijenta je obavezno.',
        ],
        'payment_method' => [
            'required' => 'Polje za način plačanja je obavezno.',
        ],
        'promotion_code' => [
            'required' => 'Polje za promotivni kod je obavezno.',
        ],
        'order_status' => [
            'required' => 'Polje za status narudžbe je obavezno.',
        ],
        'product' => [
            'required' => 'Polje za proizvod je obavezno.',
        ],
        'subscription' => [
            'required' => 'Polje za pretplatu je obavezno.',
        ],
        'price_override' => [
            'numeric' => 'Cijena mora biti broj.',
        ],
        'qty' => [
            'integer' => 'Količina mora biti cijeli broj.',
        ],
    ],
    'coupon_form' => [
        'code' => [
            'required' => 'Polje za kupon kod je obavezno.',
            'string' => 'Kupon kod mora biti string.',
            'max' => 'Kupon kod ne smije biti duži od 255 karaktera.',
        ],
        'type' => [
            'required' => 'Polje za tip je obavezno.',
            'in' => 'Nevalidan tip. Dozvoljeni tipovi su: procenat, drugi_tip.',
        ],
        'applied' => [
            'required' => 'Polje za primijenjeno za proizvod je obavezno.',
            'date' => 'Polje za primijenjeno za proizvod mora biti validan datum.',
        ],
        'uses' => [
            'required' => 'Polje za broj korištenja je obavezno.',
            'numeric' => 'Polje za broj korištenja mora biti broj.',
            'min' => 'Polje za broj korištenja mora biti najmanje :min.',
        ],
        'start' => [
            'required' => 'Polje za početak je obavezno.',
            'date' => 'Polje za početak mora biti validan datum.',
        ],
        'expiry' => [
            'required' => 'Polje za datum isteka je obavezno.',
            'date' => 'Polje za datum isteka mora biti validan datum.',
            'after' => 'Datum isteka mora biti nakon datuma početka.',
        ],
        'value' => [
            'required' => 'Polje za vrijednost popusta je obavezno.',
            'numeric' => 'Polje za vrijednost popusta mora biti broj.',
            'between' => 'Polje za vrijednost popusta mora biti između :min i :max ako je tip procenat.',
            'max' => 'Vrijednost popusta ne može premašiti cijenu primijenjenog proizvoda (:max).',
        ],
    ],
    'tax_form' => [
        'name' => [
            'required' => 'Polje za naziv je obavezno.',
        ],
        'rate' => [
            'required' => 'Polje za stopu je obavezno.',
            'numeric' => 'Stopa mora biti broj.',
            'decimal' => 'Stopa mora imati najviše 3 decimale.',
            'max' => 'Stopa ne smije biti veća od 999.999.',
        ],
        'priority' => [
            'required' => 'Polje prioriteta je obavezno.',
            'min' => 'Prioritet mora biti najmanje 1.',
        ],
        'level' => [
            'required' => 'Polje za nivo je obavezno.',
            'integer' => 'Nivo mora biti cijeli broj.',
        ],
        'country' => [
            'required' => 'Polje za državu je obavezno.',
        ],
        'state' => [
            'required' => 'Polje za regiju je obavezno.',
        ],
    ],
    'subscription_form' => [
        'name' => [
            'required' => 'Polje za naziv je obavezno.',
        ],
        'subscription' => [
            'required' => 'Polje za pretplatu je obavezno.',
        ],
        'regular_price' => [
            'required' => 'Polje za regularnu cijenu je obavezno.',
            'numeric' => 'Regularna cijena mora biti broj.',
        ],
        'selling_price' => [
            'required' => 'Polje za prodajnu cijenu je obavezno.',
            'numeric' => 'Prodajna cijena mora biti broj.',
        ],
        'products' => [
            'required' => 'Polje za proizvode je obavezno.',
        ],
    ],
    'bundle' => [
        'name' => [
            'required' => 'Polje za naziv je obavezno.',
        ],
        'items' => [
            'required' => 'Svaka stavka je obavezna.',
        ],
    ],
    'group' => [
        'name' => [
            'required' => 'Naziv je obavezan',
            'unique' => 'Ovo ime već postoji.',
        ],
        'pricing_templates_id' => [
            'required' => 'Predložak dizajna je obavezan.',
        ],
        'features' => [
            'name' => [
                'required' => 'Sva polja za karakteristike su obavezna',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => 'Cijena je obavezna',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => 'Vrijednost je obavezna',
            ],
        ],
        'type' => [
            'required_with' => 'Tip je obavezan',
        ],
        'title' => [
            'required_with' => 'Naslov je obavezan',
        ],
    ],
    'product' => [
        'name' => [
            'required' => 'Polje za naziv je obavezno.',
        ],
        'type' => [
            'required' => 'Polje za tip je obavezno.',
        ],
        'product_type' => [
            'required' => 'Polje kategorije proizvoda je obavezno.',
        ],
        'group' => [
            'required' => 'Polje za grupu je obavezno.',
        ],
        'subscription' => [
            'required' => 'Polje za pretplatu je obavezno.',
        ],
        'currency' => [
            'required' => 'Polje za valutu je obavezno.',
        ],
        'file' => [
            'required_without_all' => 'Polje za fajl je obavezno ako nisu dostupni github_owner ili github_repository.',
            'mimes' => 'Fajl mora biti ZIP format.',
        ],
        'image' => [
            'required_without_all' => 'Polje za sliku je obavezno ako nisu dostupni github_owner ili github_repository.',
            'mimes' => 'Slika mora biti u PNG formatu.',
        ],
        'github_owner' => [
            'required' => 'Polje vlasnika GitHub-a je obavezno.',
            'required_without_all' => 'Polje za GitHub vlasnika je obavezno ako nisu dostupni file ili image.',
        ],
        'github_repository' => [
            'required' => 'Polje GitHub repozitorija je obavezno.',
            'required_without_all' => 'Polje za GitHub repozitorij je obavezno ako nisu dostupni file ili image.',
            'required_if' => 'Polje za GitHub repozitorij je obavezno ako je tip 2.',
        ],
        'shoping_cart_link' => [
            'required' => 'Polje veze sa korpom za kupovinu je obavezno.',
        ],
    ],
    'users' => [
        'first_name' => [
            'required' => 'Polje za ime je obavezno.',
        ],
        'last_name' => [
            'required' => 'Polje za prezime je obavezno.',
        ],
        'company' => [
            'required' => 'Polje za kompaniju je obavezno.',
        ],
        'email' => [
            'required' => 'Polje za email je obavezno.',
            'email' => 'Email mora biti važeća email adresa.',
            'unique' => 'Email adresa je već zauzeta.',
        ],
        'address' => [
            'required' => 'Polje za adresu je obavezno.',
        ],
        'mobile' => [
            'required' => 'Polje za mobilni broj je obavezno.',
        ],
        'country' => [
            'required' => 'Polje za zemlju je obavezno.',
            'exists' => 'Izabrana zemlja nije validna.',
        ],
        'state' => [
            'required_if' => 'Polje za državu je obavezno kada je zemlja Indija.',
        ],
        'timezone_id' => [
            'required' => 'Polje za vremensku zonu je obavezno.',
        ],
        'user_name' => [
            'required' => 'Polje za korisničko ime je obavezno.',
            'unique' => 'Korisničko ime je već zauzeto.',
        ],
        'zip' => [
            'regex' => 'Polje za poštanski broj je obavezno kada je zemlja Indija.',
        ],
        'gstin' => [
            'regex' => 'GSTIN format je nevažeći.',
        ],
    ],
    'profile_form' => [
        'first_name' => [
            'required' => 'Ime je obavezno.',
            'min' => 'Ime mora imati najmanje :min karaktera.',
            'max' => 'Ime ne može biti duže od :max karaktera.',
        ],
        'last_name' => [
            'required' => 'Prezime je obavezno.',
            'max' => 'Prezime ne može biti duže od :max karaktera.',
        ],
        'company' => [
            'required' => 'Naziv kompanije je obavezan.',
            'max' => 'Naziv kompanije ne može biti duži od :max karaktera.',
        ],
        'email' => [
            'required' => 'Email je obavezan.',
            'email' => 'Unesite validnu email adresu.',
            'unique' => 'Email adresa je već zauzeta. Molimo odaberite drugačiji email.',
        ],
        'mobile' => [
            'required' => 'Mobilni broj je obavezan.',
            'regex' => 'Unesite validan mobilni broj.',
            'min' => 'Mobilni broj mora imati najmanje :min karaktera.',
            'max' => 'Mobilni broj ne može biti duži od :max karaktera.',
        ],
        'address' => [
            'required' => 'Adresa je obavezna.',
        ],
        'user_name' => [
            'required' => 'Korisničko ime je obavezno.',
            'unique' => 'Korisničko ime je već zauzeto.',
        ],
        'timezone_id' => [
            'required' => 'Vremenska zona je obavezna.',
        ],
        'country' => [
            'required' => 'Zemlja je obavezna.',
            'exists' => 'Izabrana zemlja nije validna.',
        ],
        'state' => [
            'required_if' => 'Polje za državu je obavezno kada je zemlja Indija.',
        ],
        'gstin' => [
            'regex' => 'GSTIN format je nevažeći.',
        ],
        'old_password' => [
            'required' => 'Stara lozinka je obavezna.',
            'min' => 'Stara lozinka mora imati najmanje :min karaktera.',
        ],
        'new_password' => [
            'required' => 'Nova lozinka je obavezna.',
            'different' => 'Nova lozinka mora biti različita od stare lozinke.',
        ],
        'confirm_password' => [
            'required' => 'Potvrda lozinke je obavezna.',
            'same' => 'Potvrda lozinke mora odgovarati novoj lozinci.',
        ],
        'terms' => [
            'required' => 'Morate prihvatiti uslove.',
        ],
        'password' => [
            'required' => 'Lozinka je obavezna.',
        ],
        'password_confirmation' => [
            'required' => 'Potvrda lozinke je obavezna.',
            'same' => 'Lozinke se ne podudaraju.',
        ],
        'mobile_code' => [
            'required' => 'Unesite pozivni broj zemlje (mobilni).',
        ],
    ],
    'invoice' => [
        'user' => [
            'required' => 'Polje za klijente je obavezno.',
        ],
        'date' => [
            'required' => 'Polje za datum je obavezno.',
            'date' => 'Datum mora biti validan.',
        ],
        'domain' => [
            'required' => 'Polje domene je obavezno.',
            'regex' => 'Format domene nije validan.',
        ],
        'cloud_domain' => [
            'required' => 'Polje domena oblaka je obavezno.',
            'regex' => 'Dozvoljena su samo slova, brojevi i crtice.',
        ],
        'plan' => [
            'required_if' => 'Polje za pretplatu je obavezno.',
        ],
        'price' => [
            'required' => 'Polje za cijenu je obavezno.',
        ],
        'product' => [
            'required' => 'Polje za proizvod je obavezno.',
        ],
    ],
    'domain_form' => [
        'domain' => [
            'required' => 'Polje za domenu je obavezno.',
            'url' => 'Domena mora biti validan URL.',
        ],
    ],
    'product_renewal' => [
        'domain' => [
            'required' => 'Polje za domenu je obavezno.',
            'no_http' => 'Domena ne smije sadržavati "http" ili "https".',
        ],
    ],
    'language' => [
        'required' => 'Polje za jezik je obavezno.',
        'invalid' => 'Odabrani jezik nije validan.',
    ],
    'storage_path' => [
        'disk' => [
            'required' => 'Polje za skladišni disk je obavezno.',
            'string' => 'Disk mora biti tekstualna vrijednost.',
        ],
        'path' => [
            'required' => 'Polje za put memorije je obavezno.',
            'string' => 'Putanja mora biti tekstualna vrijednost.',
            'nullable' => 'Polje za putanju je opcionalno.',
            'invalid' => 'Putanja ne postoji ili se ne može pisati.',
        ],
    ],
    'pdf_settings' => [
        'node_path' => [
            'required' => 'Polje putanje čvora je obavezno.',
            'string' => 'Putanja čvora mora biti važeći niz.',
        ],
        'npm_path' => [
            'required' => 'Polje npm putanje je obavezno.',
            'string' => 'Putanja npm mora biti ispravan niz.',
        ],
        'chrome_path' => [
            'required' => 'Hromirano polje putanje je obavezno.',
            'string' => 'Hromirana putanja mora biti važeći niz.',
            'invalid' => 'Chrome putanja ne postoji ili nije izvršna.',
        ],
    ],
    'validate_secret' => [
        'totp' => [
            'required' => 'Molimo unesite kod',
            'digits' => 'Molimo unesite validan 6-cifreni kod',
        ],
    ],
    'verify_email' => [
        'required' => 'Polje za email je obavezno.',
        'email' => 'Email mora biti validna email adresa.',
        'verify_email' => 'Verifikacija emaila nije uspjela.',
    ],
    'verify_country_code' => [
        'required' => 'Pozivni broj države je obavezan.',
        'numeric' => 'Pozivni broj države mora biti validan broj.',
        'verify_country_code' => 'Verifikacija pozivnog broja države nije uspjela.',
    ],
    'verify_number' => [
        'required' => 'Broj je obavezan.',
        'numeric' => 'Broj mora biti validan.',
        'verify_number' => 'Verifikacija broja nije uspjela.',
    ],
    'password_otp' => [
        'required' => 'Polje za lozinku je obavezno.',
        'password' => 'Lozinka nije tačna.',
        'invalid' => 'Nevažeća lozinka.',
    ],
    'auth_controller' => [
        'name_required' => 'Ime je obavezno.',
        'name_max' => 'Ime ne smije biti duže od 255 karaktera.',
        'email_required' => 'Email je obavezan.',
        'email_email' => 'Unesite važeću email adresu.',
        'email_max' => 'Email ne smije biti duži od 255 karaktera.',
        'email_unique' => 'Ovaj email je već registrovan.',
        'password_required' => 'Lozinka je obavezna.',
        'password_confirmed' => 'Potvrda lozinke se ne podudara.',
        'password_min' => 'Lozinka mora imati najmanje 6 karaktera.',
    ],
    'resend_otp' => [
        'eid_required' => 'EID polje je obavezno.',
        'eid_string' => 'EID mora biti string.',
        'type_required' => 'Polje tipa je obavezno.',
        'type_string' => 'Tip mora biti string.',
        'type_in' => 'Izabrani tip je nevažeći.',
    ],
    'verify_otp' => [
        'eid_required' => 'EID polje je obavezno.',
        'eid_string' => 'EID mora biti string.',
        'otp_required' => 'OTP je obavezan.',
        'otp_size' => 'OTP mora biti tačno 6 karaktera.',
        'recaptcha_required' => 'Molimo završite CAPTCHA verifikaciju.',
        'recaptcha_size' => 'CAPTCHA odgovor je nevažeći.',
    ],
    'company_validation' => [
        'company_required' => 'Ime kompanije je obavezno.',
        'company_string' => 'Kompanija mora biti tekst.',
        'address_required' => 'Adresa je obavezna.',
        'address_string' => 'Adresa mora biti tekst.',
    ],
    'token_validation' => [
        'token_required' => 'Token je obavezan.',
        'password_required' => 'Polje lozinke je obavezno.',
        'password_confirmed' => 'Potvrda lozinke se ne podudara.',
    ],
    'custom_email' => [
        'required' => 'Email polje je obavezno.',
        'email' => 'Molimo unesite važeću email adresu.',
        'exists' => 'Ovaj email nije registrovan kod nas.',
    ],
    'newsletterEmail' => [
        'required' => 'Email za newsletter je obavezan.',
        'email' => 'Molimo unesite važeću email adresu za newsletter.',
    ],
    'widget' => [
        'name_required' => 'Ime je obavezno.',
        'name_max' => 'Ime ne smije biti duže od 50 karaktera.',
        'publish_required' => 'Status objavljivanja je obavezan.',
        'type_required' => 'Tip je obavezan.',
        'type_unique' => 'Ovaj tip već postoji.',
    ],
    'payment' => [
        'payment_date_required' => 'Datum plaćanja je obavezan.',
        'payment_method_required' => 'Metoda plaćanja je obavezna.',
        'amount_required' => 'Iznos je obavezan.',
    ],
    'custom_date' => [
        'date_required' => 'Polje za datum je obavezno.',
        'total_required' => 'Polje za ukupno je obavezno.',
        'status_required' => 'Polje za status je obavezno.',
    ],
    'plan_renewal' => [
        'plan_required' => 'Polje za plan je obavezno.',
        'payment_method_required' => 'Polje za metodu plaćanja je obavezno.',
        'cost_required' => 'Polje za trošak je obavezno.',
        'code_not_valid' => 'Promotivni kod nije važeći.',
    ],
    'rate' => [
        'required' => 'Ocjena je obavezna.',
        'numeric' => 'Ocjena mora biti broj.',
    ],
    'product_validate' => [
        'producttitle_required' => 'Naziv proizvoda je obavezan.',
        'version_required' => 'Verzija je obavezna.',
        'filename_required' => 'Molimo prenesite fajl.',
        'dependencies_required' => 'Polje za zavisnosti je obavezno.',
        'description_required' => 'Opis je obavezan.',
        'release_type_required' => 'Vrsta izdanja je obavezna.',
    ],
    'product_sku_unique' => 'SKU proizvoda treba biti jedinstven',
    'product_name_unique' => 'Ime treba biti jedinstveno',
    'product_show_agent_required' => 'Odaberite vašu preferencu stranice sa korpom',
    'config_file_path_regex' => 'Mora biti relativna putanja bez ../ segmenata.',
    'license_file_path_regex' => 'Mora biti relativna putanja bez ../ segmenata.',
    'product_controller' => [
        'name_required' => 'Ime proizvoda je obavezno.',
        'name_unique' => 'Ime treba biti jedinstveno.',
        'name_unique_in_group' => 'Proizvod s ovim imenom već postoji u odabranoj grupi. Molimo odaberite drugo ime ili drugu grupu.',
        'type_required' => 'Tip proizvoda je obavezan.',
        'description_required' => 'Opis proizvoda je obavezan.',
        'product_description_required' => 'Detaljan opis proizvoda je obavezan.',
        'short_description_required' => 'Kratak opis je obavezan.',
        'image_mimes' => 'Slika mora biti fajl tipa: jpeg, png, jpg.',
        'image_max' => 'Slika ne smije biti veća od 2048 kilobajta.',
        'product_sku_required' => 'SKU proizvoda je obavezan.',
        'group_required' => 'Grupa proizvoda je obavezna.',
        'show_agent_required' => 'Odaberite vašu preferencu stranice sa korpom.',
    ],
    'current_domain_required' => 'Trenutna domena je obavezna.',
    'new_domain_required' => 'Nova domena je obavezna.',
    'special_characters_not_allowed' => 'Specijalni karakteri nisu dozvoljeni u imenu domene.',
    'orderno_required' => 'Broj narudžbe je obavezan.',
    'cloud_central_domain_required' => 'Cloud centralna domena je obavezna.',
    'cloud_cname_required' => 'Cloud CNAME je obavezan.',
    'cloud_tenant' => [
        'cloud_top_message_required' => 'Cloud gornja poruka je obavezna.',
        'cloud_label_field_required' => 'Cloud polje za oznaku je obavezno.',
        'cloud_label_radio_required' => 'Cloud radio oznaka je obavezna.',
        'cloud_product_required' => 'Cloud proizvod je obavezan.',
        'cloud_product_unique' => 'Ovaj proizvod već ima konfiguraciju u oblaku.',
        'cloud_free_plan_required' => 'Cloud besplatan plan je obavezan.',
        'cloud_free_plan_invalid' => 'Odabrani plan ne pripada odabranom proizvodu.',
        'cloud_product_key_required' => 'Cloud ključ proizvoda je obavezan.',
    ],
    'reg_till_after' => 'Datum registracije do mora biti poslije datuma registracije od.',
    'extend_product' => [
        'title_required' => 'Polje za naslov je obavezno.',
        'version_required' => 'Polje za verziju je obavezno.',
        'dependencies_required' => 'Polje za zavisnosti je obavezno.',
    ],
    'please_enter_recovery_code' => 'Molimo unesite kod za oporavak.',
    'social_login' => [
        'client_id_required' => 'Client ID je obavezan za Google, Github, ili Linkedin.',
        'client_secret_required' => 'Client Secret je obavezan za Google, Github, ili Linkedin.',
        'api_key_required' => 'API ključ je obavezan za Twitter.',
        'api_secret_required' => 'API Secret je obavezan za Twitter.',
        'redirect_url_required' => 'Redirect URL je obavezan.',
    ],
    'thirdparty_api' => [
        'app_name_required' => 'Naziv aplikacije je obavezan.',
        'app_key_required' => 'App ključ je obavezan.',
        'app_key_size' => 'App ključ mora biti tačno 32 karaktera.',
        'app_secret_required' => 'App tajna je obavezna.',
    ],
    'plan_request' => [
        'name_required' => 'Polje za ime je obavezno',
        'product_quant_req' => 'Polje za količinu proizvoda je obavezno kada broj agenata nije unesen.',
        'no_agent_req' => 'Polje za broj agenata je obavezno kada količina proizvoda nije unesena.',
        'pro_req' => 'Polje za proizvod je obavezno',
        'offer_price' => 'Cijena ponude ne smije biti veća od 100',
        'currency_duplicate' => 'Svaka valuta se može koristiti samo jednom.',
        'non_negative' => 'Ova vrijednost ne može biti negativna.',
    ],
    'razorpay_val' => [
        'business_required' => 'Polje za poslovanje je obavezno.',
        'cmd_required' => 'Polje za naredbu je obavezno.',
        'paypal_url_required' => 'URL PayPal-a je obavezan.',
        'paypal_url_invalid' => 'URL PayPal-a mora biti validan URL.',
        'success_url_invalid' => 'URL za uspjeh mora biti validan URL.',
        'cancel_url_invalid' => 'URL za otkazivanje mora biti validan URL.',
        'notify_url_invalid' => 'URL za obavještenje mora biti validan URL.',
        'currencies_required' => 'Polje valuta je obavezno.',
    ],
    'login_failed' => 'Prijava nije uspjela, provjerite e-mail/korisničko ime i lozinku koju ste unijeli.',
    'forgot_email_validation' => 'Ako je e-mail koji ste unijeli registriran, dobit ćete e-mail s uputama za resetiranje lozinke uskoro.',
    'too_many_login_attempts' => 'Zaključani ste iz aplikacije zbog previše neuspjelih pokušaja prijave. Molimo pokušajte ponovo nakon :time.',
    'phone_number' => 'Unesite važeći broj mobilnog telefona.',
    'mobile_number' => ':attribute mora biti važeći broj mobilnog telefona.',
    'license' => [
        'product' => [
            'required' => 'Polje proizvoda je obavezno.',
        ],
        'client' => [
            'required' => 'Polje klijenta je obavezno.',
        ],
        'license_code' => [
            'required' => 'Polje koda licence je obavezno.',
        ],
        'license_expire_date' => [
            'required' => 'Polje za datum isteka licence je obavezno.',
        ],
        'license_updates_date' => [
            'required' => 'Polje datuma isteka ažuriranja je obavezno.',
        ],
        'license_support_date' => [
            'required' => 'Polje za datum isteka podrške je obavezno.',
        ],
        'banned_host_ip' => [
            'required' => 'Polje zabranjene IP adrese je obavezno.',
            'invalid' => 'Unesite ispravnu IP adresu.',
        ],
        'installation_ip' => [
            'required' => 'Polje IP instalacije je obavezno.',
        ],
        'notification_field' => [
            'required' => 'Ovo polje obavijesti je obavezno.',
        ],
    ],
];
