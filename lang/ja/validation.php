<?php

return [
    'accepted'             => ':attributeを承認してください。',
    'accepted_if'          => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url'           => ':attributeが有効なURLではありません。',
    'after'                => ':attributeには、:dateより後の日付を指定してください。',
    'after_or_equal'       => ':attributeには、:date以降の日付を指定してください。',
    'alpha'                => ':attributeはアルファベットのみがご利用できます。',
    'alpha_dash'           => ':attributeはアルファベットとダッシュ(-)及び下線(_)がご利用できます。',
    'alpha_num'            => ':attributeはアルファベット数字がご利用できます。',
    'array'                => ':attributeは配列でなくてはなりません。',
    'before'               => ':attributeには、:dateより前の日付をご利用ください。',
    'before_or_equal'      => ':attributeには、:date以前の日付をご利用ください。',
    'between'              => [
        'array'   => ':attributeは:minから:maxの間で指定してください。',
        'file'    => ':attributeは:minから:maxキロバイトの間で指定してください。',
        'numeric' => ':attributeは:minから:maxの間で指定してください。',
        'string'  => ':attributeは:minから:max文字の間で指定してください。',
    ],
    'boolean'              => ':attributeはtrueまたはfalseでなければなりません。',
    'confirmed'            => ':attributeの確認が一致しません。',
    'current_password'     => 'パスワードが間違っています。',
    'date'                 => ':attributeが有効な日付ではありません。',
    'date_equals'          => ':attributeには、:dateと同じ日付を指定してください。',
    'date_format'          => ':attributeは、:formatの形式と一致しません。',
    'declined'             => ':attributeは拒否されなければなりません。',
    'declined_if'          => ':otherが:valueの場合、:attributeは拒否されなければなりません。',
    'different'            => ':attributeと:otherは異なっていなければなりません。',
    'digits'               => ':attributeは:digits桁でなければなりません。',
    'digits_between'       => ':attributeは:minから:max桁の間で指定してください。',
    'dimensions'           => ':attributeの画像の寸法が無効です。',
    'distinct'             => ':attributeのフィールドに重複した値があります。',
    'doesnt_start_with'    => ':attributeは以下のいずれかで始めることはできません: :values。',
    'email'                => ':attributeは有効なメールアドレスでなければなりません。',
    'ends_with'            => ':attributeは以下のいずれかで終わらなければなりません: :values。',
    'enum'                 => '選択された:attributeは無効です。',
    'exists'               => '選択された:attributeは無効です。',
    'file'                 => ':attributeはファイルでなければなりません。',
    'filled'               => ':attributeのフィールドには値が必要です。',
    'gt'                   => [
        'array'   => ':attributeは:value個より多くの項目を含まなければなりません。',
        'file'    => ':attributeは:valueキロバイトより大きくなければなりません。',
        'numeric' => ':attributeは:valueより大きくなければなりません。',
        'string'  => ':attributeは:value文字より多くなければなりません。',
    ],
    'gte'                  => [
        'array'   => ':attributeは:value個以上の項目を含まなければなりません。',
        'file'    => ':attributeは:valueキロバイト以上でなければなりません。',
        'numeric' => ':attributeは:value以上でなければなりません。',
        'string'  => ':attributeは:value文字以上でなければなりません。',
    ],
    'image'                => ':attributeは画像でなければなりません。',
    'in'                   => '選択された:attributeは無効です。',
    'in_array'             => ':attributeのフィールドは:otherに存在しません。',
    'integer'              => ':attributeは整数でなければなりません。',
    'ip'                   => ':attributeは有効なIPアドレスでなければなりません。',
    'ipv4'                 => ':attributeは有効なIPv4アドレスでなければなりません。',
    'ipv6'                 => ':attributeは有効なIPv6アドレスでなければなりません。',
    'json'                 => ':attributeは有効なJSON文字列でなければなりません。',
    'lt'                   => [
        'array'   => ':attributeは:value個より少ない項目を含まなければなりません。',
        'file'    => ':attributeは:valueキロバイトより小さくなければなりません。',
        'numeric' => ':attributeは:valueより小さくなければなりません。',
        'string'  => ':attributeは:value文字より少なくなければなりません。',
    ],
    'lte'                  => [
        'array'   => ':attributeは:value個以下の項目を含まなければなりません。',
        'file'    => ':attributeは:valueキロバイト以下でなければなりません。',
        'numeric' => ':attributeは:value以下でなければなりません。',
        'string'  => ':attributeは:value文字以下でなければなりません。',
    ],
    'mac_address'          => ':attributeは有効なMACアドレスでなければなりません。',
    'max'                  => [
        'array'   => ':attributeは:max個以下の項目を含んではいけません。',
        'file'    => ':attributeは:maxキロバイト以下でなければなりません。',
        'numeric' => ':attributeは:max以下でなければなりません。',
        'string'  => ':attributeは:max文字以下でなければなりません。',
    ],
    'mimes'                => ':attributeは以下のタイプのファイルでなければなりません: :values。',
    'mimetypes'            => ':attributeは以下のタイプのファイルでなければなりません: :values。',
    'min' => [
        'array' => ':attribute は少なくとも :min アイテムを含んでいる必要があります。',
        'file' => ':attribute は少なくとも :min キロバイトである必要があります。',
        'numeric' => ':attribute は少なくとも :min である必要があります。',
        'string' => ':attribute は少なくとも :min 文字である必要があります。',
    ],
    'multiple_of' => ':attributeは:valueの倍数でなければなりません。',
    'not_in' => '選択された:attributeは無効です。',
    'not_regex' => ':attributeの形式が無効です。',
    'numeric' => ':attributeは数値でなければなりません。',
    'password' => [
        'letters' => ':attributeには少なくとも1つの文字を含める必要があります。',
        'mixed' => ':attributeには少なくとも1つの大文字と1つの小文字を含める必要があります。',
        'numbers' => ':attributeには少なくとも1つの数字を含める必要があります。',
        'symbols' => ':attributeには少なくとも1つの記号を含める必要があります。',
        'uncompromised' => '入力された:attributeはデータ漏洩で確認されています。別の:attributeを選んでください。',
    ],
    'present' => ':attributeフィールドが存在している必要があります。',
    'prohibited' => ':attributeフィールドは禁止されています。',
    'prohibited_if' => ':otherが:valueの場合、:attributeフィールドは禁止されています。',
    'prohibited_unless' => ':otherが:valuesに含まれていない限り、:attributeフィールドは禁止されています。',
    'prohibits' => ':attributeフィールドは:otherの入力を禁止します。',
    'regex' => ':attributeの形式が無効です。',
    'required' => ':attributeフィールドは必須です。',
    'required_array_keys' => ':attributeフィールドには以下の項目を含める必要があります: :values。',
    'required_if' => ':otherが:valueの場合、:attributeフィールドは必須です。',
    'required_unless' => ':otherが:valuesに含まれていない場合、:attributeフィールドは必須です。',
    'required_with' => ':valuesが存在する場合、:attributeフィールドは必須です。',
    'required_with_all' => ':valuesがすべて存在する場合、:attributeフィールドは必須です。',
    'required_without' => ':valuesが存在しない場合、:attributeフィールドは必須です。',
    'required_without_all' => ':valuesが一つも存在しない場合、:attributeフィールドは必須です。',
    'same' => ':attributeと:otherは一致しなければなりません。',
    'size' => [
        'array' => ':attributeは:size個の項目を含んでいなければなりません。',
        'file' => ':attributeは:sizeキロバイトでなければなりません。',
        'numeric' => ':attributeは:sizeでなければなりません。',
        'string' => ':attributeは:size文字でなければなりません。',
    ],
    'starts_with' => ':attributeは次のいずれかで始まっている必要があります: :values。',
    'string' => ':attributeは文字列でなければなりません。',
    'timezone' => ':attributeは有効なタイムゾーンでなければなりません。',
    'unique' => ':attributeは既に使用されています。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'url' => ':attributeは有効なURLでなければなりません。',
    'uuid' => ':attributeは有効なUUIDでなければなりません。',
    'attributes' => [],
    'publish_date_required' => '公開日が必須です。',
    'price_numeric_value' => '価格は数値でなければなりません。',
    'quantity_integer_value' => '数量は整数でなければなりません。',
    'order_has_Expired' => '注文の有効期限が切れました。',
    'expired' => '期限切れ',

    /*
    Request file custom validation messages
    */

    // Common
    'settings_form' => [
        'company' => [
            'required' => '会社名の入力は必須です。',
        ],
        'website' => [
            'url' => 'ウェブサイトは有効なURLである必要があります。',
        ],
        'phone' => [
            'regex' => '電話番号の形式が無効です。',
        ],
        'address' => [
            'required' => '住所の入力は必須です。',
            'max' => '住所は300文字以内で入力してください。',
        ],
        'logo' => [
            'mimes' => 'ロゴはPNGファイルである必要があります。',
        ],
        'driver' => [
            'required' => 'ドライバの入力は必須です。',
        ],
        'port' => [
            'integer' => 'ポートは整数でなければなりません。',
        ],
        'email' => [
            'required' => 'メールアドレスの入力は必須です。',
            'email' => '有効なメールアドレスを入力してください。',
        ],
        'password' => [
            'required' => 'パスワードの入力は必須です。',
        ],
        'error_email' => [
            'email' => 'エラーメールは有効なメールアドレスでなければなりません。',
        ],
    ],

    'settings_forms' => [
        'company' => [
            'required' => '会社名の入力は必須です。',
            'max' => '会社名は50文字以内で入力してください。',
        ],
        'company_email' => [
            'required' => '会社のメールアドレスの入力は必須です。',
            'email' => '有効な会社のメールアドレスを入力してください。',
        ],
        'title' => [
            'max' => 'タイトルは50文字以内で入力してください。',
        ],
        'website' => [
            'required' => 'ウェブサイトのURLは必須です。',
            'url' => 'ウェブサイトは有効なURLでなければなりません。',
            'regex' => 'ウェブサイトの形式が無効です。',
        ],
        'phone' => [
            'required' => '電話番号の入力は必須です。',
        ],
        'address' => [
            'required' => '住所の入力は必須です。',
        ],
        'state' => [
            'required' => '都道府県の入力は必須です。',
        ],
        'country' => [
            'required' => '国名の入力は必須です。',
        ],
        'gstin' => [
            'max' => 'GSTINは15文字以内で入力してください。',
        ],
        'default_currency' => [
            'required' => 'デフォルト通貨の入力は必須です。',
        ],
        'admin_logo' => [
            'mimes' => '管理者ロゴはjpeg、png、jpg形式のファイルである必要があります。',
            'max' => '管理者ロゴは2MB以下である必要があります。',
        ],
        'fav_icon' => [
            'mimes' => 'ファビコンはjpeg、png、jpg形式のファイルである必要があります。',
            'max' => 'ファビコンは2MB以下である必要があります。',
        ],
        'logo' => [
            'mimes' => 'ロゴはjpeg、png、jpg形式のファイルである必要があります。',
            'max' => 'ロゴは2MB以下である必要があります。',
        ],
    ],

    'social_media_form' => [
        'name' => [
            'required' => '名前の入力は必須です。',
            'unique' => 'この名前はすでに存在します。',
            'max' => '名前は50文字以内で入力してください。',
        ],
        'link' => [
            'required' => 'リンクの入力は必須です。',
            'url' => 'リンクは有効なURLである必要があります。',
            'regex' => 'リンクの形式が無効です。',
        ],
    ],

    // Email
    'custom' => [
        'password' => [
            'required_if' => '選択したメールドライバにはパスワードの入力が必要です。',
        ],
        'port' => [
            'required_if' => 'SMTPを使用する場合、ポートの入力が必要です。',
        ],
        'encryption' => [
            'required_if' => 'SMTPを使用する場合、暗号化方式の入力が必要です。',
        ],
        'host' => [
            'required_if' => 'SMTPを使用する場合、ホストの入力が必要です。',
        ],
        'secret' => [
            'required_if' => '選択したメールドライバにはシークレットの入力が必要です。',
        ],
        'domain' => [
            'required_if' => 'Mailgunを使用する場合、ドメインの入力が必要です。',
        ],
    ],
    'key' => [
        'required_if' => 'SESにはキーの入力が必要です。',
    ],
    'region' => [
        'required_if' => 'SESにはリージョンの入力が必要です。',
    ],
    'email' => [
        'required_if' => '選択されたメールドライバにはメールアドレスの入力が必要です。',
        'required' => 'メールアドレスの入力は必須です。',
        'email' => '有効なメールアドレスを入力してください。',
        'not_matching' => 'メールのドメインは現在のサイトのドメインと一致している必要があります。',
    ],
    'driver' => [
        'required' => 'ドライバの入力は必須です。',
    ],

    'customer_form' => [
        'first_name' => [
            'required' => '名の入力は必須です。',
        ],
        'last_name' => [
            'required' => '姓の入力は必須です。',
        ],
        'company' => [
            'required' => '会社名の入力は必須です。',
        ],
        'mobile' => [
            'regex' => '携帯番号の形式が無効です。',
        ],
        'address' => [
            'required' => '住所の入力は必須です。',
        ],
        'zip' => [
            'required' => '郵便番号の入力は必須です。',
            'min' => '郵便番号は少なくとも5桁である必要があります。',
            'numeric' => '郵便番号は数値である必要があります。',
        ],
        'email' => [
            'required' => 'メールアドレスの入力は必須です。',
            'email' => '有効なメールアドレスを入力してください。',
            'unique' => 'このメールアドレスは既に使用されています。',
        ],
    ],

    'contact_request' => [
        'conName' => '名前の入力は必須です。',
        'email' => 'メールアドレスの入力は必須です。',
        'conmessage' => 'メッセージの入力は必須です。',
        'Mobile' => '携帯番号の入力は必須です。',
        'country_code' => '携帯番号の入力は必須です。',
        'demoname' => '名前の入力は必須です。',
        'demomessage' => 'メッセージの入力は必須です。',
        'demoemail' => 'メールアドレスの入力は必須です。',
        'congg-recaptcha-response-1.required' => 'ロボット検証に失敗しました。もう一度お試しください。',
        'demo-recaptcha-response-1.required' => 'ロボット検証に失敗しました。もう一度お試しください。',
    ],

    'frontend_pages' => [
        'name' => [
            'required' => '名前の入力は必須です。',
            'unique' => 'この名前は既に存在します。',
            'max' => '名前は20文字以内で入力してください。',
            'regex' => '名前には文字とスペースのみ使用できます。',
        ],
        'publish' => [
            'required' => '公開の入力は必須です。',
        ],
        'slug' => [
            'required' => 'スラッグの入力は必須です。',
        ],
        'url' => [
            'required' => 'URLの入力は必須です。',
            'url' => 'URLは有効なリンクである必要があります。',
            'regex' => 'URLの形式が無効です。',
        ],
        'content' => [
            'required' => 'コンテンツの入力は必須です。',
        ],
        'created_at' => [
            'required' => '作成日時の入力は必須です。',
        ],
    ],

    // Order form
    'order_form' => [
        'client' => [
            'required' => 'クライアントの入力は必須です。',
        ],
        'payment_method' => [
            'required' => '支払い方法の入力は必須です。',
        ],
        'promotion_code' => [
            'required' => 'プロモーションコードの入力は必須です。',
        ],
        'order_status' => [
            'required' => '注文ステータスの入力は必須です。',
        ],
        'product' => [
            'required' => '製品の入力は必須です。',
        ],
        'subscription' => [
            'required' => 'サブスクリプションの入力は必須です。',
        ],
        'price_override' => [
            'numeric' => '上書き価格は数値でなければなりません。',
        ],
        'qty' => [
            'integer' => '数量は整数でなければなりません。',
        ],
    ],
    //Payment form
    'coupon_form' => [
        'code' => [
            'required' => 'クーポンコードの入力は必須です。',
            'string' => 'クーポンコードは文字列でなければなりません。',
            'max' => 'クーポンコードは255文字以内でなければなりません。',
        ],
        'type' => [
            'required' => 'タイプの入力は必須です。',
            'in' => '無効なタイプです。許可されている値は：percentage、other_typeです。',
        ],
        'applied' => [
            'required' => '対象商品の入力は必須です。',
            'date' => '対象商品には有効な日付を指定してください。',
        ],
        'uses' => [
            'required' => '使用回数の入力は必須です。',
            'numeric' => '使用回数は数値でなければなりません。',
            'min' => '使用回数は最低でも:min必要です。',
        ],
        'start' => [
            'required' => '開始日の入力は必須です。',
            'date' => '開始日は有効な日付である必要があります。',
        ],
        'expiry' => [
            'required' => '有効期限の入力は必須です。',
            'date' => '有効期限は有効な日付である必要があります。',
            'after' => '有効期限は開始日より後でなければなりません。',
        ],
        'value' => [
            'required' => '割引額の入力は必須です。',
            'numeric' => '割引額は数値でなければなりません。',
            'between' => 'タイプがパーセンテージの場合、割引額は:minから:maxの間でなければなりません。',
        ],
    ],

    'tax_form' => [
        'name' => [
            'required' => '名前の入力は必須です。',
        ],
        'rate' => [
            'required' => '税率の入力は必須です。',
            'numeric' => '税率は数値でなければなりません。',
        ],
        'level' => [
            'required' => 'レベルの入力は必須です。',
            'integer' => 'レベルは整数でなければなりません。',
        ],
        'country' => [
            'required' => '国の入力は必須です。',
        ],
        'state' => [
            'required' => '都道府県の入力は必須です。',
        ],
    ],

    //Product
    'subscription_form' => [
        'name' => [
            'required' => '名前の入力は必須です。',
        ],
        'subscription' => [
            'required' => 'サブスクリプションの入力は必須です。',
        ],
        'regular_price' => [
            'required' => '通常価格の入力は必須です。',
            'numeric' => '通常価格は数値でなければなりません。',
        ],
        'selling_price' => [
            'required' => '販売価格の入力は必須です。',
            'numeric' => '販売価格は数値でなければなりません。',
        ],
        'products' => [
            'required' => '製品の入力は必須です。',
        ],
    ],

    'bundle' => [
        'name' => [
            'required' => '名前の入力は必須です。',
        ],
        'items' => [
            'required' => 'すべてのアイテムの入力は必須です。',
        ],
    ],

    'group' => [
        'name' => [
            'required' => '名前の入力は必須です。',
        ],
        'features' => [
            'name' => [
                'required' => 'すべての機能の入力は必須です。',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => '価格の入力は必須です。',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => '値の入力は必須です。',
            ],
        ],
        'type' => [
            'required_with' => 'タイプの入力は必須です。',
        ],
        'title' => [
            'required_with' => 'タイトルの入力は必須です。',
        ],
    ],

    'product' => [
        'name' => [
            'required' => '名前の入力は必須です。',
        ],
        'type' => [
            'required' => 'タイプの入力は必須です。',
        ],
        'group' => [
            'required' => 'グループの入力は必須です。',
        ],
        'subscription' => [
            'required' => 'サブスクリプションの入力は必須です。',
        ],
        'currency' => [
            'required' => '通貨の入力は必須です。',
        ],
        'file' => [
            'required_without_all' => 'github_ownerまたはgithub_repositoryのいずれも提供されていない場合、ファイルの入力は必須です。',
            'mimes' => 'ファイルはzip形式でなければなりません。',
        ],
        'image' => [
            'required_without_all' => 'github_ownerまたはgithub_repositoryのいずれも提供されていない場合、画像の入力は必須です。',
            'mimes' => '画像はPNG形式でなければなりません。',
        ],
        'github_owner' => [
            'required_without_all' => 'ファイルまたは画像が提供されていない場合、GitHubのオーナー情報の入力は必須です。',
        ],
        'github_repository' => [
            'required_without_all' => 'ファイルまたは画像が提供されていない場合、GitHubリポジトリの入力は必須です。',
            'required_if' => 'タイプが2の場合、GitHubリポジトリの入力は必須です。',
        ],
    ],
    //User
    'users' => [
        'first_name' => [
            'required' => '名は必須項目です。',
        ],
        'last_name' => [
            'required' => '姓は必須項目です。',
        ],
        'company' => [
            'required' => '会社名は必須項目です。',
        ],
        'email' => [
            'required' => 'メールアドレスは必須項目です。',
            'email' => '有効なメールアドレスを入力してください。',
            'unique' => 'このメールアドレスは既に使用されています。',
        ],
        'address' => [
            'required' => '住所は必須項目です。',
        ],
        'mobile' => [
            'required' => '携帯番号は必須項目です。',
        ],
        'country' => [
            'required' => '国は必須項目です。',
            'exists' => '選択された国が無効です。',
        ],
        'state' => [
            'required_if' => '国がインドの場合、都道府県は必須項目です。',
        ],
        'timezone_id' => [
            'required' => 'タイムゾーンは必須項目です。',
        ],
        'user_name' => [
            'required' => 'ユーザー名は必須項目です。',
            'unique' => 'このユーザー名は既に使用されています。',
        ],
        'zip' => [
            'regex' => '国がインドの場合、都道府県は必須項目です。',
        ],
    ],

    'profile_form' => [
        'first_name' => [
            'required' => '名は必須項目です。',
            'min' => '名は:min文字以上で入力してください。',
            'max' => '名は:max文字以内で入力してください。',
        ],
        'last_name' => [
            'required' => '姓は必須項目です。',
            'max' => '姓は:max文字以内で入力してください。',
        ],
        'company' => [
            'required' => '会社名は必須項目です。',
            'max' => '会社名は:max文字以内で入力してください。',
        ],
        'email' => [
            'required' => 'メールアドレスは必須項目です。',
            'email' => '有効なメールアドレスを入力してください。',
            'unique' => 'このメールアドレスは既に使用されています。別のメールアドレスを入力してください。',
        ],
        'mobile' => [
            'required' => '携帯番号は必須項目です。',
            'regex' => '有効な携帯番号を入力してください。',
            'min' => '携帯番号は最低でも:min文字でなければなりません。',
            'max' => '携帯番号は:max文字以内でなければなりません。',
        ],
        'address' => [
            'required' => '住所は必須項目です。',
        ],
        'user_name' => [
            'required' => 'ユーザー名は必須項目です。',
            'unique' => 'このユーザー名は既に使用されています。',
        ],
        'timezone_id' => [
            'required' => 'タイムゾーンは必須項目です。',
        ],
        'country' => [
            'required' => '国は必須項目です。',
            'exists' => '選択された国が無効です。',
        ],
        'state' => [
            'required_if' => '国がインドの場合、都道府県は必須項目です。',
        ],
        'old_password' => [
            'required' => '現在のパスワードは必須項目です。',
            'min' => '現在のパスワードは:min文字以上である必要があります。',
        ],
        'new_password' => [
            'required' => '新しいパスワードは必須項目です。',
            'different' => '新しいパスワードは現在のパスワードと異なる必要があります。',
        ],
        'confirm_password' => [
            'required' => 'パスワード確認は必須項目です。',
            'same' => '確認パスワードは新しいパスワードと一致する必要があります。',
        ],
        'terms' => [
            'required' => '利用規約に同意する必要があります。',
        ],
    ],
    'password' => [
        'required' => 'パスワードは必須項目です。',
    ],
    'password_confirmation' => [
        'required' => 'パスワードの確認は必須項目です。',
        'same' => 'パスワードが一致しません。',
    ],
    'mobile_code' => [
        'required' => '国コード（携帯）を入力してください。',
    ],

    //Invoice form
    'invoice' => [
        'user' => [
            'required' => '顧客フィールドは必須です。',
        ],
        'date' => [
            'required' => '日付フィールドは必須です。',
            'date' => '日付は有効な形式である必要があります。',
        ],
        'domain' => [
            'regex' => 'ドメインの形式が無効です。',
        ],
        'plan' => [
            'required_if' => 'サブスクリプションフィールドは必須です。',
        ],
        'price' => [
            'required' => '価格フィールドは必須です。',
        ],
        'product' => [
            'required' => '製品フィールドは必須です。',
        ],
    ],

    //LocalizedLicense form
    'domain_form' => [
        'domain' => [
            'required' => 'ドメインフィールドは必須です。',
            'url' => 'ドメインは有効なURLである必要があります。',
        ],
    ],

    //Product Renewal form
    'product_renewal' => [
        'domain' => [
            'required' => 'ドメインフィールドは必須です。',
            'no_http' => 'ドメインには"http"または"https"を含めないでください。',
        ],
    ],

    //Language form
    'language' => [
        'required' => '言語フィールドは必須です。',
        'invalid' => '選択された言語が無効です。',
    ],

    //UpdateSroragePathRequest form
    'storage_path' => [
        'disk' => [
            'required' => 'ストレージディスクフィールドは必須です。',
            'string' => 'ディスクは文字列である必要があります。',
        ],
        'path' => [
            'string' => 'パスは文字列である必要があります。',
            'nullable' => 'パスフィールドは省略可能です。',
        ],
    ],

    //ValidateSecretRequest form
    'validate_secret' => [
        'totp' => [
            'required' => 'コードを入力してください。',
            'digits' => '6桁の有効なコードを入力してください。',
        ],
    ],

    //VerifyOtp form
    'verify_email' => [
        'required' => 'メールアドレスは必須項目です。',
        'email' => '有効なメールアドレスを入力してください。',
        'verify_email' => 'メールアドレスの認証に失敗しました。',
    ],

    'verify_country_code' => [
        'required' => '国コードは必須項目です。',
        'numeric' => '国コードは有効な数値である必要があります。',
        'verify_country_code' => '国コードの認証に失敗しました。',
    ],

    'verify_number' => [
        'required' => '番号は必須項目です。',
        'numeric' => '番号は有効な数値である必要があります。',
        'verify_number' => '番号の認証に失敗しました。',
    ],

    'password_otp' => [
        'required' => 'パスワードフィールドは必須です。',
        'password' => 'パスワードが正しくありません。',
        'invalid' => '無効なパスワードです。',
    ],
    ];