<?php

return [

    'accepted' => ':attribute는 반드시 동의해야 합니다.',
    'accepted_if' => ':attribute는 :other가 :value일 때 동의해야 합니다.',
    'active_url' => ':attribute는 유효한 URL이 아닙니다.',
    'after' => ':attribute는 :date 이후의 날짜여야 합니다.',
    'after_or_equal' => ':attribute는 :date 이후 또는 같은 날짜여야 합니다.',
    'alpha' => ':attribute는 문자만 포함해야 합니다.',
    'alpha_dash' => ':attribute는 문자, 숫자, 대시 및 밑줄만 포함해야 합니다.',
    'alpha_num' => ':attribute는 문자와 숫자만 포함해야 합니다.',
    'array' => ':attribute는 배열이어야 합니다.',
    'before' => ':attribute는 :date 이전의 날짜여야 합니다.',
    'before_or_equal' => ':attribute는 :date 이전 또는 같은 날짜여야 합니다.',
    'between' => [
        'array' => ':attribute는 :min과 :max 항목 사이여야 합니다.',
        'file' => ':attribute는 :min과 :max 킬로바이트 사이여야 합니다.',
        'numeric' => ':attribute는 :min과 :max 사이여야 합니다.',
        'string' => ':attribute는 :min과 :max 자 사이여야 합니다.',
    ],
    'boolean' => ':attribute 필드는 true 또는 false여야 합니다.',
    'confirmed' => ':attribute 확인이 일치하지 않습니다.',
    'current_password' => '비밀번호가 올바르지 않습니다.',
    'date' => ':attribute는 유효한 날짜가 아닙니다.',
    'date_equals' => ':attribute는 :date와 동일한 날짜여야 합니다.',
    'date_format' => ':attribute는 :format 형식과 일치하지 않습니다.',
    'declined' => ':attribute는 거부되어야 합니다.',
    'declined_if' => ':attribute는 :other가 :value일 때 거부되어야 합니다.',
    'different' => ':attribute와 :other는 달라야 합니다.',
    'digits' => ':attribute는 :digits 자여야 합니다.',
    'digits_between' => ':attribute는 :min과 :max 자 사이여야 합니다.',
    'dimensions' => ':attribute는 유효하지 않은 이미지 크기를 가집니다.',
    'distinct' => ':attribute 필드는 중복 값이 있습니다.',
    'doesnt_start_with' => ':attribute는 다음 중 하나로 시작할 수 없습니다: :values.',
    'email' => ':attribute는 유효한 이메일 주소여야 합니다.',
    'ends_with' => ':attribute는 다음 중 하나로 끝나야 합니다: :values.',
    'enum' => '선택된 :attribute는 유효하지 않습니다.',
    'exists' => '선택된 :attribute는 유효하지 않습니다.',
    'file' => ':attribute는 파일이어야 합니다.',
    'filled' => ':attribute 필드는 값이 있어야 합니다.',
    'gt' => [
        'array' => ':attribute는 :value 항목보다 많아야 합니다.',
        'file' => ':attribute는 :value 킬로바이트보다 커야 합니다.',
        'numeric' => ':attribute는 :value보다 커야 합니다.',
        'string' => ':attribute는 :value 자보다 길어야 합니다.',
    ],
    'gte' => [
        'array' => ':attribute는 :value 항목 이상이어야 합니다.',
        'file' => ':attribute는 :value 킬로바이트 이상이어야 합니다.',
        'numeric' => ':attribute는 :value 이상이어야 합니다.',
        'string' => ':attribute는 :value 자 이상이어야 합니다.',
    ],
    'image' => ':attribute는 이미지여야 합니다.',
    'in' => '선택된 :attribute는 유효하지 않습니다.',
    'in_array' => ':attribute 필드는 :other에 존재하지 않습니다.',
    'integer' => ':attribute는 정수여야 합니다.',
    'ip' => ':attribute는 유효한 IP 주소여야 합니다.',
    'ipv4' => ':attribute는 유효한 IPv4 주소여야 합니다.',
    'ipv6' => ':attribute는 유효한 IPv6 주소여야 합니다.',
    'json' => ':attribute는 유효한 JSON 문자열이어야 합니다.',
    'lt' => [
        'array' => ':attribute는 :value 항목보다 적어야 합니다.',
        'file' => ':attribute는 :value 킬로바이트보다 작아야 합니다.',
        'numeric' => ':attribute는 :value보다 작아야 합니다.',
        'string' => ':attribute는 :value 자보다 짧아야 합니다.',
    ],
    'lte' => [
        'array' => ':attribute는 :value 항목 이하이어야 합니다.',
        'file' => ':attribute는 :value 킬로바이트 이하이어야 합니다.',
        'numeric' => ':attribute는 :value 이하이어야 합니다.',
        'string' => ':attribute는 :value 자 이하이어야 합니다.',
    ],
    'mac_address' => ':attribute는 유효한 MAC 주소여야 합니다.',
    'max' => [
        'array' => ':attribute는 :max 항목 이하이어야 합니다.',
        'file' => ':attribute는 :max 킬로바이트 이하이어야 합니다.',
        'numeric' => ':attribute는 :max 이하이어야 합니다.',
        'string' => ':attribute는 :max 자 이하이어야 합니다.',
    ],
    'mimes' => ':attribute는 다음 형식의 파일이어야 합니다: :values.',
    'mimetypes' => ':attribute는 다음 형식의 파일이어야 합니다: :values.',
    'min' => [
        'array' => ':attribute는 최소 :min 항목이어야 합니다.',
        'file' => ':attribute는 최소 :min 킬로바이트이어야 합니다.',
        'numeric' => ':attribute는 최소 :min이어야 합니다.',
        'string' => ':attribute는 최소 :min 자이어야 합니다.',
    ],
    'multiple_of' => ':attribute는 :value의 배수여야 합니다.',
    'not_in' => '선택된 :attribute는 유효하지 않습니다.',
    'not_regex' => ':attribute 형식이 유효하지 않습니다.',
    'numeric' => ':attribute는 숫자여야 합니다.',
    'password' => [
        'letters' => ':attribute는 최소한 하나의 문자를 포함해야 합니다.',
        'mixed' => ':attribute는 최소한 하나의 대문자와 소문자를 포함해야 합니다.',
        'numbers' => ':attribute는 최소한 하나의 숫자를 포함해야 합니다.',
        'symbols' => ':attribute는 최소한 하나의 기호를 포함해야 합니다.',
        'uncompromised' => '주어진 :attribute는 데이터 유출에서 발견되었습니다. 다른 :attribute를 선택하세요.',
    ],
    'present' => ':attribute 필드는 존재해야 합니다.',
    'prohibited' => ':attribute 필드는 금지됩니다.',
    'prohibited_if' => ':attribute 필드는 :other가 :value일 때 금지됩니다.',
    'prohibited_unless' => ':attribute 필드는 :other가 :values에 있을 때만 금지되지 않습니다.',
    'prohibits' => ':attribute 필드는 :other가 존재하는 것을 금지합니다.',
    'regex' => ':attribute 형식이 유효하지 않습니다.',
    'required' => ':attribute 필드는 필수입니다.',
    'required_array_keys' => ':attribute 필드는 :values에 대한 항목을 포함해야 합니다.',
    'required_if' => ':attribute 필드는 :other가 :value일 때 필수입니다.',
    'required_unless' => ':attribute 필드는 :other가 :values에 있을 때만 필수입니다.',
    'required_with' => ':values가 있을 때 :attribute 필드는 필수입니다.',
    'required_with_all' => ':values가 모두 있을 때 :attribute 필드는 필수입니다.',
    'required_without' => ':values가 없을 때 :attribute 필드는 필수입니다.',
    'required_without_all' => ':values가 모두 없을 때 :attribute 필드는 필수입니다.',
    'same' => ':attribute와 :other는 일치해야 합니다.',
    'size' => [
        'array' => ':attribute는 :size 항목을 포함해야 합니다.',
        'file' => ':attribute는 :size 킬로바이트여야 합니다.',
        'numeric' => ':attribute는 :size이어야 합니다.',
        'string' => ':attribute는 :size 자여야 합니다.',
    ],
    'starts_with' => ':attribute는 다음 중 하나로 시작해야 합니다: :values.',
    'string' => ':attribute는 문자열이어야 합니다.',
    'timezone' => ':attribute는 유효한 시간대여야 합니다.',
    'unique' => ':attribute는 이미 사용되었습니다.',
    'uploaded' => ':attribute 업로드에 실패했습니다.',
    'url' => ':attribute는 유효한 URL이어야 합니다.',
    'uuid' => ':attribute는 유효한 UUID여야 합니다.',
    'attribute' => [],

    'publish_date_required' => '출판일은 필수 항목입니다.',
    'price_numeric_value' => '가격은 숫자여야 합니다.',
    'quantity_integer_value' => '수량은 정수여야 합니다.',
    'order_has_Expired' => '주문이 만료되었습니다.',
    'expired' => '만료됨',
    'eid_required' => 'EID 필드는 필수 항목입니다.',
    'eid_string' => 'EID는 문자열이어야 합니다.',
    'otp_required' => 'OTP 필드는 필수 항목입니다.',
    'amt_required' => '금액 필드는 필수 항목입니다.',
    'amt_numeric' => '금액은 숫자여야 합니다.',
    'payment_date_required' => '결제일은 필수 항목입니다.',
    'payment_method_required' => '결제 방법은 필수 항목입니다.',
    'total_amount_required' => '총 금액은 필수 항목입니다.',
    'total_amount_numeric' => '총 금액은 숫자여야 합니다.',
    'invoice_link_required' => '적어도 하나의 인보이스와 금액을 연결해 주세요.',
    /*
    Request file custom validation messages
    */

    //Common
    'settings_form' => [
        'company' => [
            'required' => '회사 필드는 필수 항목입니다.',
        ],
        'website' => [
            'url' => '웹사이트는 유효한 URL이어야 합니다.',
        ],
        'phone' => [
            'regex' => '전화번호 형식이 잘못되었습니다.',
        ],
        'address' => [
            'required' => '주소 필드는 필수 항목입니다.',
            'max' => '주소는 300자 이하이어야 합니다.',
        ],
        'logo' => [
            'mimes' => '로고는 PNG 파일이어야 합니다.',
        ],
        'driver' => [
            'required' => '운전사 필드는 필수 항목입니다.',
        ],
        'port' => [
            'integer' => '포트는 정수여야 합니다.',
        ],
        'email' => [
            'required' => '이메일 필드는 필수 항목입니다.',
            'email' => '이메일은 유효한 이메일 주소여야 합니다.',
        ],
        'password' => [
            'required' => '비밀번호 필드는 필수 항목입니다.',
        ],
        'error_email' => [
            'email' => '오류 이메일은 유효한 이메일 주소여야 합니다.',
        ],
    ],

    'settings_forms' => [
        'company' => [
            'required' => '회사 이름은 필수 항목입니다.',
            'max' => '회사 이름은 50자를 초과할 수 없습니다.',
        ],
        'company_email' => [
            'required' => '회사 이메일은 필수 항목입니다.',
            'email' => '회사 이메일은 유효한 이메일 주소여야 합니다.',
        ],
        'title' => [
            'max' => '제목은 50자를 초과할 수 없습니다.',
        ],
        'website' => [
            'required' => '웹사이트 URL은 필수 항목입니다.',
            'url' => '웹사이트는 유효한 URL이어야 합니다.',
            'regex' => '웹사이트 형식이 잘못되었습니다.',
        ],
        'phone' => [
            'required' => '전화번호는 필수 항목입니다.',
        ],
        'address' => [
            'required' => '주소는 필수 항목입니다.',
        ],
        'state' => [
            'required' => '주(State)는 필수 항목입니다.',
        ],
        'country' => [
            'required' => '국가는 필수 항목입니다.',
        ],
        'gstin' => [
            'max' => 'GSTIN은 15자를 초과할 수 없습니다.',
        ],
        'default_currency' => [
            'required' => '기본 통화는 필수 항목입니다.',
        ],
        'admin_logo' => [
            'mimes' => '관리자 로고는 jpeg, png, jpg 형식이어야 합니다.',
            'max' => '관리자 로고는 2MB를 초과할 수 없습니다.',
        ],
        'fav_icon' => [
            'mimes' => '파비콘은 jpeg, png, jpg 형식이어야 합니다.',
            'max' => '파비콘은 2MB를 초과할 수 없습니다.',
        ],
        'logo' => [
            'mimes' => '로고는 jpeg, png, jpg 형식이어야 합니다.',
            'max' => '로고는 2MB를 초과할 수 없습니다.',
        ],
    ],

    'social_media_form' => [
        'name' => [
            'required' => '이름 필드는 필수 항목입니다.',
            'unique' => '이 이름은 이미 존재합니다.',
            'max' => '이름은 50자를 초과할 수 없습니다.',
        ],
        'link' => [
            'required' => '링크 필드는 필수 항목입니다.',
            'url' => '링크는 유효한 URL이어야 합니다.',
            'regex' => '링크 형식이 잘못되었습니다.',
        ],
    ],

    //Email
    'custom' => [
        'password' => [
            'required_if' => '선택한 메일 드라이버에 대해 비밀번호 필드는 필수 항목입니다.',
        ],
        'port' => [
            'required_if' => 'SMTP에 대해 포트 필드는 필수 항목입니다.',
        ],
        'encryption' => [
            'required_if' => 'SMTP에 대해 암호화 필드는 필수 항목입니다.',
        ],
        'host' => [
            'required_if' => 'SMTP에 대해 호스트 필드는 필수 항목입니다.',
        ],
        'secret' => [
            'required_if' => '선택한 메일 드라이버에 대해 비밀 필드는 필수 항목입니다.',
        ],
        'domain' => [
            'required_if' => 'Mailgun에 대해 도메인 필드는 필수 항목입니다.',
        ],
        'key' => [
            'required_if' => 'SES에 대해 키 필드는 필수 항목입니다.',
        ],
        'region' => [
            'required_if' => 'SES에 대해 지역 필드는 필수 항목입니다.',
        ],
        'email' => [
            'required_if' => '선택한 메일 드라이버에 대해 이메일 필드는 필수 항목입니다.',
            'required' => '이메일 필드는 필수 항목입니다.',
            'email' => '유효한 이메일 주소를 입력해 주세요.',
            'not_matching' => '이메일 도메인은 현재 사이트 도메인과 일치해야 합니다.',
        ],
        'driver' => [
            'required' => '드라이버 필드는 필수 항목입니다.',
        ],
    ],

    'customer_form' => [
        'first_name' => [
            'required' => '이름 필드는 필수 항목입니다.',
        ],
        'last_name' => [
            'required' => '성 필드는 필수 항목입니다.',
        ],
        'company' => [
            'required' => '회사 필드는 필수 항목입니다.',
        ],
        'mobile' => [
            'regex' => '휴대폰 번호 형식이 잘못되었습니다.',
        ],
        'address' => [
            'required' => '주소 필드는 필수 항목입니다.',
        ],
        'zip' => [
            'required' => '우편번호 필드는 필수 항목입니다.',
            'min' => '우편번호는 최소 5자리여야 합니다.',
            'numeric' => '우편번호는 숫자여야 합니다.',
        ],
        'email' => [
            'required' => '이메일 필드는 필수 항목입니다.',
            'email' => '이메일은 유효한 이메일 주소여야 합니다.',
            'unique' => '이 이메일은 이미 사용되었습니다.',
        ],
    ],
    'contact_request' => [
        'conName' => '이름 필드는 필수 항목입니다.',
        'email' => '이메일 필드는 필수 항목입니다.',
        'conmessage' => '메시지 필드는 필수 항목입니다.',
        'Mobile' => '휴대폰 필드는 필수 항목입니다.',
        'country_code' => '휴대폰 필드는 필수 항목입니다.',
        'demoname' => '이름 필드는 필수 항목입니다.',
        'demomessage' => '메시지 필드는 필수 항목입니다.',
        'demoemail' => '이메일 필드는 필수 항목입니다.',
        'congg-recaptcha-response-1.required' => '로봇 검증에 실패했습니다. 다시 시도해 주세요.',
        'demo-recaptcha-response-1.required' => '로봇 검증에 실패했습니다. 다시 시도해 주세요.',
    ],

    'frontend_pages' => [
        'name' => [
            'required' => '이름 필드는 필수 항목입니다.',
            'unique' => '이 이름은 이미 존재합니다.',
            'max' => '이름은 20자를 초과할 수 없습니다.',
            'regex' => '이름은 문자와 공백만 포함할 수 있습니다.',
        ],
        'publish' => [
            'required' => '게시 필드는 필수 항목입니다.',
        ],
        'slug' => [
            'required' => '슬러그 필드는 필수 항목입니다.',
        ],
        'url' => [
            'required' => 'URL 필드는 필수 항목입니다.',
            'url' => 'URL은 유효한 링크여야 합니다.',
            'regex' => 'URL 형식이 잘못되었습니다.',
        ],
        'content' => [
            'required' => '내용 필드는 필수 항목입니다.',
        ],
        'created_at' => [
            'required' => '생성일 필드는 필수 항목입니다.',
        ],
    ],

    //Order form
    'order_form' => [
        'client' => [
            'required' => '고객 필드는 필수 항목입니다.',
        ],
        'payment_method' => [
            'required' => '결제 방법 필드는 필수 항목입니다.',
        ],
        'promotion_code' => [
            'required' => '프로모션 코드 필드는 필수 항목입니다.',
        ],
        'order_status' => [
            'required' => '주문 상태 필드는 필수 항목입니다.',
        ],
        'product' => [
            'required' => '상품 필드는 필수 항목입니다.',
        ],
        'subscription' => [
            'required' => '구독 필드는 필수 항목입니다.',
        ],
        'price_override' => [
            'numeric' => '가격 오버라이드는 숫자여야 합니다.',
        ],
        'qty' => [
            'integer' => '수량은 정수여야 합니다.',
        ],
    ],

    //Payment form
    'coupon_form' => [
        'code' => [
            'required' => '쿠폰 코드 필드는 필수 항목입니다.',
            'string' => '쿠폰 코드는 문자열이어야 합니다.',
            'max' => '쿠폰 코드는 255자를 초과할 수 없습니다.',
        ],
        'type' => [
            'required' => '유형 필드는 필수 항목입니다.',
            'in' => '유효하지 않은 유형입니다. 허용된 값은: percentage, other_type입니다.',
        ],
        'applied' => [
            'required' => '적용된 제품 필드는 필수 항목입니다.',
            'date' => '적용된 제품 필드는 유효한 날짜여야 합니다.',
        ],
        'uses' => [
            'required' => '사용 횟수 필드는 필수 항목입니다.',
            'numeric' => '사용 횟수는 숫자여야 합니다.',
            'min' => '사용 횟수는 최소 :min이어야 합니다.',
        ],
        'start' => [
            'required' => '시작 필드는 필수 항목입니다.',
            'date' => '시작 필드는 유효한 날짜여야 합니다.',
        ],
        'expiry' => [
            'required' => '만료 필드는 필수 항목입니다.',
            'date' => '만료 필드는 유효한 날짜여야 합니다.',
            'after' => '만료 날짜는 시작 날짜 이후여야 합니다.',
        ],
        'value' => [
            'required' => '할인 값 필드는 필수 항목입니다.',
            'numeric' => '할인 값 필드는 숫자여야 합니다.',
            'between' => '할인 값 필드는 타입이 percentage인 경우 :min과 :max 사이여야 합니다.',
        ],
    ],

    'tax_form' => [
        'name' => [
            'required' => '이름 필드는 필수 항목입니다.',
        ],
        'rate' => [
            'required' => '세율 필드는 필수 항목입니다.',
            'numeric' => '세율은 숫자여야 합니다.',
        ],
        'level' => [
            'required' => '레벨 필드는 필수 항목입니다.',
            'integer' => '레벨은 정수여야 합니다.',
        ],
        'country' => [
            'required' => '국가 필드는 필수 항목입니다.',
            // 'exists' => '선택한 국가는 유효하지 않습니다.',
        ],
        'state' => [
            'required' => '상태 필드는 필수 항목입니다.',
            // 'exists' => '선택한 상태는 유효하지 않습니다.',
        ],
    ],

    //Product
    'subscription_form' => [
        'name' => [
            'required' => '이름 필드는 필수 항목입니다.',
        ],
        'subscription' => [
            'required' => '구독 필드는 필수 항목입니다.',
        ],
        'regular_price' => [
            'required' => '정상 가격 필드는 필수 항목입니다.',
            'numeric' => '정상 가격은 숫자여야 합니다.',
        ],
        'selling_price' => [
            'required' => '판매 가격 필드는 필수 항목입니다.',
            'numeric' => '판매 가격은 숫자여야 합니다.',
        ],
        'products' => [
            'required' => '상품 필드는 필수 항목입니다.',
        ],
    ],

    'bundle' => [
        'name' => [
            'required' => '이름 필드는 필수 항목입니다.',
        ],
        'items' => [
            'required' => '각 항목은 필수 항목입니다.',
        ],
    ],
    'group' => [
        'name' => [
            'required' => '이름은 필수 항목입니다.',
        ],
        'features' => [
            'name' => [
                'required' => '모든 기능 필드는 필수 항목입니다.',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => '가격은 필수 항목입니다.',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => '값은 필수 항목입니다.',
            ],
        ],
        'type' => [
            'required_with' => '유형은 필수 항목입니다.',
        ],
        'title' => [
            'required_with' => '제목은 필수 항목입니다.',
        ],
    ],

    'product' => [
        'name' => [
            'required' => '이름 필드는 필수 항목입니다.',
        ],
        'type' => [
            'required' => '유형 필드는 필수 항목입니다.',
        ],
        'group' => [
            'required' => '그룹 필드는 필수 항목입니다.',
        ],
        'subscription' => [
            'required' => '구독 필드는 필수 항목입니다.',
        ],
        'currency' => [
            'required' => '통화 필드는 필수 항목입니다.',
        ],
        'file' => [
            'required_without_all' => '파일 필드는 github_owner나 github_repository가 제공되지 않으면 필수입니다.',
            'mimes' => '파일은 zip 파일이어야 합니다.',
        ],
        'image' => [
            'required_without_all' => '이미지 필드는 github_owner나 github_repository가 제공되지 않으면 필수입니다.',
            'mimes' => '이미지는 PNG 파일이어야 합니다.',
        ],
        'github_owner' => [
            'required_without_all' => 'GitHub 소유자 필드는 파일이나 이미지가 제공되지 않으면 필수입니다.',
        ],
        'github_repository' => [
            'required_without_all' => 'GitHub 저장소 필드는 파일이나 이미지가 제공되지 않으면 필수입니다.',
            'required_if' => 'GitHub 저장소 필드는 유형이 2일 때 필수입니다.',
        ],
    ],

    'users' => [
        'first_name' => [
            'required' => '이름 필드는 필수 항목입니다.',
        ],
        'last_name' => [
            'required' => '성 필드는 필수 항목입니다.',
        ],
        'company' => [
            'required' => '회사 필드는 필수 항목입니다.',
        ],
        'email' => [
            'required' => '이메일 필드는 필수 항목입니다.',
            'email' => '이메일은 유효한 이메일 주소여야 합니다.',
            'unique' => '이 이메일은 이미 사용되었습니다.',
        ],
        'address' => [
            'required' => '주소 필드는 필수 항목입니다.',
        ],
        'mobile' => [
            'required' => '휴대폰 필드는 필수 항목입니다.',
        ],
        'country' => [
            'required' => '국가 필드는 필수 항목입니다.',
            'exists' => '선택한 국가는 유효하지 않습니다.',
        ],
        'state' => [
            'required_if' => '국가가 인도일 경우, 상태 필드는 필수입니다.',
        ],
        'timezone_id' => [
            'required' => '시간대 필드는 필수 항목입니다.',
        ],
        'user_name' => [
            'required' => '사용자 이름 필드는 필수 항목입니다.',
            'unique' => '이 사용자 이름은 이미 사용되었습니다.',
        ],
        'zip' => [
            'regex' => '국가가 인도일 경우, 상태 필드는 필수입니다.',
        ],
    ],

    'profile_form' => [
        'first_name' => [
            'required' => '이름은 필수 항목입니다.',
            'min' => '이름은 최소 :min 자 이상이어야 합니다.',
            'max' => '이름은 최대 :max 자 이하이어야 합니다.',
        ],
        'last_name' => [
            'required' => '성은 필수 항목입니다.',
            'max' => '성은 최대 :max 자 이하이어야 합니다.',
        ],
        'company' => [
            'required' => '회사 이름은 필수 항목입니다.',
            'max' => '회사 이름은 최대 :max 자 이하이어야 합니다.',
        ],
        'email' => [
            'required' => '이메일은 필수 항목입니다.',
            'email' => '유효한 이메일 주소를 입력하세요.',
            'unique' => '이 이메일 주소는 이미 사용되었습니다. 다른 이메일을 선택하세요.',
        ],
        'mobile' => [
            'required' => '휴대폰 번호는 필수 항목입니다.',
            'regex' => '유효한 휴대폰 번호를 입력하세요.',
            'min' => '휴대폰 번호는 최소 :min 자 이상이어야 합니다.',
            'max' => '휴대폰 번호는 최대 :max 자 이하이어야 합니다.',
        ],
        'address' => [
            'required' => '주소는 필수 항목입니다.',
        ],
        'user_name' => [
            'required' => '사용자 이름은 필수 항목입니다.',
            'unique' => '이 사용자 이름은 이미 사용되었습니다.',
        ],
        'timezone_id' => [
            'required' => '시간대는 필수 항목입니다.',
        ],
        'country' => [
            'required' => '국가는 필수 항목입니다.',
            'exists' => '선택한 국가는 유효하지 않습니다.',
        ],
        'state' => [
            'required_if' => '국가가 인도일 경우, 상태 필드는 필수입니다.',
        ],
        'old_password' => [
            'required' => '기존 비밀번호는 필수 항목입니다.',
            'min' => '기존 비밀번호는 최소 :min 자 이상이어야 합니다.',
        ],
        'new_password' => [
            'required' => '새 비밀번호는 필수 항목입니다.',
            'different' => '새 비밀번호는 기존 비밀번호와 달라야 합니다.',
        ],
        'confirm_password' => [
            'required' => '비밀번호 확인은 필수 항목입니다.',
            'same' => '비밀번호 확인은 새 비밀번호와 일치해야 합니다.',
        ],
        'terms' => [
            'required' => '약관에 동의해야 합니다.',
        ],
        'password' => [
            'required' => '비밀번호는 필수 항목입니다.',
        ],
        'password_confirmation' => [
            'required' => '비밀번호 확인은 필수 항목입니다.',
            'same' => '비밀번호가 일치하지 않습니다.',
        ],
        'mobile_code' => [
            'required' => '국가 코드 (휴대폰)를 입력하세요.',
        ],
    ],
    //Invoice form
    'invoice' => [
        'user' => [
            'required' => '클라이언트 필드는 필수 항목입니다.',
        ],
        'date' => [
            'required' => '날짜 필드는 필수 항목입니다.',
            'date' => '날짜는 유효한 날짜여야 합니다.',
        ],
        'domain' => [
            'regex' => '도메인 형식이 잘못되었습니다.',
        ],
        'plan' => [
            'required_if' => '구독 필드는 필수 항목입니다.',
        ],
        'price' => [
            'required' => '가격 필드는 필수 항목입니다.',
        ],
        'product' => [
            'required' => '제품 필드는 필수 항목입니다.',
        ],
    ],

    //LocalizedLicense form
    'domain_form' => [
        'domain' => [
            'required' => '도메인 필드는 필수 항목입니다.',
            'url' => '도메인은 유효한 URL이어야 합니다.',
        ],
    ],

    //Product Renewal form
    'product_renewal' => [
        'domain' => [
            'required' => '도메인 필드는 필수 항목입니다.',
            'no_http' => '도메인에 "http" 또는 "https"가 포함되지 않아야 합니다.',
        ],
    ],

    //Language form
    'language' => [
        'required' => '언어 필드는 필수 항목입니다.',
        'invalid' => '선택한 언어는 유효하지 않습니다.',
    ],

    //UpdateSroragePathRequest form
    'storage_path' => [
        'disk' => [
            'required' => '저장 디스크 필드는 필수 항목입니다.',
            'string' => '디스크는 문자열이어야 합니다.',
        ],
        'path' => [
            'string' => '경로는 문자열이어야 합니다.',
            'nullable' => '경로 필드는 선택 사항입니다.',
        ],
    ],

    //ValidateSecretRequest form
    'validate_secret' => [
        'totp' => [
            'required' => '코드를 입력하세요.',
            'digits' => '유효한 6자리 코드를 입력하세요.',
        ],
    ],

    //VerifyOtp form
    'verify_email' => [
        'required' => '이메일 필드는 필수 항목입니다.',
        'email' => '이메일은 유효한 이메일 주소여야 합니다.',
        'verify_email' => '이메일 인증에 실패했습니다.', // Custom message for verify_email
    ],

    'verify_country_code' => [
        'required' => '국가 코드는 필수 항목입니다.',
        'numeric' => '국가 코드는 유효한 숫자여야 합니다.',
        'verify_country_code' => '국가 코드 인증에 실패했습니다.', // Custom message for verify_country_code
    ],

    'verify_number' => [
        'required' => '번호는 필수 항목입니다.',
        'numeric' => '번호는 유효한 숫자여야 합니다.',
        'verify_number' => '번호 인증에 실패했습니다.', // Custom message for verify_number
    ],

    'password_otp' => [
        'required' => '비밀번호 필드는 필수 항목입니다.',
        'password' => '비밀번호가 잘못되었습니다.',
        'invalid' => '잘못된 비밀번호입니다.',
    ],

    //AuthController file
    'auth_controller' => [
        'name_required' => '이름은 필수 항목입니다.',
        'name_max' => '이름은 255자를 초과할 수 없습니다.',

        'email_required' => '이메일은 필수 항목입니다.',
        'email_email' => '유효한 이메일 주소를 입력하세요.',
        'email_max' => '이메일은 255자를 초과할 수 없습니다.',
        'email_unique' => '이 이메일은 이미 등록되어 있습니다.',

        'password_required' => '비밀번호는 필수 항목입니다.',
        'password_confirmed' => '비밀번호 확인이 일치하지 않습니다.',
        'password_min' => '비밀번호는 최소 6자 이상이어야 합니다.',
    ],

    'resend_otp' => [
        'eid_required' => 'EID 필드는 필수 항목입니다.',
        'eid_string' => 'EID는 문자열이어야 합니다.',
        'type_required' => '유형 필드는 필수 항목입니다.',
        'type_string' => '유형은 문자열이어야 합니다.',
        'type_in' => '선택한 유형은 유효하지 않습니다.',
    ],

    'verify_otp' => [
        'eid_required' => '직원 ID는 필수 항목입니다.',
        'eid_string' => '직원 ID는 문자열이어야 합니다.',
        'otp_required' => 'OTP는 필수 항목입니다.',
        'otp_size' => 'OTP는 정확히 6자리여야 합니다.',
        'recaptcha_required' => 'CAPTCHA를 완료해주세요.',
        'recaptcha_size' => 'CAPTCHA 응답이 유효하지 않습니다.',
    ],

    'company_validation' => [
        'company_required' => '회사 이름은 필수 항목입니다.',
        'company_string' => '회사는 텍스트여야 합니다.',
        'address_required' => '주소는 필수 항목입니다.',
        'address_string' => '주소는 텍스트여야 합니다.',
    ],

    'token_validation' => [
        'token_required' => '토큰은 필수 항목입니다.',
        'password_required' => '비밀번호 필드는 필수 항목입니다.',
        'password_confirmed' => '비밀번호 확인이 일치하지 않습니다.',
    ],

    'custom_email' => [
        'required' => '이메일 필드는 필수 항목입니다.',
        'email' => '유효한 이메일 주소를 입력하세요.',
        'exists' => '이 이메일은 등록되지 않았습니다.',
    ],

    'newsletterEmail' => [
        'required' => '뉴스레터 이메일은 필수 항목입니다.',
        'email' => '뉴스레터에 유효한 이메일 주소를 입력하세요.',
    ],

    'widget' => [
        'name_required' => '이름은 필수 항목입니다.',
        'name_max' => '이름은 50자를 초과할 수 없습니다.',
        'publish_required' => '게시 상태는 필수 항목입니다.',
        'type_required' => '유형은 필수 항목입니다.',
        'type_unique' => '이 유형은 이미 존재합니다.',
    ],

    'payment' => [
        'payment_date_required' => '지불 날짜는 필수 항목입니다.',
        'payment_method_required' => '지불 방법은 필수 항목입니다.',
        'amount_required' => '금액은 필수 항목입니다.',
    ],

    'custom_date' => [
        'date_required' => '날짜 필드는 필수 항목입니다.',
        'total_required' => '총 금액 필드는 필수 항목입니다.',
        'status_required' => '상태 필드는 필수 항목입니다.',
    ],

    'plan_renewal' => [
        'plan_required' => '계획 필드는 필수 항목입니다.',
        'payment_method_required' => '지불 방법 필드는 필수 항목입니다.',
        'cost_required' => '비용 필드는 필수 항목입니다.',
        'code_not_valid' => '프로모션 코드는 유효하지 않습니다.',
    ],

    'rate' => [
        'required' => '요금은 필수 항목입니다.',
        'numeric' => '요금은 숫자여야 합니다.',
    ],

    'product_validate' => [
        'producttitle_required' => '제품 제목은 필수입니다.',
        'version_required' => '버전은 필수입니다.',
        'filename_required' => '파일을 업로드해 주세요.',
        'dependencies_required' => '종속성 필드는 필수입니다.',
    ],
    'product_sku_unique' => '제품 SKU는 고유해야 합니다.',
    'product_name_unique' => '이름은 고유해야 합니다.',
    'product_show_agent_required' => '카트 페이지 선호도를 선택해 주세요.',
    'product_controller' => [
        'name_required' => '제품 이름은 필수입니다.',
        'name_unique' => '이름은 고유해야 합니다.',
        'type_required' => '제품 유형은 필수입니다.',
        'description_required' => '제품 설명은 필수입니다.',
        'product_description_required' => '상세 제품 설명은 필수입니다.',
        'image_mimes' => '이미지는 jpeg, png, jpg 형식이어야 합니다.',
        'image_max' => '이미지 크기는 2048킬로바이트를 넘을 수 없습니다.',
        'product_sku_required' => '제품 SKU는 필수입니다.',
        'group_required' => '제품 그룹은 필수입니다.',
        'show_agent_required' => '카트 페이지 선호도를 선택해 주세요.',
    ],
    'current_domain_required' => '현재 도메인은 필수입니다.',
    'new_domain_required' => '새 도메인은 필수입니다.',
    'special_characters_not_allowed' => '도메인 이름에 특수 문자는 허용되지 않습니다.',
    'orderno_required' => '주문 번호는 필수입니다.',
    'cloud_central_domain_required' => '클라우드 중앙 도메인은 필수입니다.',
    'cloud_cname_required' => '클라우드 CNAME은 필수입니다.',
    'cloud_tenant' => [
        'cloud_top_message_required' => '클라우드 상단 메시지는 필수입니다.',
        'cloud_label_field_required' => '클라우드 레이블 필드는 필수입니다.',
        'cloud_label_radio_required' => '클라우드 레이블 라디오는 필수입니다.',
        'cloud_product_required' => '클라우드 제품은 필수입니다.',
        'cloud_free_plan_required' => '클라우드 무료 플랜은 필수입니다.',
        'cloud_product_key_required' => '클라우드 제품 키는 필수입니다.',
    ],
    'reg_till_after' => '등록 종료 날짜는 등록 시작 날짜 이후여야 합니다.',
    'extend_product' => [
        'title_required' => '제목 필드는 필수입니다.',
        'version_required' => '버전 필드는 필수입니다.',
        'dependencies_required' => '종속성 필드는 필수입니다.',
    ],
    'please_enter_recovery_code' => '복구 코드를 입력해 주세요.',
    'social_login' => [
        'client_id_required' => 'Google, Github 또는 Linkedin에 대해 클라이언트 ID가 필요합니다.',
        'client_secret_required' => 'Google, Github 또는 Linkedin에 대해 클라이언트 비밀키가 필요합니다.',
        'api_key_required' => 'Twitter에 대해 API 키가 필요합니다.',
        'api_secret_required' => 'Twitter에 대해 API 비밀키가 필요합니다.',
        'redirect_url_required' => '리디렉션 URL이 필요합니다.',
    ],
    'thirdparty_api' => [
        'app_name_required' => '앱 이름은 필수입니다.',
        'app_key_required' => '앱 키는 필수입니다.',
        'app_key_size' => '앱 키는 정확히 32자여야 합니다.',
        'app_secret_required' => '앱 비밀키는 필수입니다.',
    ],
    'plan_request' => [
        'name_required' => '이름 필드는 필수입니다',
        'product_quant_req' => '에이전트 수가 없는 경우 제품 수량 필드는 필수입니다.',
        'no_agent_req' => '제품 수량이 없는 경우 에이전트 수 필드는 필수입니다.',
        'pro_req' => '제품 필드는 필수입니다',
        'offer_price' => '할인 가격은 100보다 클 수 없습니다',
    ],
    'razorpay_val' => [
        'business_required' => '비즈니스 필드는 필수입니다.',
        'cmd_required' => '명령어 필드는 필수입니다.',
        'paypal_url_required' => 'PayPal URL은 필수입니다.',
        'paypal_url_invalid' => 'PayPal URL은 유효한 URL이어야 합니다.',
        'success_url_invalid' => '성공 URL은 유효한 URL이어야 합니다.',
        'cancel_url_invalid' => '취소 URL은 유효한 URL이어야 합니다.',
        'notify_url_invalid' => '알림 URL은 유효한 URL이어야 합니다.',
        'currencies_required' => '통화 필드는 필수입니다.',
    ],
    'login_failed' => '로그인에 실패했습니다. 입력한 이메일/사용자 이름과 비밀번호가 올바른지 확인하세요.',
    'forgot_email_validation' => '제공한 이메일이 등록되어 있다면, 비밀번호 재설정 지침이 포함된 이메일을 곧 받게 됩니다.',
    'too_many_login_attempts' => '너무 많은 로그인 시도로 인해 애플리케이션에서 차단되었습니다. :time 후에 다시 시도하세요.',

];
