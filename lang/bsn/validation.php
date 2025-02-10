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

    'custom' => [
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

];
