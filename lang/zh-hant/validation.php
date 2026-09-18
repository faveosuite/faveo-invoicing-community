<?php

return [
    'accepted' => '必須接受 :attribute。',
    'accepted_if' => '當 :other 為 :value 時，必須接受 :attribute。',
    'active_url' => ':attribute 不是有效的 URL。',
    'after' => ':attribute 必須是 :date 之後的日期。',
    'after_or_equal' => ':attribute 必須是 :date 之後或相等的日期。',
    'alpha' => ':attribute 只能包含字母。',
    'alpha_dash' => ':attribute 只能包含字母、數字、破折號和底線。',
    'alpha_num' => ':attribute 只能包含字母和數字。',
    'array' => ':attribute 必須是陣列。',
    'before' => ':attribute 必須是 :date 之前的日期。',
    'before_or_equal' => ':attribute 必須是 :date 之前或相等的日期。',
    'between' => [
        'array' => ':attribute 必須有 :min 到 :max 項目。',
        'file' => ':attribute 必須在 :min 到 :max KB 之間。',
        'numeric' => ':attribute 必須在 :min 到 :max 之間。',
        'string' => ':attribute 必須在 :min 到 :max 字符之間。',
    ],
    'boolean' => ':attribute 欄位必須是 true 或 false。',
    'confirmed' => ':attribute 確認不匹配。',
    'current_password' => '密碼不正確。',
    'date' => ':attribute 不是有效的日期。',
    'date_equals' => ':attribute 必須是與 :date 相等的日期。',
    'date_format' => ':attribute 與格式 :format 不符。',
    'decimal' => ':attribute 欄位必須有 :decimal 小數位。',
    'declined' => ':attribute 必須被拒絕。',
    'declined_if' => '當 :other 為 :value 時，:attribute 必須被拒絕。',
    'different' => ':attribute 和 :other 必須不同。',
    'digits' => ':attribute 必須是 :digits 位數字。',
    'digits_between' => ':attribute 必須是 :min 到 :max 位數字。',
    'dimensions' => ':attribute 圖像尺寸無效。',
    'distinct' => ':attribute 欄位有重複的值。',
    'doesnt_start_with' => ':attribute 不得以以下之一開始：:values。',
    'email' => ':attribute 必須是有效的電子郵件地址。',
    'ends_with' => ':attribute 必須以以下之一結尾：:values。',
    'enum' => '選擇的 :attribute 無效。',
    'exists' => '選擇的 :attribute 無效。',
    'file' => ':attribute 必須是檔案。',
    'filled' => ':attribute 欄位必須有值。',
    'gt' => [
        'array' => ':attribute 必須有多於 :value 項目。',
        'file' => ':attribute 必須大於 :value KB。',
        'numeric' => ':attribute 必須大於 :value。',
        'string' => ':attribute 必須大於 :value 字符。',
    ],
    'gte' => [
        'array' => ':attribute 必須有 :value 項目或更多。',
        'file' => ':attribute 必須大於或等於 :value KB。',
        'numeric' => ':attribute 必須大於或等於 :value。',
        'string' => ':attribute 必須大於或等於 :value 字符。',
    ],
    'image' => ':attribute 必須是圖像。',
    'in' => '選擇的 :attribute 無效。',
    'in_array' => ':attribute 欄位不存在於 :other 中。',
    'integer' => ':attribute 必須是整數。',
    'ip' => ':attribute 必須是有效的 IP 地址。',
    'ipv4' => ':attribute 必須是有效的 IPv4 地址。',
    'ipv6' => ':attribute 必須是有效的 IPv6 地址。',
    'json' => ':attribute 必須是有效的 JSON 字串。',
    'lt' => [
        'array' => ':attribute 必須有少於 :value 項目。',
        'file' => ':attribute 必須小於 :value KB。',
        'numeric' => ':attribute 必須小於 :value。',
        'string' => ':attribute 必須小於 :value 字符。',
    ],
    'lte' => [
        'array' => ':attribute 必須沒有超過 :value 項目。',
        'file' => ':attribute 必須小於或等於 :value KB。',
        'numeric' => ':attribute 必須小於或等於 :value。',
        'string' => ':attribute 必須小於或等於 :value 字符。',
    ],
    'mac_address' => ':attribute 必須是有效的 MAC 地址。',
    'max' => [
        'array' => ':attribute 必須不超過 :max 項目。',
        'file' => ':attribute 必須不大於 :max KB。',
        'numeric' => ':attribute 必須不大於 :max。',
        'string' => ':attribute 必須不超過 :max 字符。',
    ],
    'mimes' => ':attribute 必須是以下類型的檔案：:values。',
    'mimetypes' => ':attribute 必須是以下類型的檔案：:values。',
    'min' => [
        'array' => ':attribute 必須至少有 :min 項目。',
        'file' => ':attribute 必須至少有 :min KB。',
        'numeric' => ':attribute 必須至少是 :min。',
        'string' => ':attribute 必須至少是 :min 字符。',
    ],
    'multiple_of' => ':attribute 必須是 :value 的倍數。',
    'not_in' => '選擇的 :attribute 無效。',
    'not_regex' => ':attribute 格式無效。',
    'numeric' => ':attribute 必須是數字。',
    'password' => [
        'letters' => ':attribute 必須至少包含一個字母。',
        'mixed' => ':attribute 必須至少包含 1 個大寫字母和 1 個小寫字母。',
        'numbers' => ':attribute 必須至少包含一個數字。',
        'symbols' => ':attribute 必須至少包含一個符號。',
        'uncompromised' => '給定的:attribute出現在資料外洩。請選擇不同的 :attribute。',
    ],
    'present' => ':attribute 欄位必須存在。',
    'prohibited' => ':attribute 欄位被禁止。',
    'prohibited_if' => '當 :other 為 :value 時，:attribute 欄位被禁止。',
    'prohibited_unless' => '除非 :other 在 :values 中，否則 :attribute 欄位被禁止。',
    'prohibits' => ':attribute 欄位禁止 :other 存在。',
    'regex' => ':attribute 格式無效。',
    'required' => ':attribute 欄位是必須的。',
    'required_array_keys' => ':attribute 欄位必須包含以下項目：:values。',
    'required_if' => '當 :other 為 :value 時，:attribute 欄位是必須的。',
    'required_unless' => '除非 :other 在 :values 中，否則 :attribute 欄位是必須的。',
    'required_with' => '當 :values 存在時，:attribute 欄位是必須的。',
    'required_with_all' => '當 :values 存在時，:attribute 欄位是必須的。',
    'required_without' => '當 :values 不存在時，:attribute 欄位是必須的。',
    'required_without_all' => '當 :values 都不存在時，:attribute 欄位是必須的。',
    'same' => ':attribute 和 :other 必須匹配。',
    'size' => [
        'array' => ':attribute 必須包含 :size 項目。',
        'file' => ':attribute 必須是 :size KB。',
        'numeric' => ':attribute 必須是 :size。',
        'string' => ':attribute 必須是 :size 字符。',
    ],
    'starts_with' => ':attribute 必須以以下之一開始：:values。',
    'string' => ':attribute 必須是字串。',
    'timezone' => ':attribute 必須是有效的時區。',
    'unique' => ':attribute 已經被使用。',
    'uploaded' => ':attribute 上傳失敗。',
    'url' => ':attribute 必須是有效的 URL。',
    'uuid' => ':attribute 必須是有效的 UUID。',
    'custom_dup' => [
        'attribute-name' => [
            'rule-name' => '自訂訊息',
        ],
    ],
    'attributes' => [],
    'publish_date_required' => '發布日期為必填項',
    'price_numeric_value' => '價格必須為數值',
    'quantity_integer_value' => '數量必須為整數',
    'order_has_Expired' => '訂單已過期',
    'expired' => '已過期',
    'eid_required' => 'EID 欄位為必填項。',
    'eid_string' => 'EID 必須是字串。',
    'otp_required' => 'OTP 欄位為必填項。',
    'amt_required' => '金額欄位為必填項',
    'amt_numeric' => '金額必須為數字',
    'payment_date_required' => '付款日期為必填項。',
    'payment_method_required' => '付款方式為必填項。',
    'total_amount_required' => '總金額為必填項。',
    'total_amount_numeric' => '總金額必須為數值。',
    'invoice_link_required' => '請將金額連結至至少一張發票。',
    'settings_form' => [
        'company' => [
            'required' => '公司欄位為必填項。',
        ],
        'website' => [
            'url' => '網站必須是有效的 URL。',
        ],
        'phone' => [
            'regex' => '電話號碼格式無效。',
        ],
        'address' => [
            'required' => '地址欄位為必填項。',
            'max' => '地址不能超過 300 個字元。',
        ],
        'logo' => [
            'mimes' => 'Logo 必須是 PNG 檔案。',
        ],
        'driver' => [
            'required' => '驅動欄位為必填項。',
        ],
        'port' => [
            'integer' => '連接埠必須是整數。',
        ],
        'email' => [
            'required' => '電子郵件欄位為必填項。',
            'email' => '電子郵件必須是有效的電子郵件地址。',
        ],
        'password' => [
            'required' => '密碼欄位為必填項。',
        ],
        'error_email' => [
            'email' => '錯誤電子郵件必須是有效的電子郵件地址。',
        ],
    ],
    'settings_forms' => [
        'company' => [
            'required' => '公司名稱為必填項。',
            'max' => '公司名稱不得超過 50 個字元。',
        ],
        'company_email' => [
            'required' => '公司電子郵件為必填項。',
            'email' => '公司電子郵件必須是有效的電子郵件地址。',
        ],
        'title' => [
            'max' => '標題不得超過 50 個字元。',
        ],
        'website' => [
            'required' => '網站 URL 為必填項。',
            'url' => '網站必須是有效的 URL。',
            'regex' => '網站格式無效。',
        ],
        'phone' => [
            'required' => '電話號碼為必填項。',
        ],
        'address' => [
            'required' => '地址為必填項。',
        ],
        'state' => [
            'required' => '州/省為必填項。',
        ],
        'country' => [
            'required' => '國家為必填項。',
        ],
        'gstin' => [
            'regex' => 'GSTIN 格式無效。',
        ],
        'default_currency' => [
            'required' => '預設貨幣為必填項。',
        ],
        'admin_logo' => [
            'mimes' => '管理員 Logo 檔案必須為：jpeg、png、jpg。',
            'max' => '管理員 Logo 不得大於 2MB。',
        ],
        'fav_icon' => [
            'mimes' => '網站圖示檔案必須為：jpeg、png、jpg。',
            'max' => '網站圖示不得大於 2MB。',
        ],
        'logo' => [
            'mimes' => 'Logo 檔案必須為：jpeg、png、jpg。',
            'max' => 'Logo 不得大於 2MB。',
        ],
    ],
    'og_image' => [
        'mimes' => 'OG影像必須是以下類型的檔案：jpeg、png、jpg、webp。',
        'max' => 'OG圖像不得大於2MB。',
    ],
    'social_media_form' => [
        'name' => [
            'required' => '名稱欄位為必填項。',
            'unique' => '此名稱已存在。',
            'max' => '名稱不得超過 50 個字元。',
        ],
        'link' => [
            'required' => '連結欄位為必填項。',
            'url' => '連結必須是有效的 URL。',
            'regex' => '連結格式無效。',
        ],
        'class' => [
            'required' => '圖示類別欄位為必填項。',
        ],
        'fa_class' => [
            'required' => '圖示類別欄位為必填項。',
        ],
    ],
    'custom' => [
        'password' => [
            'required_if' => '為所選的郵件驅動，密碼欄位為必填項。',
        ],
        'port' => [
            'required_if' => 'SMTP 需要連接埠欄位為必填項。',
        ],
        'encryption' => [
            'required_if' => 'SMTP 需要加密欄位為必填項。',
        ],
        'host' => [
            'required_if' => 'SMTP 需要主機欄位為必填項。',
        ],
        'secret' => [
            'required_if' => '為所選的郵件驅動，密鑰欄位為必填項。',
        ],
        'domain' => [
            'required_if' => 'Mailgun 需要網域欄位為必填項。',
        ],
        'key' => [
            'required_if' => 'SES 需要金鑰欄位為必填項。',
        ],
        'region' => [
            'required_if' => 'SES 需要區域欄位為必填項。',
        ],
        'email' => [
            'required_if' => '為所選的郵件驅動，電子郵件欄位為必填項。',
            'required' => '電子郵件欄位為必填項。',
            'email' => '請輸入有效的電子郵件地址。',
            'not_matching' => '電子郵件網域必須與當前網站網域相符。',
        ],
        'driver' => [
            'required' => '驅動欄位為必填項。',
        ],
    ],
    'customer_form' => [
        'first_name' => [
            'required' => '名字欄位為必填項。',
        ],
        'last_name' => [
            'required' => '姓氏欄位為必填項。',
        ],
        'company' => [
            'required' => '公司欄位為必填項。',
        ],
        'mobile' => [
            'regex' => '手機號碼格式無效。',
        ],
        'address' => [
            'required' => '地址欄位為必填項。',
        ],
        'zip' => [
            'required' => '郵遞區號欄位為必填項。',
            'min' => '郵遞區號至少需 5 位數。',
            'numeric' => '郵遞區號必須為數字。',
        ],
        'email' => [
            'required' => '電子郵件欄位為必填項。',
            'email' => '電子郵件必須是有效的地址。',
            'unique' => '該電子郵件已被使用。',
        ],
    ],
    'contact_request' => [
        'conName' => '姓名欄位為必填項。',
        'email' => '電子郵件欄位為必填項。',
        'conmessage' => '訊息欄位為必填項。',
        'Mobile' => '手機欄位為必填項。',
        'country_code' => '手機欄位為必填項。',
        'demoname' => '姓名欄位為必填項。',
        'demomessage' => '訊息欄位為必填項。',
        'demoemail' => '電子郵件欄位為必填項。',
        'congg-recaptcha-response-1.required' => '機器人驗證失敗，請再試一次。',
        'demo-recaptcha-response-1.required' => '機器人驗證失敗，請再試一次。',
    ],
    'frontend_pages' => [
        'name' => [
            'required' => '名稱欄位為必填項。',
            'unique' => '此名稱已存在。',
            'max' => '名稱不得超過 20 個字元。',
            'regex' => '名稱只能包含字母與空格。',
        ],
        'publish' => [
            'required' => '發布欄位為必填項。',
        ],
        'slug' => [
            'required' => 'Slug 欄位為必填項。',
            'unique' => '這個 slug 已經存在。',
        ],
        'url' => [
            'required' => 'URL 欄位為必填項。',
            'url' => 'URL 必須是有效的連結。',
            'regex' => 'URL 格式無效。',
        ],
        'content' => [
            'required' => '內容欄位為必填項。',
        ],
        'created_at' => [
            'required' => '建立時間欄位為必填項。',
        ],
        'parent_page_id' => [
            'exists' => '所選的父頁面不存在。',
            'self' => '頁面不能是自己的父頁面。',
            'nested' => '所選頁面已經是子頁面，不能用作父頁面。',
        ],
    ],
    'order_form' => [
        'client' => [
            'required' => '客戶欄位為必填項。',
        ],
        'payment_method' => [
            'required' => '付款方式欄位為必填項。',
        ],
        'promotion_code' => [
            'required' => '優惠碼欄位為必填項。',
        ],
        'order_status' => [
            'required' => '訂單狀態欄位為必填項。',
        ],
        'product' => [
            'required' => '產品欄位為必填項。',
        ],
        'subscription' => [
            'required' => '訂閱欄位為必填項。',
        ],
        'price_override' => [
            'numeric' => '價格覆蓋必須為數值。',
        ],
        'qty' => [
            'integer' => '數量必須為整數。',
        ],
    ],
    'coupon_form' => [
        'code' => [
            'required' => '優惠碼欄位為必填項。',
            'string' => '優惠碼必須為字串。',
            'max' => '優惠碼不得超過 255 個字元。',
        ],
        'type' => [
            'required' => '類型欄位為必填項。',
            'in' => '類型無效。允許的值為：percentage、other_type。',
        ],
        'applied' => [
            'required' => '產品適用欄位為必填項。',
            'date' => '產品適用欄位必須是有效日期。',
        ],
        'uses' => [
            'required' => '使用次數欄位為必填項。',
            'numeric' => '使用次數必須為數字。',
            'min' => '使用次數至少為 :min。',
        ],
        'start' => [
            'required' => '開始日期欄位為必填項。',
            'date' => '開始日期必須是有效日期。',
        ],
        'expiry' => [
            'required' => '到期日欄位為必填項。',
            'date' => '到期日必須是有效日期。',
            'after' => '到期日必須在開始日期之後。',
        ],
        'value' => [
            'required' => '折扣值欄位為必填項。',
            'numeric' => '折扣值必須為數字。',
            'between' => '如果類型為百分比，折扣值必須介於 :min 到 :max 之間。',
            'max' => '折扣金額不能超過申請商品的價格（:max）。',
        ],
    ],
    'tax_form' => [
        'name' => [
            'required' => '名稱欄位為必填項。',
        ],
        'rate' => [
            'required' => '稅率欄位為必填項。',
            'numeric' => '稅率必須為數字。',
            'decimal' => '匯率最多保留 3 位小數。',
            'max' => '比率不得大於999.999。',
        ],
        'priority' => [
            'required' => '優先權欄位為必填項。',
            'min' => '優先順序必須至少為1。',
        ],
        'level' => [
            'required' => '層級欄位為必填項。',
            'integer' => '層級必須為整數。',
        ],
        'country' => [
            'required' => '國家欄位為必填項。',
        ],
        'state' => [
            'required' => '州/省欄位為必填項。',
        ],
    ],
    'subscription_form' => [
        'name' => [
            'required' => '名稱欄位為必填項。',
        ],
        'subscription' => [
            'required' => '訂閱欄位為必填項。',
        ],
        'regular_price' => [
            'required' => '原價欄位為必填項。',
            'numeric' => '原價必須是數字。',
        ],
        'selling_price' => [
            'required' => '銷售價格欄位為必填項。',
            'numeric' => '銷售價格必須是數字。',
        ],
        'products' => [
            'required' => '產品欄位為必填項。',
        ],
    ],
    'bundle' => [
        'name' => [
            'required' => '名稱欄位為必填項。',
        ],
        'items' => [
            'required' => '每項產品都是必填的。',
        ],
    ],
    'group' => [
        'name' => [
            'required' => '名稱為必填項',
            'unique' => '該名稱已存在。',
        ],
        'pricing_templates_id' => [
            'required' => '需要設計模板。',
        ],
        'features' => [
            'name' => [
                'required' => '所有功能欄位皆為必填項',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => '價格為必填項',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => '數值為必填項',
            ],
        ],
        'type' => [
            'required_with' => '類型為必填項',
        ],
        'title' => [
            'required_with' => '標題為必填項',
        ],
    ],
    'product' => [
        'name' => [
            'required' => '名稱欄位為必填項。',
        ],
        'type' => [
            'required' => '類型欄位為必填項。',
        ],
        'product_type' => [
            'required' => '產品類別欄位為必填項。',
        ],
        'group' => [
            'required' => '群組欄位為必填項。',
        ],
        'subscription' => [
            'required' => '訂閱欄位為必填項。',
        ],
        'currency' => [
            'required' => '貨幣欄位為必填項。',
        ],
        'file' => [
            'required_without_all' => '如果未提供 GitHub 擁有者或儲存庫，檔案欄位為必填項。',
            'mimes' => '檔案必須是 ZIP 檔案。',
        ],
        'image' => [
            'required_without_all' => '如果未提供 GitHub 擁有者或儲存庫，圖片欄位為必填項。',
            'mimes' => '圖片必須是 PNG 檔案。',
        ],
        'github_owner' => [
            'required' => 'GitHub 所有者欄位為必填項。',
            'required_without_all' => '如果未提供檔案或圖片，GitHub 擁有者欄位為必填項。',
        ],
        'github_repository' => [
            'required' => 'GitHub 儲存庫欄位為必填項。',
            'required_without_all' => '如果未提供檔案或圖片，GitHub 儲存庫欄位為必填項。',
            'required_if' => '當類型為 2 時，GitHub 儲存庫欄位為必填項。',
        ],
        'shoping_cart_link' => [
            'required' => '購物車連結欄位為必填項。',
        ],
    ],
    'users' => [
        'first_name' => [
            'required' => '名字欄位為必填項。',
        ],
        'last_name' => [
            'required' => '姓氏欄位為必填項。',
        ],
        'company' => [
            'required' => '公司欄位為必填項。',
        ],
        'email' => [
            'required' => '電子郵件欄位為必填項。',
            'email' => '電子郵件必須是有效的地址。',
            'unique' => '該電子郵件已被使用。',
        ],
        'address' => [
            'required' => '地址欄位為必填項。',
        ],
        'mobile' => [
            'required' => '手機欄位為必填項。',
        ],
        'country' => [
            'required' => '國家欄位為必填項。',
            'exists' => '所選國家無效。',
        ],
        'state' => [
            'required_if' => '當國家為印度時，州欄位為必填項。',
        ],
        'timezone_id' => [
            'required' => '時區欄位為必填項。',
        ],
        'user_name' => [
            'required' => '使用者名稱欄位為必填項。',
            'unique' => '該使用者名稱已被使用。',
        ],
        'zip' => [
            'regex' => '當國家為印度時，州欄位為必填項。',
        ],
        'gstin' => [
            'regex' => 'GSTIN 格式無效。',
        ],
    ],
    'profile_form' => [
        'first_name' => [
            'required' => '名字為必填項。',
            'min' => '名字至少需 :min 個字元。',
            'max' => '名字不得超過 :max 個字元。',
        ],
        'last_name' => [
            'required' => '姓氏為必填項。',
            'max' => '姓氏不得超過 :max 個字元。',
        ],
        'company' => [
            'required' => '公司名稱為必填項。',
            'max' => '公司名稱不得超過 :max 個字元。',
        ],
        'email' => [
            'required' => '電子郵件為必填項。',
            'email' => '請輸入有效的電子郵件地址。',
            'unique' => '該電子郵件地址已被使用，請選擇其他電子郵件。',
        ],
        'mobile' => [
            'required' => '手機號碼為必填項。',
            'regex' => '請輸入有效的手機號碼。',
            'min' => '手機號碼至少需 :min 個字元。',
            'max' => '手機號碼不得超過 :max 個字元。',
        ],
        'address' => [
            'required' => '地址為必填項。',
        ],
        'user_name' => [
            'required' => '需要輸入使用者名稱。',
            'unique' => '該用戶名已被使用。',
        ],
        'timezone_id' => [
            'required' => '需要時區。',
        ],
        'country' => [
            'required' => '國家為必填項。',
            'exists' => '選擇的國家無效。',
        ],
        'state' => [
            'required_if' => '該國家需要州字段。',
        ],
        'gstin' => [
            'regex' => 'GSTIN 格式無效。',
        ],
        'old_password' => [
            'required' => '需要舊密碼。',
            'min' => '舊密碼必須至少包含 :min 個字元。',
        ],
        'new_password' => [
            'required' => '需要新密碼。',
            'different' => '新密碼必須與舊密碼不同。',
        ],
        'confirm_password' => [
            'required' => '需要確認密碼。',
            'same' => '確認密碼必須與新密碼一致。',
        ],
        'terms' => [
            'required' => '您必須接受條款。',
        ],
        'password' => [
            'required' => '需要密碼。',
        ],
        'password_confirmation' => [
            'required' => '需要確認密碼。',
            'same' => '密碼不符。',
        ],
        'mobile_code' => [
            'required' => '輸入國家代碼（手機）',
        ],
    ],
    'invoice' => [
        'user' => [
            'required' => '必須填寫客戶欄位。',
        ],
        'date' => [
            'required' => '必須填寫日期欄位。',
            'date' => '日期必須是有效的日期格式。',
        ],
        'domain' => [
            'required' => '域字段為必填項。',
            'regex' => '域名格式無效。',
        ],
        'cloud_domain' => [
            'required' => '雲域欄位為必填項。',
            'regex' => '只能使用字母、數字和連字號。',
        ],
        'plan' => [
            'required_if' => '訂閱欄位必須填寫。',
        ],
        'price' => [
            'required' => '必須填寫價格欄位。',
        ],
        'product' => [
            'required' => '必須填寫產品欄位。',
        ],
    ],
    'domain_form' => [
        'domain' => [
            'required' => '必須填寫域名欄位。',
            'url' => '域名必須是有效的URL。',
        ],
    ],
    'product_renewal' => [
        'domain' => [
            'required' => '必須填寫域名欄位。',
            'no_http' => '域名不得包含 "http" 或 "https"。',
        ],
    ],
    'language' => [
        'required' => '必須填寫語言欄位。',
        'invalid' => '選擇的語言無效。',
    ],
    'storage_path' => [
        'disk' => [
            'required' => '必須填寫存儲磁碟欄位。',
            'string' => '磁碟必須是字串。',
        ],
        'path' => [
            'required' => '儲存路徑欄位為必填項。',
            'string' => '路徑必須是字串。',
            'nullable' => '路徑欄位是可選的。',
            'invalid' => '路徑不存在或不可寫入。',
        ],
    ],
    'pdf_settings' => [
        'node_path' => [
            'required' => '節點路徑欄位為必填項。',
            'string' => '節點路徑必須是有效的字串。',
        ],
        'npm_path' => [
            'required' => 'npm 路徑欄位是必需的。',
            'string' => 'npm 路徑必須是有效的字串。',
        ],
        'chrome_path' => [
            'required' => '鍍鉻路徑欄位是必需的。',
            'string' => 'chrome 路徑必須是有效的字串。',
            'invalid' => 'chrome路徑不存在或不可執行。',
        ],
    ],
    'validate_secret' => [
        'totp' => [
            'required' => '請輸入驗證碼',
            'digits' => '請輸入有效的6位數驗證碼',
        ],
    ],
    'verify_email' => [
        'required' => '必須填寫電子郵件欄位。',
        'email' => '電子郵件必須是有效的電子郵件地址。',
        'verify_email' => '電子郵件驗證失敗。',
    ],
    'verify_country_code' => [
        'required' => '必須填寫國家代碼。',
        'numeric' => '國家代碼必須是有效的數字。',
        'verify_country_code' => '國家代碼驗證失敗。',
    ],
    'verify_number' => [
        'required' => '必須輸入數字。',
        'numeric' => '數字必須是有效的數字。',
        'verify_number' => '數字驗證失敗。',
    ],
    'password_otp' => [
        'required' => '必須輸入密碼。',
        'password' => '密碼不正確。',
        'invalid' => '密碼無效。',
    ],
    'auth_controller' => [
        'name_required' => '必須輸入姓名。',
        'name_max' => '姓名不得超過255個字符。',
        'email_required' => '必須輸入電子郵件。',
        'email_email' => '請輸入有效的電子郵件地址。',
        'email_max' => '電子郵件不得超過255個字符。',
        'email_unique' => '此電子郵件已被註冊。',
        'password_required' => '必須輸入密碼。',
        'password_confirmed' => '密碼確認不匹配。',
        'password_min' => '密碼必須至少包含6個字符。',
    ],
    'resend_otp' => [
        'eid_required' => '必須填寫EID欄位。',
        'eid_string' => 'EID必須是字串。',
        'type_required' => '必須填寫類型欄位。',
        'type_string' => '類型必須是字串。',
        'type_in' => '選擇的類型無效。',
    ],
    'verify_otp' => [
        'eid_required' => '必須填寫員工ID。',
        'eid_string' => '員工ID必須是字串。',
        'otp_required' => '必須填寫OTP。',
        'otp_size' => 'OTP必須正好為6個字符。',
        'recaptcha_required' => '請完成驗證碼。',
        'recaptcha_size' => '驗證碼回應無效。',
    ],
    'company_validation' => [
        'company_required' => '公司名稱是必填欄位。',
        'company_string' => '公司名稱必須是文本。',
        'address_required' => '地址是必填欄位。',
        'address_string' => '地址必須是文本。',
    ],
    'token_validation' => [
        'token_required' => '必須填寫令牌。',
        'password_required' => '必須填寫密碼欄位。',
        'password_confirmed' => '密碼確認不匹配。',
    ],
    'custom_email' => [
        'required' => '必須填寫電子郵件欄位。',
        'email' => '請輸入有效的電子郵件地址。',
        'exists' => '此電子郵件未在我們這裡註冊。',
    ],
    'newsletterEmail' => [
        'required' => '必須填寫電子報電子郵件欄位。',
        'email' => '請輸入有效的電子郵件地址來訂閱電子報。',
    ],
    'widget' => [
        'name_required' => '必須輸入名稱。',
        'name_max' => '名稱不得超過50個字符。',
        'publish_required' => '必須選擇發布狀態。',
        'type_required' => '必須選擇類型。',
        'type_unique' => '此類型已存在。',
    ],
    'payment' => [
        'payment_date_required' => '必須填寫付款日期。',
        'payment_method_required' => '必須選擇付款方式。',
        'amount_required' => '必須填寫金額。',
    ],
    'custom_date' => [
        'date_required' => '必須填寫日期欄位。',
        'total_required' => '必須填寫總金額欄位。',
        'status_required' => '必須填寫狀態欄位。',
    ],
    'plan_renewal' => [
        'plan_required' => '必須選擇方案。',
        'payment_method_required' => '必須選擇付款方式。',
        'cost_required' => '必須填寫費用欄位。',
        'code_not_valid' => '促銷代碼無效。',
    ],
    'rate' => [
        'required' => '必須填寫費率。',
        'numeric' => '費率必須是數字。',
    ],
    'product_validate' => [
        'producttitle_required' => '產品標題是必需的。',
        'version_required' => '版本是必需的。',
        'filename_required' => '請上傳文件。',
        'dependencies_required' => '依賴欄位是必需的。',
        'description_required' => '需要描述。',
        'release_type_required' => '需要釋放類型。',
    ],
    'product_sku_unique' => '產品 SKU 必須是唯一的。',
    'product_name_unique' => '名稱必須是唯一的。',
    'product_show_agent_required' => '選擇您的購物車頁面偏好。',
    'config_file_path_regex' => '必須是不帶 ../ 段的相對路徑。',
    'license_file_path_regex' => '必須是不帶 ../ 段的相對路徑。',
    'product_controller' => [
        'name_required' => '產品名稱是必需的。',
        'name_unique' => '名稱必須是唯一的。',
        'name_unique_in_group' => '所選群組中已存在同名產品。請選擇其他名稱或選擇其他群組。',
        'type_required' => '產品類型是必需的。',
        'description_required' => '產品描述是必需的。',
        'product_description_required' => '詳細的產品描述是必需的。',
        'short_description_required' => '需要簡短描述。',
        'image_mimes' => '圖片必須是以下類型的文件：jpeg, png, jpg。',
        'image_max' => '圖片大小不能超過 2048 KB。',
        'product_sku_required' => '產品 SKU 是必需的。',
        'group_required' => '產品組別是必需的。',
        'show_agent_required' => '選擇您的購物車頁面偏好。',
    ],
    'current_domain_required' => '當前域名是必需的。',
    'new_domain_required' => '新域名是必需的。',
    'special_characters_not_allowed' => '域名中不允許使用特殊字符。',
    'orderno_required' => '訂單號是必需的。',
    'cloud_central_domain_required' => '雲端中央域名是必需的。',
    'cloud_cname_required' => '雲端 CNAME 是必需的。',
    'cloud_tenant' => [
        'cloud_top_message_required' => '雲端頂部訊息是必需的。',
        'cloud_label_field_required' => '雲端標籤欄位是必需的。',
        'cloud_label_radio_required' => '雲端標籤單選框是必需的。',
        'cloud_product_required' => '雲端產品是必需的。',
        'cloud_product_unique' => '本產品已有雲端配置。',
        'cloud_free_plan_required' => '雲端免費方案是必需的。',
        'cloud_free_plan_invalid' => '所選方案不屬於所​​選產品。',
        'cloud_product_key_required' => '雲端產品金鑰是必需的。',
    ],
    'reg_till_after' => '註冊結束日期必須晚於註冊開始日期。',
    'extend_product' => [
        'title_required' => '標題欄位是必需的。',
        'version_required' => '版本欄位是必需的。',
        'dependencies_required' => '依賴欄位是必需的。',
    ],
    'please_enter_recovery_code' => '請輸入恢復代碼。',
    'social_login' => [
        'client_id_required' => 'Google、Github 或 Linkedin 需要客戶端 ID。',
        'client_secret_required' => 'Google、Github 或 Linkedin 需要客戶端密鑰。',
        'api_key_required' => 'Twitter 需要 API 金鑰。',
        'api_secret_required' => 'Twitter 需要 API 密鑰。',
        'redirect_url_required' => '重定向 URL 是必需的。',
    ],
    'thirdparty_api' => [
        'app_name_required' => '應用程式名稱是必需的。',
        'app_key_required' => '應用程式金鑰是必需的。',
        'app_key_size' => '應用程式金鑰必須恰好為 32 個字符。',
        'app_secret_required' => '應用程式密鑰是必需的。',
    ],
    'plan_request' => [
        'name_required' => '名稱欄位為必填項目',
        'product_quant_req' => '當未填寫代理人數時，產品數量欄位為必填項目。',
        'no_agent_req' => '當未填寫產品數量時，代理人數欄位為必填項目。',
        'pro_req' => '產品欄位為必填項目',
        'offer_price' => '優惠價格不得高於 100',
        'currency_duplicate' => '每種貨幣只能使用一次。',
        'non_negative' => '該值不能為負數。',
    ],
    'razorpay_val' => [
        'business_required' => '商業欄位為必填。',
        'cmd_required' => '命令欄位為必填。',
        'paypal_url_required' => '必須提供 PayPal 網址。',
        'paypal_url_invalid' => 'PayPal 網址必須是有效的網址。',
        'success_url_invalid' => '成功網址必須是有效的網址。',
        'cancel_url_invalid' => '取消網址必須是有效的網址。',
        'notify_url_invalid' => '通知網址必須是有效的網址。',
        'currencies_required' => '貨幣欄位為必填。',
    ],
    'login_failed' => '登入失敗，請檢查您輸入的電子郵件/使用者名稱和密碼是否正確。',
    'forgot_email_validation' => '如果您提供的電子郵件已註冊，您將很快收到一封包含重設密碼指示的電子郵件。',
    'too_many_login_attempts' => '由於多次登入失敗，您已被鎖定，請在 :time 後重試。',
    'phone_number' => '請輸入有效的手機號碼。',
    'mobile_number' => ':attribute 必須是有效的手機號碼。',
    'license' => [
        'product' => [
            'required' => '產品欄位為必填項。',
        ],
        'client' => [
            'required' => '客戶欄位為必填項。',
        ],
        'license_code' => [
            'required' => '許可證代碼欄位為必填項。',
        ],
        'license_expire_date' => [
            'required' => '許可證到期日欄位為必填項。',
        ],
        'license_updates_date' => [
            'required' => '更新到期日欄位為必填項。',
        ],
        'license_support_date' => [
            'required' => '支援到期日期欄位為必填項。',
        ],
        'banned_host_ip' => [
            'required' => '禁止的主機IP 欄位為必填項。',
            'invalid' => '請輸入有效的IP位址。',
        ],
        'installation_ip' => [
            'required' => '安裝IP 欄位為必填項。',
        ],
        'notification_field' => [
            'required' => '此通知欄位為必填項。',
        ],
    ],
];
