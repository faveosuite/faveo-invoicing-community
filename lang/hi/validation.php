<?php

return [
    'accepted' => ':attribute को स्वीकार किया जाना चाहिए।',
    'accepted_if' => ':attribute को स्वीकार किया जाना चाहिए जब :other :value हो।',
    'active_url' => ':attribute एक वैध URL नहीं है।',
    'after' => ':attribute को :date के बाद की तारीख होनी चाहिए।',
    'after_or_equal' => ':attribute को :date के बाद या बराबर की तारीख होनी चाहिए।',
    'alpha' => ':attribute में केवल अक्षर होने चाहिए।',
    'alpha_dash' => ':attribute में केवल अक्षर, अंक, डैश और अंडरस्कोर हो सकते हैं।',
    'alpha_num' => ':attribute में केवल अक्षर और अंक होने चाहिए।',
    'array' => ':attribute एक ऐरे होना चाहिए।',
    'before' => ':attribute को :date से पहले की तारीख होनी चाहिए।',
    'before_or_equal' => ':attribute को :date से पहले या बराबर की तारीख होनी चाहिए।',
    'between' => [
        'array' => ':attribute में :min और :max आइटम्स होने चाहिए।',
        'file' => ':attribute का आकार :min और :max किलोबाइट्स के बीच होना चाहिए।',
        'numeric' => ':attribute :min और :max के बीच होना चाहिए।',
        'string' => ':attribute में :min और :max अक्षर होने चाहिए।',
    ],
    'boolean' => ':attribute फ़ील्ड को सही या गलत होना चाहिए।',
    'confirmed' => ':attribute पुष्टि मेल नहीं खाती।',
    'current_password' => 'पासवर्ड गलत है।',
    'date' => ':attribute एक वैध तारीख नहीं है।',
    'date_equals' => ':attribute को :date के बराबर की तारीख होनी चाहिए।',
    'date_format' => ':attribute :format स्वरूप से मेल नहीं खाता।',
    'declined' => ':attribute को अस्वीकृत किया जाना चाहिए।',
    'declined_if' => ':attribute को अस्वीकृत किया जाना चाहिए जब :other :value हो।',
    'different' => ':attribute और :other अलग होने चाहिए।',
    'digits' => ':attribute में :digits अंक होने चाहिए।',
    'digits_between' => ':attribute में :min और :max अंक होने चाहिए।',
    'dimensions' => ':attribute के चित्र का आकार अवैध है।',
    'distinct' => ':attribute फ़ील्ड में डुप्लिकेट मान है।',
    'doesnt_start_with' => ':attribute इनमें से किसी एक से शुरू नहीं हो सकता: :values।',
    'email' => ':attribute को एक वैध ईमेल पता होना चाहिए।',
    'ends_with' => ':attribute इनमें से किसी एक से समाप्त होना चाहिए: :values।',
    'enum' => 'चुना हुआ :attribute अवैध है।',
    'exists' => 'चुना हुआ :attribute अवैध है।',
    'file' => ':attribute एक फ़ाइल होना चाहिए।',
    'filled' => ':attribute फ़ील्ड में मान होना चाहिए।',
    'gt' => [
        'array' => ':attribute में :value से अधिक आइटम होने चाहिए।',
        'file' => ':attribute का आकार :value किलोबाइट्स से अधिक होना चाहिए।',
        'numeric' => ':attribute :value से अधिक होना चाहिए।',
        'string' => ':attribute में :value से अधिक अक्षर होने चाहिए।',
    ],
    'gte' => [
        'array' => ':attribute में :value या उससे अधिक आइटम होने चाहिए।',
        'file' => ':attribute का आकार :value किलोबाइट्स या उससे अधिक होना चाहिए।',
        'numeric' => ':attribute :value या उससे अधिक होना चाहिए।',
        'string' => ':attribute में :value या उससे अधिक अक्षर होने चाहिए।',
    ],
    'image' => ':attribute एक चित्र होना चाहिए।',
    'in' => 'चुना हुआ :attribute अवैध है।',
    'in_array' => ':attribute फ़ील्ड :other में मौजूद नहीं है।',
    'integer' => ':attribute एक पूर्णांक होना चाहिए।',
    'ip' => ':attribute को एक वैध IP पता होना चाहिए।',
    'ipv4' => ':attribute को एक वैध IPv4 पता होना चाहिए।',
    'ipv6' => ':attribute को एक वैध IPv6 पता होना चाहिए।',
    'json' => ':attribute को एक वैध JSON स्ट्रिंग होना चाहिए।',
    'lt' => [
        'array' => ':attribute में :value से कम आइटम होने चाहिए।',
        'file' => ':attribute का आकार :value किलोबाइट्स से कम होना चाहिए।',
        'numeric' => ':attribute :value से कम होना चाहिए।',
        'string' => ':attribute में :value से कम अक्षर होने चाहिए।',
    ],
    'lte' => [
        'array' => ':attribute में :value से अधिक आइटम नहीं होने चाहिए।',
        'file' => ':attribute का आकार :value किलोबाइट्स या उससे कम होना चाहिए।',
        'numeric' => ':attribute :value या उससे कम होना चाहिए।',
        'string' => ':attribute में :value या उससे कम अक्षर होने चाहिए।',
    ],
    'mac_address' => ':attribute को एक वैध MAC पता होना चाहिए।',
    'max' => [
        'array' => ':attribute में :max से अधिक आइटम नहीं होने चाहिए।',
        'file' => ':attribute का आकार :max किलोबाइट्स से अधिक नहीं होना चाहिए।',
        'numeric' => ':attribute :max से अधिक नहीं होना चाहिए।',
        'string' => ':attribute में :max से अधिक अक्षर नहीं होने चाहिए।',
    ],
    'mimes' => ':attribute को निम्नलिखित प्रकार की फ़ाइल होनी चाहिए: :values।',
    'mimetypes' => ':attribute को निम्नलिखित प्रकार की फ़ाइल होनी चाहिए: :values।',
    'min' => [
        'array' => ':attribute में कम से कम :min आइटम होने चाहिए।',
        'file' => ':attribute का आकार कम से कम :min किलोबाइट्स होना चाहिए।',
        'numeric' => ':attribute कम से कम :min होना चाहिए।',
        'string' => ':attribute में कम से कम :min अक्षर होने चाहिए।',
    ],
    'multiple_of' => ':attribute को :value का गुणांक होना चाहिए।',
    'not_in' => 'चुना हुआ :attribute अवैध है।',
    'not_regex' => ':attribute का स्वरूप अवैध है।',
    'numeric' => ':attribute एक संख्या होनी चाहिए।',
    'password' => [
        'letters' => ':attribute में कम से कम एक अक्षर होना चाहिए।',
        'mixed' => ':attribute में कम से कम एक अपरकेस और एक लोअरकेस अक्षर होना चाहिए।',
        'numbers' => ':attribute में कम से कम एक संख्या होना चाहिए।',
        'symbols' => ':attribute में कम से कम एक चिन्ह होना चाहिए।',
        'uncompromised' => ':attribute का उपयोग डेटा लीक में हुआ है। कृपया एक अलग :attribute चुनें।',
    ],
    'present' => ':attribute फ़ील्ड मौजूद होना चाहिए।',
    'prohibited' => ':attribute फ़ील्ड पर प्रतिबंध है।',
    'prohibited_if' => ':attribute फ़ील्ड पर प्रतिबंध है जब :other :value हो।',
    'prohibited_unless' => ':attribute फ़ील्ड पर प्रतिबंध है जब तक :other :values में से न हो।',
    'prohibits' => ':attribute फ़ील्ड :other को मौजूद होने से रोकता है।',
    'regex' => ':attribute का स्वरूप अवैध है।',
    'required' => ':attribute फ़ील्ड अनिवार्य है।',
    'required_array_keys' => ':attribute फ़ील्ड में :values के लिए प्रविष्टियां होनी चाहिए।',
    'required_if' => ':attribute फ़ील्ड तब आवश्यक है जब :other :value हो।',
    'required_unless' => ':attribute फ़ील्ड तब आवश्यक है जब तक :other :values में न हो।',
    'required_with' => ':attribute फ़ील्ड तब आवश्यक है जब :values मौजूद हो।',
    'required_with_all' => ':attribute फ़ील्ड तब आवश्यक है जब :values सभी मौजूद हो।',
    'required_without' => ':attribute फ़ील्ड तब आवश्यक है जब :values मौजूद न हो।',
    'required_without_all' => ':attribute फ़ील्ड तब आवश्यक है जब :values में से कोई भी मौजूद न हो।',
    'same' => ':attribute और :other मेल खाने चाहिए।',
    'size' => [
        'array' => ':attribute में :size आइटम होने चाहिए।',
        'file' => ':attribute का आकार :size किलोबाइट्स होना चाहिए।',
        'numeric' => ':attribute :size होना चाहिए।',
        'string' => ':attribute में :size अक्षर होने चाहिए।',
    ],
    'starts_with' => ':attribute को इनमें से किसी एक से शुरू होना चाहिए: :values।',
    'string' => ':attribute एक स्ट्रिंग होना चाहिए।',
    'timezone' => ':attribute एक वैध टाइमज़ोन होना चाहिए।',
    'unique' => ':attribute पहले ही लिया जा चुका है।',
    'uploaded' => ':attribute अपलोड करने में विफल रहा।',
    'url' => ':attribute को एक वैध URL होना चाहिए।',
    'uuid' => ':attribute को एक वैध UUID होना चाहिए।',
    'attributes' => [],
    'publish_date_required' => 'प्रकाशन तिथि अनिवार्य है',
    'price_numeric_value' => 'कीमत एक संख्यात्मक मान होना चाहिए',
    'quantity_integer_value' => 'मात्रा एक पूर्णांक मान होना चाहिए',
    'order_has_Expired' => 'आदेश समाप्त हो गया है',
    'expired' => 'समाप्त',
    /*
    Request file custom validation messages
    */

    //Common
    'settings_form' => [
        'company' => [
            'required' => 'कंपनी क्षेत्र अनिवार्य है।',
        ],
        'website' => [
            'url' => 'वेबसाइट एक वैध URL होना चाहिए।',
        ],
        'phone' => [
            'regex' => 'फोन नंबर का प्रारूप अवैध है।',
        ],
        'address' => [
            'required' => 'पता क्षेत्र अनिवार्य है।',
            'max' => 'पता 300 अक्षरों से अधिक नहीं हो सकता।',
        ],
        'logo' => [
            'mimes' => 'लोगो एक PNG फ़ाइल होनी चाहिए।',
        ],
        'driver' => [
            'required' => 'ड्राइवर क्षेत्र अनिवार्य है।',
        ],
        'port' => [
            'integer' => 'पोर्ट एक पूर्णांक होना चाहिए।',
        ],
        'email' => [
            'required' => 'ईमेल क्षेत्र अनिवार्य है।',
            'email' => 'ईमेल एक वैध ईमेल पता होना चाहिए।',
        ],
        'password' => [
            'required' => 'पासवर्ड क्षेत्र अनिवार्य है।',
        ],
        'error_email' => [
            'email' => 'त्रुटि ईमेल एक वैध ईमेल पता होना चाहिए।',
        ],
    ],

    'settings_forms' => [
        'company' => [
            'required' => 'कंपनी का नाम अनिवार्य है।',
            'max' => 'कंपनी का नाम 50 अक्षरों से अधिक नहीं हो सकता।',
        ],
        'company_email' => [
            'required' => 'कंपनी ईमेल अनिवार्य है।',
            'email' => 'कंपनी ईमेल एक वैध ईमेल पता होना चाहिए।',
        ],
        'title' => [
            'max' => 'शीर्षक 50 अक्षरों से अधिक नहीं हो सकता।',
        ],
        'website' => [
            'required' => 'वेबसाइट URL अनिवार्य है।',
            'url' => 'वेबसाइट एक वैध URL होना चाहिए।',
            'regex' => 'वेबसाइट का प्रारूप अवैध है।',
        ],
        'phone' => [
            'required' => 'फोन नंबर अनिवार्य है।',
        ],
        'address' => [
            'required' => 'पता अनिवार्य है।',
        ],
        'state' => [
            'required' => 'राज्य अनिवार्य है।',
        ],
        'country' => [
            'required' => 'देश अनिवार्य है।',
        ],
        'gstin' => [
            'max' => 'GSTIN 15 अक्षरों से अधिक नहीं हो सकता।',
        ],
        'default_currency' => [
            'required' => 'डिफ़ॉल्ट मुद्रा अनिवार्य है।',
        ],
        'admin_logo' => [
            'mimes' => 'प्रशासनिक लोगो एक jpeg, png, jpg प्रकार की फ़ाइल होनी चाहिए।',
            'max' => 'प्रशासनिक लोगो 2MB से अधिक नहीं हो सकता।',
        ],
        'fav_icon' => [
            'mimes' => 'फेव आइकन एक jpeg, png, jpg प्रकार की फ़ाइल होनी चाहिए।',
            'max' => 'फेव आइकन 2MB से अधिक नहीं हो सकता।',
        ],
        'logo' => [
            'mimes' => 'लोगो एक jpeg, png, jpg प्रकार की फ़ाइल होनी चाहिए।',
            'max' => 'लोगो 2MB से अधिक नहीं हो सकता।',
        ],
    ],

    'social_media_form' => [
        'name' => [
            'required' => 'नाम क्षेत्र अनिवार्य है।',
            'unique' => 'यह नाम पहले से मौजूद है।',
            'max' => 'नाम 50 अक्षरों से अधिक नहीं हो सकता।',
        ],
        'link' => [
            'required' => 'लिंक क्षेत्र अनिवार्य है।',
            'url' => 'लिंक एक वैध URL होना चाहिए।',
            'regex' => 'लिंक का प्रारूप अवैध है।',
        ],
    ],

    //Email
    'custom' => [
        'password' => [
            'required_if' => 'चयनित मेल ड्राइवर के लिए पासवर्ड क्षेत्र अनिवार्य है।',
        ],
        'port' => [
            'required_if' => 'SMTP के लिए पोर्ट क्षेत्र अनिवार्य है।',
        ],
        'encryption' => [
            'required_if' => 'SMTP के लिए एन्क्रिप्शन क्षेत्र अनिवार्य है।',
        ],
        'host' => [
            'required_if' => 'SMTP के लिए होस्ट क्षेत्र अनिवार्य है।',
        ],
        'secret' => [
            'required_if' => 'चयनित मेल ड्राइवर के लिए सीक्रेट क्षेत्र अनिवार्य है।',
        ],
        'domain' => [
            'required_if' => 'Mailgun के लिए डोमेन क्षेत्र अनिवार्य है।',
        ],
        'key' => [
            'required_if' => 'SES के लिए कुंजी क्षेत्र अनिवार्य है।',
        ],
        'region' => [
            'required_if' => 'SES के लिए क्षेत्र क्षेत्र अनिवार्य है।',
        ],
        'email' => [
            'required_if' => 'चयनित मेल ड्राइवर के लिए ईमेल क्षेत्र अनिवार्य है।',
            'required' => 'ईमेल क्षेत्र अनिवार्य है।',
            'email' => 'कृपया एक वैध ईमेल पता दर्ज करें।',
            'not_matching' => 'ईमेल डोमेन को वर्तमान साइट डोमेन से मेल खाना चाहिए।',
        ],
        'driver' => [
            'required' => 'ड्राइवर क्षेत्र अनिवार्य है।',
        ],
    ],
    'customer_form' => [
        'first_name' => [
            'required' => 'पहला नाम क्षेत्र अनिवार्य है।',
        ],
        'last_name' => [
            'required' => 'अंतिम नाम क्षेत्र अनिवार्य है।',
        ],
        'company' => [
            'required' => 'कंपनी क्षेत्र अनिवार्य है।',
        ],
        'mobile' => [
            'regex' => 'मोबाइल नंबर का प्रारूप अवैध है।',
        ],
        'address' => [
            'required' => 'पता क्षेत्र अनिवार्य है।',
        ],
        'zip' => [
            'required' => 'ज़िप कोड क्षेत्र अनिवार्य है।',
            'min' => 'ज़िप कोड में कम से कम 5 अंक होने चाहिए।',
            'numeric' => 'ज़िप कोड केवल संख्यात्मक होना चाहिए।',
        ],
        'email' => [
            'required' => 'ईमेल क्षेत्र अनिवार्य है।',
            'email' => 'ईमेल एक वैध ईमेल पता होना चाहिए।',
            'unique' => 'यह ईमेल पहले से लिया गया है।',
        ],
    ],

    'contact_request' => [
        'conName' => 'नाम क्षेत्र अनिवार्य है।',
        'email' => 'ईमेल क्षेत्र अनिवार्य है।',
        'conmessage' => 'संदेश क्षेत्र अनिवार्य है।',
        'Mobile' => 'मोबाइल क्षेत्र अनिवार्य है।',
        'country_code' => 'मोबाइल क्षेत्र अनिवार्य है।',
        'demoname' => 'नाम क्षेत्र अनिवार्य है।',
        'demomessage' => 'संदेश क्षेत्र अनिवार्य है।',
        'demoemail' => 'ईमेल क्षेत्र अनिवार्य है।',
        'congg-recaptcha-response-1.required' => 'रोबोट सत्यापन विफल हो गया। कृपया पुनः प्रयास करें।',
        'demo-recaptcha-response-1.required' => 'रोबोट सत्यापन विफल हो गया। कृपया पुनः प्रयास करें।',
    ],

    'frontend_pages' => [
        'name' => [
            'required' => 'नाम क्षेत्र अनिवार्य है।',
            'unique' => 'यह नाम पहले से मौजूद है।',
            'max' => 'नाम 20 अक्षरों से अधिक नहीं हो सकता।',
            'regex' => 'नाम में केवल अक्षर और रिक्त स्थान हो सकते हैं।',
        ],
        'publish' => [
            'required' => 'प्रकाशित करने का क्षेत्र अनिवार्य है।',
        ],
        'slug' => [
            'required' => 'स्लग क्षेत्र अनिवार्य है।',
        ],
        'url' => [
            'required' => 'URL क्षेत्र अनिवार्य है।',
            'url' => 'URL एक वैध लिंक होना चाहिए।',
            'regex' => 'URL प्रारूप अवैध है।',
        ],
        'content' => [
            'required' => 'सामग्री क्षेत्र अनिवार्य है।',
        ],
        'created_at' => [
            'required' => 'निर्मित तिथि क्षेत्र अनिवार्य है।',
        ],
    ],

    //Order form
    'order_form' => [
        'client' => [
            'required' => 'क्लाइंट क्षेत्र अनिवार्य है।',
        ],
        'payment_method' => [
            'required' => 'भुगतान विधि क्षेत्र अनिवार्य है।',
        ],
        'promotion_code' => [
            'required' => 'प्रोमोशन कोड क्षेत्र अनिवार्य है।',
        ],
        'order_status' => [
            'required' => 'ऑर्डर स्थिति क्षेत्र अनिवार्य है।',
        ],
        'product' => [
            'required' => 'उत्पाद क्षेत्र अनिवार्य है।',
        ],
        'subscription' => [
            'required' => 'सदस्यता क्षेत्र अनिवार्य है।',
        ],
        'price_override' => [
            'numeric' => 'मूल्य ओवरराइड एक संख्या होनी चाहिए।',
        ],
        'qty' => [
            'integer' => 'मात्रा एक पूर्णांक होनी चाहिए।',
        ],
    ],

    //Payment form
    'coupon_form' => [
        'code' => [
            'required' => 'कूपन कोड क्षेत्र अनिवार्य है।',
            'string' => 'कूपन कोड एक स्ट्रिंग होनी चाहिए।',
            'max' => 'कूपन कोड 255 अक्षरों से अधिक नहीं हो सकता।',
        ],
        'type' => [
            'required' => 'प्रकार क्षेत्र अनिवार्य है।',
            'in' => 'अमान्य प्रकार। अनुमत मान: प्रतिशत, अन्य_प्रकार।',
        ],
        'applied' => [
            'required' => 'उत्पाद के लिए लागू क्षेत्र अनिवार्य है।',
            'date' => 'उत्पाद के लिए लागू क्षेत्र एक वैध तिथि होनी चाहिए।',
        ],
        'uses' => [
            'required' => 'उपयोग क्षेत्र अनिवार्य है।',
            'numeric' => 'उपयोग क्षेत्र एक संख्या होनी चाहिए।',
            'min' => 'उपयोग क्षेत्र कम से कम :min होना चाहिए।',
        ],
        'start' => [
            'required' => 'प्रारंभ क्षेत्र अनिवार्य है।',
            'date' => 'प्रारंभ क्षेत्र एक वैध तिथि होनी चाहिए।',
        ],
        'expiry' => [
            'required' => 'समाप्ति क्षेत्र अनिवार्य है।',
            'date' => 'समाप्ति क्षेत्र एक वैध तिथि होनी चाहिए।',
            'after' => 'समाप्ति तिथि प्रारंभ तिथि के बाद होनी चाहिए।',
        ],
        'value' => [
            'required' => 'छूट मूल्य क्षेत्र अनिवार्य है।',
            'numeric' => 'छूट मूल्य क्षेत्र एक संख्या होनी चाहिए।',
            'between' => 'यदि प्रकार प्रतिशत है, तो छूट मूल्य क्षेत्र :min और :max के बीच होना चाहिए।',
        ],
    ],

    'tax_form' => [
        'name' => [
            'required' => 'नाम क्षेत्र अनिवार्य है।',
        ],
        'rate' => [
            'required' => 'दर क्षेत्र अनिवार्य है।',
            'numeric' => 'दर एक संख्या होनी चाहिए।',
        ],
        'level' => [
            'required' => 'स्तर क्षेत्र अनिवार्य है।',
            'integer' => 'स्तर एक पूर्णांक होना चाहिए।',
        ],
        'country' => [
            'required' => 'देश क्षेत्र अनिवार्य है।',
            // 'exists' => 'चयनित देश अमान्य है।',
        ],
        'state' => [
            'required' => 'राज्य क्षेत्र अनिवार्य है।',
            // 'exists' => 'चयनित राज्य अमान्य है।',
        ],
    ],
    //Product
    'subscription_form' => [
        'name' => [
            'required' => 'नाम क्षेत्र अनिवार्य है।',
        ],
        'subscription' => [
            'required' => 'सदस्यता क्षेत्र अनिवार्य है।',
        ],
        'regular_price' => [
            'required' => 'नियमित मूल्य क्षेत्र अनिवार्य है।',
            'numeric' => 'नियमित मूल्य एक संख्या होनी चाहिए।',
        ],
        'selling_price' => [
            'required' => 'बिक्री मूल्य क्षेत्र अनिवार्य है।',
            'numeric' => 'बिक्री मूल्य एक संख्या होनी चाहिए।',
        ],
        'products' => [
            'required' => 'उत्पाद क्षेत्र अनिवार्य है।',
        ],
    ],

    'bundle' => [
        'name' => [
            'required' => 'नाम क्षेत्र अनिवार्य है।',
        ],
        'items' => [
            'required' => 'प्रत्येक आइटम आवश्यक है।',
        ],
    ],

    'group' => [
        'name' => [
            'required' => 'नाम आवश्यक है।',
        ],
        'features' => [
            'name' => [
                'required' => 'सभी सुविधाएँ क्षेत्र आवश्यक हैं।',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => 'मूल्य आवश्यक है।',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => 'मान आवश्यक है।',
            ],
        ],
        'type' => [
            'required_with' => 'प्रकार आवश्यक है।',
        ],
        'title' => [
            'required_with' => 'शीर्षक आवश्यक है।',
        ],
    ],

    'product' => [
        'name' => [
            'required' => 'नाम क्षेत्र अनिवार्य है।',
        ],
        'type' => [
            'required' => 'प्रकार क्षेत्र अनिवार्य है।',
        ],
        'group' => [
            'required' => 'समूह क्षेत्र अनिवार्य है।',
        ],
        'subscription' => [
            'required' => 'सदस्यता क्षेत्र अनिवार्य है।',
        ],
        'currency' => [
            'required' => 'मुद्रा क्षेत्र अनिवार्य है।',
        ],
        // 'price' => [
        //     'required' => 'मूल्य क्षेत्र अनिवार्य है।',
        // ],
        'file' => [
            'required_without_all' => 'फ़ाइल क्षेत्र तब अनिवार्य है जब github_owner या github_repository में से कोई भी प्रदान नहीं किया गया हो।',
            'mimes' => 'फ़ाइल एक zip फ़ाइल होनी चाहिए।',
        ],
        'image' => [
            'required_without_all' => 'चित्र क्षेत्र तब अनिवार्य है जब github_owner या github_repository में से कोई भी प्रदान नहीं किया गया हो।',
            'mimes' => 'चित्र एक PNG फ़ाइल होनी चाहिए।',
        ],
        'github_owner' => [
            'required_without_all' => 'GitHub मालिक क्षेत्र तब अनिवार्य है जब फ़ाइल या चित्र में से कोई भी प्रदान नहीं किया गया हो।',
        ],
        'github_repository' => [
            'required_without_all' => 'GitHub रिपॉजिटरी क्षेत्र तब अनिवार्य है जब फ़ाइल या चित्र में से कोई भी प्रदान नहीं किया गया हो।',
            'required_if' => 'GitHub रिपॉजिटरी क्षेत्र तब अनिवार्य है जब प्रकार 2 हो।',
        ],
    ],

    //User
    'users' => [
        'first_name' => [
            'required' => 'पहला नाम क्षेत्र अनिवार्य है।',
        ],
        'last_name' => [
            'required' => 'अंतिम नाम क्षेत्र अनिवार्य है।',
        ],
        'company' => [
            'required' => 'कंपनी क्षेत्र अनिवार्य है।',
        ],
        'email' => [
            'required' => 'ईमेल क्षेत्र अनिवार्य है।',
            'email' => 'ईमेल एक वैध ईमेल पता होना चाहिए।',
            'unique' => 'यह ईमेल पहले से लिया गया है।',
        ],
        'address' => [
            'required' => 'पता क्षेत्र अनिवार्य है।',
        ],
        'mobile' => [
            'required' => 'मोबाइल क्षेत्र अनिवार्य है।',
        ],
        'country' => [
            'required' => 'देश क्षेत्र अनिवार्य है।',
            'exists' => 'चयनित देश अमान्य है।',
        ],
        'state' => [
            'required_if' => 'जब देश भारत हो, तो राज्य क्षेत्र अनिवार्य है।',
        ],
        'timezone_id' => [
            'required' => 'समय क्षेत्र क्षेत्र अनिवार्य है।',
        ],
        'user_name' => [
            'required' => 'उपयोगकर्ता नाम क्षेत्र अनिवार्य है।',
            'unique' => 'यह उपयोगकर्ता नाम पहले से लिया गया है।',
        ],
        'zip' => [
            'regex' => 'राज्य क्षेत्र तब अनिवार्य है जब देश भारत हो।',
        ],
    ],
    'profile_form' => [
        'first_name' => [
            'required' => 'पहला नाम आवश्यक है।',
            'min' => 'पहला नाम कम से कम :min अक्षरों का होना चाहिए।',
            'max' => 'पहला नाम :max अक्षरों से अधिक नहीं हो सकता।',
        ],
        'last_name' => [
            'required' => 'अंतिम नाम आवश्यक है।',
            'max' => 'अंतिम नाम :max अक्षरों से अधिक नहीं हो सकता।',
        ],
        'company' => [
            'required' => 'कंपनी का नाम आवश्यक है।',
            'max' => 'कंपनी का नाम :max अक्षरों से अधिक नहीं हो सकता।',
        ],
        'email' => [
            'required' => 'ईमेल आवश्यक है।',
            'email' => 'एक वैध ईमेल पता दर्ज करें।',
            'unique' => 'यह ईमेल पता पहले से लिया जा चुका है। कृपया एक अलग ईमेल पता चुनें।',
        ],
        'mobile' => [
            'required' => 'मोबाइल नंबर आवश्यक है।',
            'regex' => 'एक वैध मोबाइल नंबर दर्ज करें।',
            'min' => 'मोबाइल नंबर कम से कम :min अंकों का होना चाहिए।',
            'max' => 'मोबाइल नंबर :max अंकों से अधिक नहीं हो सकता।',
        ],
        'address' => [
            'required' => 'पता आवश्यक है।',
        ],
        'user_name' => [
            'required' => 'उपयोगकर्ता नाम आवश्यक है।',
            'unique' => 'यह उपयोगकर्ता नाम पहले से लिया गया है।',
        ],
        'timezone_id' => [
            'required' => 'समय क्षेत्र आवश्यक है।',
        ],
        'country' => [
            'required' => 'देश आवश्यक है।',
            'exists' => 'चयनित देश अमान्य है।',
        ],
        'state' => [
            'required_if' => 'जब देश भारत हो, तो राज्य क्षेत्र आवश्यक है।',
        ],
        'old_password' => [
            'required' => 'पुराना पासवर्ड आवश्यक है।',
            'min' => 'पुराना पासवर्ड कम से कम :min अंकों का होना चाहिए।',
        ],
        'new_password' => [
            'required' => 'नया पासवर्ड आवश्यक है।',
            'different' => 'नया पासवर्ड पुराने पासवर्ड से अलग होना चाहिए।',
        ],
        'confirm_password' => [
            'required' => 'पासवर्ड की पुष्टि आवश्यक है।',
            'same' => 'पासवर्ड की पुष्टि नए पासवर्ड से मेल खानी चाहिए।',
        ],
        'terms' => [
            'required' => 'आपको शर्तें स्वीकार करनी होंगी।',
        ],
        'password' => [
            'required' => 'पासवर्ड आवश्यक है।',
        ],
        'password_confirmation' => [
            'required' => 'पासवर्ड की पुष्टि आवश्यक है।',
            'same' => 'पासवर्ड मेल नहीं खाते।',
        ],
        'mobile_code' => [
            'required' => 'देश कोड (मोबाइल) दर्ज करें।',
        ],
    ],

    //Invoice form
    'invoice' => [
        'user' => [
            'required' => 'क्लाइंट्स क्षेत्र आवश्यक है।',
        ],
        'date' => [
            'required' => 'तारीख क्षेत्र आवश्यक है।',
            'date' => 'तारीख एक वैध तारीख होनी चाहिए।',
        ],
        'domain' => [
            'regex' => 'डोमेन प्रारूप अमान्य है।',
        ],
        'plan' => [
            'required_if' => 'सदस्यता क्षेत्र आवश्यक है।',
        ],
        'price' => [
            'required' => 'मूल्य क्षेत्र आवश्यक है।',
        ],
        'product' => [
            'required' => 'उत्पाद क्षेत्र आवश्यक है।',
        ],
    ],

    //LocalizedLicense form
    'domain_form' => [
        'domain' => [
            'required' => 'डोमेन क्षेत्र आवश्यक है।',
            'url' => 'डोमेन एक वैध URL होना चाहिए।',
        ],
    ],

    //Product Renewal form
    'product_renewal' => [
        'domain' => [
            'required' => 'डोमेन क्षेत्र आवश्यक है।',
            'no_http' => 'डोमेन में "http" या "https" नहीं होना चाहिए।',
        ],
    ],

    //Language form
    'language' => [
        'required' => 'भाषा क्षेत्र आवश्यक है।',
        'invalid' => 'चयनित भाषा अमान्य है।',
    ],

    //UpdateSroragePathRequest form
    'storage_path' => [
        'disk' => [
            'required' => 'स्टोरेज डिस्क क्षेत्र आवश्यक है।',
            'string' => 'डिस्क एक स्ट्रिंग होनी चाहिए।',
        ],
        'path' => [
            'string' => 'पथ एक स्ट्रिंग होनी चाहिए।',
            'nullable' => 'पथ क्षेत्र वैकल्पिक है।',
        ],
    ],

    //ValidateSecretRequest form
    'validate_secret' => [
        'totp' => [
            'required' => 'कृपया कोड दर्ज करें',
            'digits' => 'कृपया 6 अंकों का वैध कोड दर्ज करें',
        ],
    ],

    //VerifyOtp form
    'verify_email' => [
        'required' => 'ईमेल क्षेत्र आवश्यक है।',
        'email' => 'ईमेल एक वैध ईमेल पता होना चाहिए।',
        'verify_email' => 'ईमेल सत्यापन विफल हो गया।',
    ],

    'verify_country_code' => [
        'required' => 'देश कोड आवश्यक है।',
        'numeric' => 'देश कोड एक वैध संख्या होनी चाहिए।',
        'verify_country_code' => 'देश कोड सत्यापन विफल हो गया।',
    ],

    'verify_number' => [
        'required' => 'संख्या आवश्यक है।',
        'numeric' => 'संख्या एक वैध संख्या होनी चाहिए।',
        'verify_number' => 'संख्या सत्यापन विफल हो गया।',
    ],

    'password_otp' => [
        'required' => 'पासवर्ड क्षेत्र आवश्यक है।',
        'password' => 'पासवर्ड गलत है।',
        'invalid' => 'अमान्य पासवर्ड।',
    ],

    // AuthController file
    'auth_controller' => [
        'name_required' => 'नाम आवश्यक है।',
        'name_max' => 'नाम 255 अक्षरों से अधिक नहीं हो सकता।',

        'email_required' => 'ईमेल आवश्यक है।',
        'email_email' => 'कृपया एक वैध ईमेल पता दर्ज करें।',
        'email_max' => 'ईमेल 255 अक्षरों से अधिक नहीं हो सकता।',
        'email_unique' => 'यह ईमेल पहले ही पंजीकृत है।',

        'password_required' => 'पासवर्ड आवश्यक है।',
        'password_confirmed' => 'पासवर्ड पुष्टि मेल नहीं खाती।',
        'password_min' => 'पासवर्ड कम से कम 6 अक्षरों का होना चाहिए।',
    ],

    'resend_otp' => [
        'eid_required' => 'EID फ़ील्ड आवश्यक है।',
        'eid_string' => 'EID एक स्ट्रिंग होना चाहिए।',
        'type_required' => 'प्रकार फ़ील्ड आवश्यक है।',
        'type_string' => 'प्रकार एक स्ट्रिंग होना चाहिए।',
        'type_in' => 'चुना गया प्रकार अवैध है।',
    ],

    'verify_otp' => [
        'eid_required' => 'कर्मचारी ID आवश्यक है।',
        'eid_string' => 'कर्मचारी ID एक स्ट्रिंग होना चाहिए।',
        'otp_required' => 'OTP आवश्यक है।',
        'otp_size' => 'OTP को ठीक 6 अक्षरों का होना चाहिए।',
        'recaptcha_required' => 'कृपया CAPTCHA पूरा करें।',
        'recaptcha_size' => 'CAPTCHA प्रतिक्रिया अवैध है।',
    ],

    'company_validation' => [
        'company_required' => 'कंपनी का नाम आवश्यक है।',
        'company_string' => 'कंपनी एक टेक्स्ट होना चाहिए।',
        'address_required' => 'पता आवश्यक है।',
        'address_string' => 'पता एक टेक्स्ट होना चाहिए।',
    ],

    'token_validation' => [
        'token_required' => 'टोकन आवश्यक है।',
        'password_required' => 'पासवर्ड फ़ील्ड आवश्यक है।',
        'password_confirmed' => 'पासवर्ड पुष्टि मेल नहीं खाती।',
    ],

    'custom_email' => [
        'required' => 'ईमेल फ़ील्ड आवश्यक है।',
        'email' => 'कृपया एक वैध ईमेल पता दर्ज करें।',
        'exists' => 'यह ईमेल हमारे साथ पंजीकृत नहीं है।',
    ],

    'newsletterEmail' => [
        'required' => 'न्यूज़लेटर ईमेल आवश्यक है।',
        'email' => 'न्यूज़लेटर के लिए एक वैध ईमेल पता दर्ज करें।',
    ],

    'widget' => [
        'name_required' => 'नाम आवश्यक है।',
        'name_max' => 'नाम 50 अक्षरों से अधिक नहीं हो सकता।',
        'publish_required' => 'प्रकाशन स्थिति आवश्यक है।',
        'type_required' => 'प्रकार आवश्यक है।',
        'type_unique' => 'यह प्रकार पहले से मौजूद है।',
    ],

    'payment' => [
        'payment_date_required' => 'भुगतान तिथि आवश्यक है।',
        'payment_method_required' => 'भुगतान विधि आवश्यक है।',
        'amount_required' => 'राशि आवश्यक है।',
    ],

    'custom_date' => [
        'date_required' => 'तिथि फ़ील्ड आवश्यक है।',
        'total_required' => 'कुल फ़ील्ड आवश्यक है।',
        'status_required' => 'स्थिति फ़ील्ड आवश्यक है।',
    ],

    'plan_renewal' => [
        'plan_required' => 'योजना फ़ील्ड आवश्यक है।',
        'payment_method_required' => 'भुगतान विधि फ़ील्ड आवश्यक है।',
        'cost_required' => 'लागत फ़ील्ड आवश्यक है।',
        'code_not_valid' => 'प्रमोशन कोड अवैध है।',
    ],

    'rate' => [
        'required' => 'दर आवश्यक है।',
        'numeric' => 'दर एक संख्या होनी चाहिए।',
    ],

    'product_validate' => [
        'producttitle_required' => 'उत्पाद का शीर्षक आवश्यक है।',
        'version_required' => 'संस्करण आवश्यक है।',
        'filename_required' => 'कृपया एक फ़ाइल अपलोड करें।',
        'dependencies_required' => 'निर्भरता फ़ील्ड आवश्यक है।',
    ],

    'product_sku_unique' => 'उत्पाद SKU अद्वितीय होना चाहिए',
    'product_name_unique' => 'नाम अद्वितीय होना चाहिए',
    'product_show_agent_required' => 'अपने कार्ट पेज प्राथमिकता का चयन करें',
    'product_controller' => [
        'name_required' => 'उत्पाद का नाम आवश्यक है।',
        'name_unique' => 'नाम अद्वितीय होना चाहिए।',
        'type_required' => 'उत्पाद प्रकार आवश्यक है।',
        'description_required' => 'उत्पाद का विवरण आवश्यक है।',
        'product_description_required' => 'विस्तृत उत्पाद विवरण आवश्यक है।',
        'image_mimes' => 'चित्र को jpeg, png, jpg प्रकार की फ़ाइल होनी चाहिए।',
        'image_max' => 'चित्र का आकार 2048 किलोबाइट से अधिक नहीं हो सकता।',
        'product_sku_required' => 'उत्पाद SKU आवश्यक है।',
        'group_required' => 'उत्पाद समूह आवश्यक है।',
        'show_agent_required' => 'अपने कार्ट पेज प्राथमिकता का चयन करें।',
    ],

    'current_domain_required' => 'वर्तमान डोमेन आवश्यक है।',
    'new_domain_required' => 'नया डोमेन आवश्यक है।',
    'special_characters_not_allowed' => 'डोमेन नाम में विशेष अक्षरों की अनुमति नहीं है।',
    'orderno_required' => 'आदेश संख्या आवश्यक है।',
    'cloud_central_domain_required' => 'क्लाउड केंद्रीय डोमेन आवश्यक है।',
    'cloud_cname_required' => 'क्लाउड CNAME आवश्यक है।',
    'cloud_tenant' => [
        'cloud_top_message_required' => 'क्लाउड शीर्ष संदेश आवश्यक है।',
        'cloud_label_field_required' => 'क्लाउड लेबल फ़ील्ड आवश्यक है।',
        'cloud_label_radio_required' => 'क्लाउड लेबल रेडियो आवश्यक है।',
        'cloud_product_required' => 'क्लाउड उत्पाद आवश्यक है।',
        'cloud_free_plan_required' => 'क्लाउड मुफ्त योजना आवश्यक है।',
        'cloud_product_key_required' => 'क्लाउड उत्पाद कुंजी आवश्यक है।',
    ],
    'reg_till_after' => 'पंजीकरण तक की तिथि, पंजीकरण से तिथि के बाद होनी चाहिए।',
    'extend_product' => [
        'title_required' => 'शीर्षक फ़ील्ड आवश्यक है।',
        'version_required' => 'संस्करण फ़ील्ड आवश्यक है।',
        'dependencies_required' => 'निर्भरता फ़ील्ड आवश्यक है।',
    ],
    'please_enter_recovery_code' => 'कृपया पुनर्प्राप्ति कोड दर्ज करें।',
    'social_login' => [
        'client_id_required' => 'गूगल, गिटहब, या लिंक्डइन के लिए क्लाइंट आईडी आवश्यक है।',
        'client_secret_required' => 'गूगल, गिटहब, या लिंक्डइन के लिए क्लाइंट सीक्रेट आवश्यक है।',
        'api_key_required' => 'ट्विटर के लिए API कुंजी आवश्यक है।',
        'api_secret_required' => 'ट्विटर के लिए API सीक्रेट आवश्यक है।',
        'redirect_url_required' => 'पुनर्निर्देश URL आवश्यक है।',
    ],
    'thirdparty_api' => [
        'app_name_required' => 'ऐप नाम आवश्यक है।',
        'app_key_required' => 'ऐप कुंजी आवश्यक है।',
        'app_key_size' => 'ऐप कुंजी बिल्कुल 32 वर्ण होनी चाहिए।',
        'app_secret_required' => 'ऐप सीक्रेट आवश्यक है।',
    ],
    'plan_request' => [
        'name_required' => 'नाम फ़ील्ड आवश्यक है',
        'product_quant_req' => 'उत्पाद मात्रा फ़ील्ड आवश्यक है',
        'plan_quant_req' => 'योजना मात्रा फ़ील्ड आवश्यक है',
        'start_date_req' => 'आरंभ तिथि आवश्यक है',
        'end_date_req' => 'समाप्ति तिथि आवश्यक है',
        'invalid_date' => 'अमान्य तिथि',
    ],
    'razorpay_val' => [
        'business_required' => 'व्यवसाय फ़ील्ड आवश्यक है।',
        'cmd_required' => 'कमांड फ़ील्ड आवश्यक है।',
        'paypal_url_required' => 'PayPal URL आवश्यक है।',
        'paypal_url_invalid' => 'PayPal URL एक मान्य URL होना चाहिए।',
        'success_url_invalid' => 'सफलता URL एक मान्य URL होना चाहिए।',
        'cancel_url_invalid' => 'रद्द करने का URL एक मान्य URL होना चाहिए।',
        'notify_url_invalid' => 'सूचना URL एक मान्य URL होना चाहिए।',
        'currencies_required' => 'मुद्राओं का फ़ील्ड आवश्यक है।',
    ],

];
