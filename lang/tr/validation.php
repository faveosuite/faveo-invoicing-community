<?php

return [
    'accepted' => ':attribute kabul edilmelidir.',
    'accepted_if' => ':attribute, :other :value olduğunda kabul edilmelidir.',
    'active_url' => ':attribute geçerli bir URL olmalıdır.',
    'after' => ':attribute, :date tarihinden sonraki bir tarih olmalıdır.',
    'after_or_equal' => ':attribute, :date tarihinden sonraki veya eşit bir tarih olmalıdır.',
    'alpha' => ':attribute yalnızca harflerden oluşmalıdır.',
    'alpha_dash' => ':attribute yalnızca harfler, sayılar, tireler ve alt çizgiler içerebilir.',
    'alpha_num' => ':attribute yalnızca harfler ve sayılardan oluşmalıdır.',
    'array' => ':attribute bir dizi olmalıdır.',
    'before' => ':attribute, :date tarihinden önceki bir tarih olmalıdır.',
    'before_or_equal' => ':attribute, :date tarihinden önceki veya eşit bir tarih olmalıdır.',
    'between' => [
        'array' => ':attribute, :min ile :max arasında öğe içermelidir.',
        'file' => ':attribute, :min ile :max kilobayt arasında olmalıdır.',
        'numeric' => ':attribute, :min ile :max arasında olmalıdır.',
        'string' => ':attribute, :min ile :max karakter uzunluğunda olmalıdır.',
    ],
    'boolean' => ':attribute alanı doğru veya yanlış olmalıdır.',
    'confirmed' => ':attribute onayı eşleşmiyor.',
    'current_password' => 'Şifre yanlış.',
    'date' => ':attribute geçerli bir tarih olmalıdır.',
    'date_equals' => ':attribute, :date tarihine eşit olmalıdır.',
    'date_format' => ':attribute, :format formatı ile eşleşmiyor.',
    'declined' => ':attribute reddedilmelidir.',
    'declined_if' => ':attribute, :other :value olduğunda reddedilmelidir.',
    'different' => ':attribute ve :other birbirinden farklı olmalıdır.',
    'digits' => ':attribute, :digits basamaktan oluşmalıdır.',
    'digits_between' => ':attribute, :min ile :max arasında basamaktan oluşmalıdır.',
    'dimensions' => ':attribute geçersiz resim boyutlarına sahiptir.',
    'distinct' => ':attribute alanında yinelenen bir değer bulunmaktadır.',
    'doesnt_start_with' => ':attribute, şu başlangıçlardan biriyle başlayamaz: :values.',
    'email' => ':attribute geçerli bir e-posta adresi olmalıdır.',
    'ends_with' => ':attribute, şu sonlardan biriyle bitmelidir: :values.',
    'enum' => 'Seçilen :attribute geçersiz.',
    'exists' => 'Seçilen :attribute geçersiz.',
    'file' => ':attribute bir dosya olmalıdır.',
    'filled' => ':attribute alanı bir değere sahip olmalıdır.',
    'gt' => [
        'array' => ':attribute, :value öğesinden fazla olmalıdır.',
        'file' => ':attribute, :value kilobayttan büyük olmalıdır.',
        'numeric' => ':attribute, :value değerinden büyük olmalıdır.',
        'string' => ':attribute, :value karakterden uzun olmalıdır.',
    ],
    'gte' => [
        'array' => ':attribute, :value öğe veya daha fazla içermelidir.',
        'file' => ':attribute, :value kilobayt veya daha fazla olmalıdır.',
        'numeric' => ':attribute, :value değerine eşit veya daha büyük olmalıdır.',
        'string' => ':attribute, :value karakter veya daha uzun olmalıdır.',
    ],
    'image' => ':attribute bir resim dosyası olmalıdır.',
    'in' => 'Seçilen :attribute geçersiz.',
    'in_array' => ':attribute alanı :other içinde mevcut değildir.',
    'integer' => ':attribute bir tam sayı olmalıdır.',
    'ip' => ':attribute geçerli bir IP adresi olmalıdır.',
    'ipv4' => ':attribute geçerli bir IPv4 adresi olmalıdır.',
    'ipv6' => ':attribute geçerli bir IPv6 adresi olmalıdır.',
    'json' => ':attribute geçerli bir JSON dizesi olmalıdır.',
    'lt' => [
        'array' => ':attribute, :value öğesinden az olmalıdır.',
        'file' => ':attribute, :value kilobayttan küçük olmalıdır.',
        'numeric' => ':attribute, :value değerinden küçük olmalıdır.',
        'string' => ':attribute, :value karakterden kısa olmalıdır.',
    ],
    'lte' => [
        'array' => ':attribute, :value öğe veya daha az içermelidir.',
        'file' => ':attribute, :value kilobayt veya daha az olmalıdır.',
        'numeric' => ':attribute, :value değerine eşit veya daha küçük olmalıdır.',
        'string' => ':attribute, :value karakter veya daha kısa olmalıdır.',
    ],
    'mac_address' => ':attribute geçerli bir MAC adresi olmalıdır.',
    'max' => [
        'array' => ':attribute, :max öğeden fazla olamaz.',
        'file' => ':attribute, :max kilobayttan büyük olamaz.',
        'numeric' => ':attribute, :max değerinden büyük olamaz.',
        'string' => ':attribute, :max karakterden uzun olamaz.',
    ],
    'mimes' => ':attribute şu dosya türlerinden biri olmalıdır: :values.',
    'mimetypes' => ':attribute şu dosya türlerinden biri olmalıdır: :values.',
    'min' => [
        'array' => ':attribute en az :min öğe içermelidir.',
        'file' => ':attribute en az :min kilobayt olmalıdır.',
        'numeric' => ':attribute en az :min olmalıdır.',
        'string' => ':attribute en az :min karakter uzunluğunda olmalıdır.',
    ],
    'multiple_of' => ':attribute, :value\'nin katı olmalıdır.',
    'not_in' => 'Seçilen :attribute geçersiz.',
    'not_regex' => ':attribute formatı geçersiz.',
    'numeric' => ':attribute bir sayı olmalıdır.',
    'password' => [
        'letters' => ':attribute en az bir harf içermelidir.',
        'mixed' => ':attribute en az bir büyük harf ve bir küçük harf içermelidir.',
        'numbers' => ':attribute en az bir rakam içermelidir.',
        'symbols' => ':attribute en az bir sembol içermelidir.',
        'uncompromised' => 'Verilen :attribute, bir veri sızıntısında yer alıyor. Lütfen farklı bir :attribute seçin.',
    ],
    'present' => ':attribute alanı mevcut olmalıdır.',
    'prohibited' => ':attribute alanı yasaktır.',
    'prohibited_if' => ':attribute alanı, :other :value olduğunda yasaktır.',
    'prohibited_unless' => ':attribute alanı, :other :values içinde olmadığı sürece yasaktır.',
    'prohibits' => ':attribute alanı, :other\'ın mevcut olmasını engeller.',
    'regex' => ':attribute formatı geçersiz.',
    'required' => ':attribute alanı gereklidir.',
    'required_array_keys' => ':attribute alanı şu anahtarları içermelidir: :values.',
    'required_if' => ':attribute alanı, :other :value olduğunda gereklidir.',
    'required_unless' => ':attribute alanı, :other :values içinde değilse gereklidir.',
    'required_with' => ':attribute alanı, :values mevcut olduğunda gereklidir.',
    'required_with_all' => ':attribute alanı, :values tamamı mevcut olduğunda gereklidir.',
    'required_without' => ':attribute alanı, :values mevcut olmadığında gereklidir.',
    'required_without_all' => ':attribute alanı, :values değerlerinden hiçbiri mevcut olmadığında gereklidir.',
    'same' => ':attribute ve :other aynı olmalıdır.',
    'size' => [
        'array' => ':attribute, :size öğe içermelidir.',
        'file' => ':attribute, :size kilobayt olmalıdır.',
        'numeric' => ':attribute, :size olmalıdır.',
        'string' => ':attribute, :size karakter olmalıdır.',
    ],
    'starts_with' => ':attribute şu ifadelerden biriyle başlamalıdır: :values.',
    'string' => ':attribute bir metin olmalıdır.',
    'timezone' => ':attribute geçerli bir zaman dilimi olmalıdır.',
    'unique' => ':attribute zaten alınmış.',
    'uploaded' => ':attribute yüklenemedi.',
    'url' => ':attribute geçerli bir URL olmalıdır.',
    'uuid' => ':attribute geçerli bir UUID olmalıdır.',
    'attributes' => [],
    'publish_date_required' => 'Yayın tarihi gereklidir.',
    'price_numeric_value' => 'Fiyat sayısal bir değer olmalıdır.',
    'quantity_integer_value' => 'Miktar bir tam sayı olmalıdır.',
    'order_has_Expired' => 'Siparişin süresi doldu.',
    'expired' => 'Süresi doldu.',

    /*
    Request file custom validation messages
    */

    // Common
    'settings_form' => [
        'company' => [
            'required' => 'Şirket alanı zorunludur.',
        ],
        'website' => [
            'url' => 'Web sitesi geçerli bir URL olmalıdır.',
        ],
        'phone' => [
            'regex' => 'Telefon numarası formatı geçersiz.',
        ],
        'address' => [
            'required' => 'Adres alanı zorunludur.',
            'max' => 'Adres 300 karakterden uzun olamaz.',
        ],
        'logo' => [
            'mimes' => 'Logo PNG dosyası olmalıdır.',
        ],
        'driver' => [
            'required' => 'Sürücü alanı zorunludur.',
        ],
        'port' => [
            'integer' => 'Port bir tam sayı olmalıdır.',
        ],
        'email' => [
            'required' => 'E-posta alanı zorunludur.',
            'email' => 'E-posta geçerli bir e-posta adresi olmalıdır.',
        ],
        'password' => [
            'required' => 'Şifre alanı zorunludur.',
        ],
        'error_email' => [
            'email' => 'Hata e-postası geçerli bir e-posta adresi olmalıdır.',
        ],
    ],

    'settings_forms' => [
        'company' => [
            'required' => 'Şirket adı zorunludur.',
            'max' => 'Şirket adı 50 karakteri geçemez.',
        ],
        'company_email' => [
            'required' => 'Şirket e-postası zorunludur.',
            'email' => 'Şirket e-postası geçerli bir e-posta adresi olmalıdır.',
        ],
        'title' => [
            'max' => 'Başlık 50 karakteri geçemez.',
        ],
        'website' => [
            'required' => 'Web sitesi URL\'si zorunludur.',
            'url' => 'Web sitesi geçerli bir URL olmalıdır.',
            'regex' => 'Web sitesi formatı geçersiz.',
        ],
        'phone' => [
            'required' => 'Telefon numarası zorunludur.',
        ],
        'address' => [
            'required' => 'Adres zorunludur.',
        ],
        'state' => [
            'required' => 'Eyalet zorunludur.',
        ],
        'country' => [
            'required' => 'Ülke zorunludur.',
        ],
        'gstin' => [
            'max' => 'GSTIN 15 karakteri geçemez.',
        ],
        'default_currency' => [
            'required' => 'Varsayılan para birimi zorunludur.',
        ],
        'admin_logo' => [
            'mimes' => 'Yönetici logosu jpeg, png, jpg dosya türlerinden biri olmalıdır.',
            'max' => 'Yönetici logosu 2MB\'dan büyük olamaz.',
        ],
        'fav_icon' => [
            'mimes' => 'Fav ikon jpeg, png, jpg dosya türlerinden biri olmalıdır.',
            'max' => 'Fav ikon 2MB\'dan büyük olamaz.',
        ],
        'logo' => [
            'mimes' => 'Logo jpeg, png, jpg dosya türlerinden biri olmalıdır.',
            'max' => 'Logo 2MB\'dan büyük olamaz.',
        ],
    ],

    'social_media_form' => [
        'name' => [
            'required' => 'İsim alanı zorunludur.',
            'unique' => 'Bu isim zaten mevcut.',
            'max' => 'İsim 50 karakteri geçemez.',
        ],
        'link' => [
            'required' => 'Bağlantı alanı zorunludur.',
            'url' => 'Bağlantı geçerli bir URL olmalıdır.',
            'regex' => 'Bağlantı formatı geçersiz.',
        ],
    ],

    // Email
    'custom' => [
        'password' => [
            'required_if' => 'Seçilen posta sürücüsü için şifre alanı zorunludur.',
        ],
        'port' => [
            'required_if' => 'SMTP için port alanı zorunludur.',
        ],
        'encryption' => [
            'required_if' => 'SMTP için şifreleme alanı zorunludur.',
        ],
        'host' => [
            'required_if' => 'SMTP için sunucu alanı zorunludur.',
        ],
        'secret' => [
            'required_if' => 'Seçilen posta sürücüsü için gizli anahtar alanı zorunludur.',
        ],
        'domain' => [
            'required_if' => 'Mailgun için alan adı alanı zorunludur.',
        ],
    ],
    'key' => [
        'required_if' => 'SES için anahtar alanı zorunludur.',
    ],
    'region' => [
        'required_if' => 'SES için bölge alanı zorunludur.',
    ],
    'email' => [
        'required_if' => 'Seçilen e-posta sağlayıcısı için e-posta alanı zorunludur.',
        'required' => 'E-posta alanı zorunludur.',
        'email' => 'Lütfen geçerli bir e-posta adresi girin.',
        'not_matching' => 'E-posta alan adı, mevcut site alan adıyla eşleşmelidir.',
    ],
    'driver' => [
        'required' => 'Sürücü alanı zorunludur.',
    ],

    'customer_form' => [
        'first_name' => [
            'required' => 'Ad alanı zorunludur.',
        ],
        'last_name' => [
            'required' => 'Soyad alanı zorunludur.',
        ],
        'company' => [
            'required' => 'Şirket alanı zorunludur.',
        ],
        'mobile' => [
            'regex' => 'Mobil numara formatı geçersiz.',
        ],
        'address' => [
            'required' => 'Adres alanı zorunludur.',
        ],
        'zip' => [
            'required' => 'Posta kodu alanı zorunludur.',
            'min' => 'Posta kodu en az 5 basamak olmalıdır.',
            'numeric' => 'Posta kodu sayısal olmalıdır.',
        ],
        'email' => [
            'required' => 'E-posta alanı zorunludur.',
            'email' => 'E-posta geçerli bir e-posta adresi olmalıdır.',
            'unique' => 'Bu e-posta zaten alınmış.',
        ],
    ],

    'contact_request' => [
        'conName' => 'İsim alanı zorunludur.',
        'email' => 'E-posta alanı zorunludur.',
        'conmessage' => 'Mesaj alanı zorunludur.',
        'Mobile' => 'Mobil alanı zorunludur.',
        'country_code' => 'Mobil alanı zorunludur.',
        'demoname' => 'İsim alanı zorunludur.',
        'demomessage' => 'Mesaj alanı zorunludur.',
        'demoemail' => 'E-posta alanı zorunludur.',
        'congg-recaptcha-response-1.required' => 'Robot doğrulaması başarısız. Lütfen tekrar deneyin.',
        'demo-recaptcha-response-1.required' => 'Robot doğrulaması başarısız. Lütfen tekrar deneyin.',
    ],

    'frontend_pages' => [
        'name' => [
            'required' => 'İsim alanı zorunludur.',
            'unique' => 'Bu isim zaten mevcut.',
            'max' => 'İsim 20 karakteri geçemez.',
            'regex' => 'İsim sadece harf ve boşluk içerebilir.',
        ],
        'publish' => [
            'required' => 'Yayınla alanı zorunludur.',
        ],
        'slug' => [
            'required' => 'Slug alanı zorunludur.',
        ],
        'url' => [
            'required' => 'URL alanı zorunludur.',
            'url' => 'URL geçerli bir bağlantı olmalıdır.',
            'regex' => 'URL formatı geçersiz.',
        ],
        'content' => [
            'required' => 'İçerik alanı zorunludur.',
        ],
        'created_at' => [
            'required' => 'Oluşturulma tarihi alanı zorunludur.',
        ],
    ],

    // Order form
    'order_form' => [
        'client' => [
            'required' => 'Müşteri alanı zorunludur.',
        ],
        'payment_method' => [
            'required' => 'Ödeme yöntemi alanı zorunludur.',
        ],
        'promotion_code' => [
            'required' => 'Promosyon kodu alanı zorunludur.',
        ],
        'order_status' => [
            'required' => 'Sipariş durumu alanı zorunludur.',
        ],
        'product' => [
            'required' => 'Ürün alanı zorunludur.',
        ],
        'subscription' => [
            'required' => 'Abonelik alanı zorunludur.',
        ],
        'price_override' => [
            'numeric' => 'Fiyat geçersiz. Sayısal bir değer olmalıdır.',
        ],
        'qty' => [
            'integer' => 'Miktar bir tam sayı olmalıdır.',
        ],
    ],
    // Payment form
    'coupon_form' => [
        'code' => [
            'required' => 'Kupon kodu alanı zorunludur.',
            'string' => 'Kupon kodu bir metin olmalıdır.',
            'max' => 'Kupon kodu 255 karakteri geçemez.',
        ],
        'type' => [
            'required' => 'Tür alanı zorunludur.',
            'in' => 'Geçersiz tür. İzin verilen değerler: yüzde, diğer_tür.',
        ],
        'applied' => [
            'required' => 'Ürün için uygulama alanı zorunludur.',
            'date' => 'Ürün için uygulama alanı geçerli bir tarih olmalıdır.',
        ],
        'uses' => [
            'required' => 'Kullanım alanı zorunludur.',
            'numeric' => 'Kullanım alanı bir sayı olmalıdır.',
            'min' => 'Kullanım alanı en az :min olmalıdır.',
        ],
        'start' => [
            'required' => 'Başlangıç alanı zorunludur.',
            'date' => 'Başlangıç alanı geçerli bir tarih olmalıdır.',
        ],
        'expiry' => [
            'required' => 'Son kullanma tarihi alanı zorunludur.',
            'date' => 'Son kullanma tarihi alanı geçerli bir tarih olmalıdır.',
            'after' => 'Son kullanma tarihi, başlangıç tarihinden sonra olmalıdır.',
        ],
        'value' => [
            'required' => 'İndirim değeri alanı zorunludur.',
            'numeric' => 'İndirim değeri alanı bir sayı olmalıdır.',
            'between' => 'İndirim değeri alanı, tür yüzde ise :min ve :max arasında olmalıdır.',
        ],
    ],

    'tax_form' => [
        'name' => [
            'required' => 'İsim alanı zorunludur.',
        ],
        'rate' => [
            'required' => 'Oran alanı zorunludur.',
            'numeric' => 'Oran bir sayı olmalıdır.',
        ],
        'level' => [
            'required' => 'Seviye alanı zorunludur.',
            'integer' => 'Seviye bir tam sayı olmalıdır.',
        ],
        'country' => [
            'required' => 'Ülke alanı zorunludur.',
            // 'exists' => 'Seçilen ülke geçersiz.',
        ],
        'state' => [
            'required' => 'Eyalet alanı zorunludur.',
            // 'exists' => 'Seçilen eyalet geçersiz.',
        ],
    ],

    // Product
    'subscription_form' => [
        'name' => [
            'required' => 'İsim alanı zorunludur.',
        ],
        'subscription' => [
            'required' => 'Abonelik alanı zorunludur.',
        ],
        'regular_price' => [
            'required' => 'Normal fiyat alanı zorunludur.',
            'numeric' => 'Normal fiyat bir sayı olmalıdır.',
        ],
        'selling_price' => [
            'required' => 'Satış fiyatı alanı zorunludur.',
            'numeric' => 'Satış fiyatı bir sayı olmalıdır.',
        ],
        'products' => [
            'required' => 'Ürünler alanı zorunludur.',
        ],
    ],

    'bundle' => [
        'name' => [
            'required' => 'İsim alanı zorunludur.',
        ],
        'items' => [
            'required' => 'Her bir ürün zorunludur.',
        ],
    ],

    'group' => [
        'name' => [
            'required' => 'İsim zorunludur.',
        ],
        'features' => [
            'name' => [
                'required' => 'Tüm Özellikler Alanı Zorunludur.',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => 'Fiyat zorunludur.',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => 'Değer zorunludur.',
            ],
        ],
        'type' => [
            'required_with' => 'Tür zorunludur.',
        ],
        'title' => [
            'required_with' => 'Başlık zorunludur.',
        ],
    ],

    'product' => [
        'name' => [
            'required' => 'İsim alanı zorunludur.',
        ],
        'type' => [
            'required' => 'Tür alanı zorunludur.',
        ],
        'group' => [
            'required' => 'Grup alanı zorunludur.',
        ],
        'subscription' => [
            'required' => 'Abonelik alanı zorunludur.',
        ],
        'currency' => [
            'required' => 'Para birimi alanı zorunludur.',
        ],
        // 'price' => [
        //     'required' => 'Fiyat alanı zorunludur.',
        // ],
        'file' => [
            'required_without_all' => 'Dosya alanı, github_owner veya github_repository sağlanmadığı takdirde zorunludur.',
            'mimes' => 'Dosya bir zip dosyası olmalıdır.',
        ],
        'image' => [
            'required_without_all' => 'Resim alanı, github_owner veya github_repository sağlanmadığı takdirde zorunludur.',
            'mimes' => 'Resim bir PNG dosyası olmalıdır.',
        ],
        'github_owner' => [
            'required_without_all' => 'GitHub sahibi alanı, dosya veya resim sağlanmadığı takdirde zorunludur.',
        ],
        'github_repository' => [
            'required_without_all' => 'GitHub deposu alanı, dosya veya resim sağlanmadığı takdirde zorunludur.',
            'required_if' => 'GitHub deposu alanı, tür 2 ise zorunludur.',
        ],
    ],
    // User
    'users' => [
        'first_name' => [
            'required' => 'İsim alanı zorunludur.',
        ],
        'last_name' => [
            'required' => 'Soyisim alanı zorunludur.',
        ],
        'company' => [
            'required' => 'Şirket alanı zorunludur.',
        ],
        'email' => [
            'required' => 'E-posta alanı zorunludur.',
            'email' => 'E-posta geçerli bir e-posta adresi olmalıdır.',
            'unique' => 'Bu e-posta adresi zaten alınmış.',
        ],
        'address' => [
            'required' => 'Adres alanı zorunludur.',
        ],
        'mobile' => [
            'required' => 'Cep telefonu alanı zorunludur.',
        ],
        'country' => [
            'required' => 'Ülke alanı zorunludur.',
            'exists' => 'Seçilen ülke geçersiz.',
        ],
        'state' => [
            'required_if' => 'Ülke Hindistan olduğunda, eyalet alanı zorunludur.',
        ],
        'timezone_id' => [
            'required' => 'Zaman dilimi alanı zorunludur.',
        ],
        'user_name' => [
            'required' => 'Kullanıcı adı alanı zorunludur.',
            'unique' => 'Bu kullanıcı adı zaten alınmış.',
        ],
        'zip' => [
            'regex' => 'Eyalet alanı, ülke Hindistan olduğunda zorunludur.',
        ],
    ],

    'profile_form' => [
        'first_name' => [
            'required' => 'İsim zorunludur.',
            'min' => 'İsim en az :min karakter olmalıdır.',
            'max' => 'İsim :max karakterden fazla olmamalıdır.',
        ],
        'last_name' => [
            'required' => 'Soyisim zorunludur.',
            'max' => 'Soyisim :max karakterden fazla olmamalıdır.',
        ],
        'company' => [
            'required' => 'Şirket adı zorunludur.',
            'max' => 'Şirket adı :max karakterden fazla olmamalıdır.',
        ],
        'email' => [
            'required' => 'E-posta zorunludur.',
            'email' => 'Geçerli bir e-posta adresi girin.',
            'unique' => 'Bu e-posta adresi zaten alınmış. Lütfen farklı bir e-posta seçin.',
        ],
        'mobile' => [
            'required' => 'Cep telefonu zorunludur.',
            'regex' => 'Geçerli bir cep telefonu numarası girin.',
            'min' => 'Cep telefonu numarası en az :min karakter olmalıdır.',
            'max' => 'Cep telefonu numarası :max karakterden fazla olmamalıdır.',
        ],
        'address' => [
            'required' => 'Adres zorunludur.',
        ],
        'user_name' => [
            'required' => 'Kullanıcı adı zorunludur.',
            'unique' => 'Bu kullanıcı adı zaten alınmış.',
        ],
        'timezone_id' => [
            'required' => 'Zaman dilimi zorunludur.',
        ],
        'country' => [
            'required' => 'Ülke zorunludur.',
            'exists' => 'Seçilen ülke geçersiz.',
        ],
        'state' => [
            'required_if' => 'Ülke Hindistan olduğunda, eyalet alanı zorunludur.',
        ],
        'old_password' => [
            'required' => 'Eski şifre zorunludur.',
            'min' => 'Eski şifre en az :min karakter olmalıdır.',
        ],
        'new_password' => [
            'required' => 'Yeni şifre zorunludur.',
            'different' => 'Yeni şifre eski şifreden farklı olmalıdır.',
        ],
        'confirm_password' => [
            'required' => 'Şifreyi doğrulamak zorunludur.',
            'same' => 'Şifreyi doğrulamak, yeni şifreyle aynı olmalıdır.',
        ],
        'terms' => [
            'required' => 'Şartları kabul etmeniz gerekmektedir.',
        ],
    ],
    'password' => [
        'required' => 'Şifre zorunludur.',
    ],
    'password_confirmation' => [
        'required' => 'Şifre onayı zorunludur.',
        'same' => 'Şifreler eşleşmiyor.',
    ],
    'mobile_code' => [
        'required' => 'Ülke kodunu girin (mobil).',
    ],

    // Invoice form
    'invoice' => [
        'user' => [
            'required' => 'Müşteri alanı zorunludur.',
        ],
        'date' => [
            'required' => 'Tarih alanı zorunludur.',
            'date' => 'Tarih geçerli bir tarih olmalıdır.',
        ],
        'domain' => [
            'regex' => 'Alan adı formatı geçersiz.',
        ],
        'plan' => [
            'required_if' => 'Abonelik alanı zorunludur.',
        ],
        'price' => [
            'required' => 'Fiyat alanı zorunludur.',
        ],
        'product' => [
            'required' => 'Ürün alanı zorunludur.',
        ],
    ],

    // LocalizedLicense form
    'domain_form' => [
        'domain' => [
            'required' => 'Alan adı alanı zorunludur.',
            'url' => 'Alan adı geçerli bir URL olmalıdır.',
        ],
    ],

    // Product Renewal form
    'product_renewal' => [
        'domain' => [
            'required' => 'Alan adı alanı zorunludur.',
            'no_http' => 'Alan adı "http" veya "https" içermemelidir.',
        ],
    ],

    // Language form
    'language' => [
        'required' => 'Dil alanı zorunludur.',
        'invalid' => 'Seçilen dil geçersiz.',
    ],

    // UpdateSroragePathRequest form
    'storage_path' => [
        'disk' => [
            'required' => 'Depolama diski alanı zorunludur.',
            'string' => 'Disk bir dize olmalıdır.',
        ],
        'path' => [
            'string' => 'Yol bir dize olmalıdır.',
            'nullable' => 'Yol alanı isteğe bağlıdır.',
        ],
    ],

    // ValidateSecretRequest form
    'validate_secret' => [
        'totp' => [
            'required' => 'Lütfen kodu girin.',
            'digits' => 'Lütfen geçerli bir 6 haneli kod girin.',
        ],
    ],

    // VerifyOtp form
    'verify_email' => [
        'required' => 'E-posta alanı zorunludur.',
        'email' => 'E-posta geçerli bir e-posta adresi olmalıdır.',
        'verify_email' => 'E-posta doğrulama başarısız oldu.', // Özel mesaj verify_email için
    ],

    'verify_country_code' => [
        'required' => 'Ülke kodu zorunludur.',
        'numeric' => 'Ülke kodu geçerli bir sayı olmalıdır.',
        'verify_country_code' => 'Ülke kodu doğrulaması başarısız oldu.', // Özel mesaj verify_country_code için
    ],

    'verify_number' => [
        'required' => 'Numara zorunludur.',
        'numeric' => 'Numara geçerli bir sayı olmalıdır.',
        'verify_number' => 'Numara doğrulaması başarısız oldu.', // Özel mesaj verify_number için
    ],

    'password_otp' => [
        'required' => 'Şifre alanı zorunludur.',
        'password' => 'Şifre yanlış.',
        'invalid' => 'Geçersiz şifre.',
    ],

    //AuthController file
    'auth_controller' => [
        'name_required' => 'Ad gereklidir.',
        'name_max' => 'Ad en fazla 255 karakter olabilir.',

        'email_required' => 'E-posta gereklidir.',
        'email_email' => 'Geçerli bir e-posta adresi girin.',
        'email_max' => 'E-posta en fazla 255 karakter olabilir.',
        'email_unique' => 'Bu e-posta zaten kayıtlı.',

        'password_required' => 'Şifre gereklidir.',
        'password_confirmed' => 'Şifre onayı eşleşmiyor.',
        'password_min' => 'Şifre en az 6 karakter olmalıdır.',
    ],

    'resend_otp' => [
        'eid_required' => 'EID alanı gereklidir.',
        'eid_string' => 'EID bir metin olmalıdır.',
        'type_required' => 'Tür alanı gereklidir.',
        'type_string' => 'Tür bir metin olmalıdır.',
        'type_in' => 'Seçilen tür geçersizdir.',
    ],

    'verify_otp' => [
        'eid_required' => 'Çalışan ID’si gereklidir.',
        'eid_string' => 'Çalışan ID’si bir metin olmalıdır.',
        'otp_required' => 'OTP gereklidir.',
        'otp_size' => 'OTP tam olarak 6 karakter olmalıdır.',
        'recaptcha_required' => 'Lütfen CAPTCHA’yı tamamlayın.',
        'recaptcha_size' => 'CAPTCHA cevabı geçersiz.',
    ],

    'company_validation' => [
        'company_required' => 'Şirket adı gereklidir.',
        'company_string' => 'Şirket bir metin olmalıdır.',
        'address_required' => 'Adres gereklidir.',
        'address_string' => 'Adres bir metin olmalıdır.',
    ],

    'token_validation' => [
        'token_required' => 'Token gereklidir.',
        'password_required' => 'Şifre alanı gereklidir.',
        'password_confirmed' => 'Şifre onayı eşleşmiyor.',
    ],

    'custom_email' => [
        'required' => 'E-posta alanı gereklidir.',
        'email' => 'Geçerli bir e-posta adresi girin.',
        'exists' => 'Bu e-posta bizimle kayıtlı değil.',
    ],

    'newsletterEmail' => [
        'required' => 'Bülten e-posta alanı gereklidir.',
        'email' => 'Lütfen geçerli bir bülten e-posta adresi girin.',
    ],

    'widget' => [
        'name_required' => 'Ad gereklidir.',
        'name_max' => 'Ad en fazla 50 karakter olabilir.',
        'publish_required' => 'Yayın durumu gereklidir.',
        'type_required' => 'Tür gereklidir.',
        'type_unique' => 'Bu tür zaten mevcut.',
    ],

    'payment' => [
        'payment_date_required' => 'Ödeme tarihi gereklidir.',
        'payment_method_required' => 'Ödeme yöntemi gereklidir.',
        'amount_required' => 'Miktar gereklidir.',
    ],

    'custom_date' => [
        'date_required' => 'Tarih alanı gereklidir.',
        'total_required' => 'Toplam alanı gereklidir.',
        'status_required' => 'Durum alanı gereklidir.',
    ],

    'plan_renewal' => [
        'plan_required' => 'Plan alanı gereklidir.',
        'payment_method_required' => 'Ödeme yöntemi alanı gereklidir.',
        'cost_required' => 'Maliyet alanı gereklidir.',
        'code_not_valid' => 'Promosyon kodu geçersiz.',
    ],

    'rate' => [
        'required' => 'Ortalama gereklidir.',
        'numeric' => 'Ortalama bir sayı olmalıdır.',
    ],

    'product_validate' => [
        'producttitle_required' => 'Ürün başlığı gereklidir.',
        'version_required' => 'Versiyon gereklidir.',
        'filename_required' => 'Lütfen bir dosya yükleyin.',
        'dependencies_required' => 'Bağımlılıklar alanı gereklidir.',
    ],
    'product_sku_unique' => 'Ürün SKU’su benzersiz olmalıdır.',
    'product_name_unique' => 'Ad benzersiz olmalıdır.',
    'product_show_agent_required' => 'Lütfen Sepet Sayfası Tercihinizi Seçin.',
    'product_controller' => [
        'name_required' => 'Ürün adı gereklidir.',
        'name_unique' => 'Ad benzersiz olmalıdır.',
        'type_required' => 'Ürün türü gereklidir.',
        'description_required' => 'Ürün açıklaması gereklidir.',
        'product_description_required' => 'Ürün detaylı açıklaması gereklidir.',
        'image_mimes' => 'Resim jpeg, png, jpg türlerinde bir dosya olmalıdır.',
        'image_max' => 'Resim en fazla 2048 kilobayt olabilir.',
        'product_sku_required' => 'Ürün SKU’su gereklidir.',
        'group_required' => 'Ürün grubu gereklidir.',
        'show_agent_required' => 'Sepet Sayfası Tercihinizi Seçin.',
    ],
    'current_domain_required' => 'Geçerli domain gereklidir.',
    'new_domain_required' => 'Yeni domain gereklidir.',
    'special_characters_not_allowed' => 'Domain adında özel karakterler yasaktır.',
    'orderno_required' => 'Sipariş numarası gereklidir.',
    'cloud_central_domain_required' => 'Cloud central domain gereklidir.',
    'cloud_cname_required' => 'Cloud CNAME gereklidir.',
    'cloud_tenant' => [
        'cloud_top_message_required' => 'Cloud üst mesajı gereklidir.',
        'cloud_label_field_required' => 'Cloud etiket alanı gereklidir.',
        'cloud_label_radio_required' => 'Cloud etiket radyo gereklidir.',
        'cloud_product_required' => 'Cloud ürünü gereklidir.',
        'cloud_free_plan_required' => 'Cloud ücretsiz plan gereklidir.',
        'cloud_product_key_required' => 'Cloud ürün anahtarı gereklidir.',
    ],
    'reg_till_after' => 'Kayıt son tarihi, kayıt başlangıç tarihinden sonra olmalıdır.',
    'extend_product' => [
        'title_required' => 'Başlık alanı gereklidir.',
        'version_required' => 'Versiyon alanı gereklidir.',
        'dependencies_required' => 'Bağımlılıklar alanı gereklidir.',
    ],
    'please_enter_recovery_code' => 'Lütfen kurtarma kodunu girin.',
    'social_login' => [
        'client_id_required' => 'Google, Github veya Linkedin için Client ID gereklidir.',
        'client_secret_required' => 'Google, Github veya Linkedin için Client Secret gereklidir.',
        'api_key_required' => 'Twitter için API Anahtarı gereklidir.',
        'api_secret_required' => 'Twitter için API Secret gereklidir.',
        'redirect_url_required' => 'Yönlendirme URL’si gereklidir.',
    ],
    'thirdparty_api' => [
        'app_name_required' => 'Uygulama adı gereklidir.',
        'app_key_required' => 'Uygulama anahtarı gereklidir.',
        'app_key_size' => 'Uygulama anahtarı tam olarak 32 karakter olmalıdır.',
        'app_secret_required' => 'Uygulama gizli anahtarı gereklidir.',
    ],

];
