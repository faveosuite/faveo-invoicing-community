<?php

return [

    'accepted' => '必须接受 :attribute。',
    'accepted_if' => '当 :other 为 :value 时，必须接受 :attribute。',
    'active_url' => ':attribute 不是一个有效的URL。',
    'after' => ':attribute 必须是一个在 :date 之后的日期。',
    'after_or_equal' => ':attribute 必须是一个在 :date 之后或等于 :date 的日期。',
    'alpha' => ':attribute 只能包含字母。',
    'alpha_dash' => ':attribute 只能包含字母、数字、破折号和下划线。',
    'alpha_num' => ':attribute 只能包含字母和数字。',
    'array' => ':attribute 必须是一个数组。',
    'before' => ':attribute 必须是一个在 :date 之前的日期。',
    'before_or_equal' => ':attribute 必须是一个在 :date 之前或等于 :date 的日期。',
    'between' => [
        'array' => ':attribute 必须包含 :min 到 :max 个项目。',
        'file' => ':attribute 必须介于 :min 到 :max KB 之间。',
        'numeric' => ':attribute 必须介于 :min 到 :max 之间。',
        'string' => ':attribute 必须包含 :min 到 :max 个字符。',
    ],
    'boolean' => ':attribute 字段必须为 true 或 false。',
    'confirmed' => ':attribute 确认不匹配。',
    'current_password' => '密码不正确。',
    'date' => ':attribute 不是一个有效的日期。',
    'date_equals' => ':attribute 必须是一个与 :date 相等的日期。',
    'date_format' => ':attribute 不匹配格式 :format。',
    'declined' => ':attribute 必须被拒绝。',
    'declined_if' => ':attribute 必须在 :other 为 :value 时被拒绝。',
    'different' => ':attribute 和 :other 必须不同。',
    'digits' => ':attribute 必须是 :digits 位数字。',
    'digits_between' => ':attribute 必须是 :min 到 :max 位数字。',
    'dimensions' => ':attribute 的图像尺寸无效。',
    'distinct' => ':attribute 字段有重复的值。',
    'doesnt_start_with' => ':attribute 不能以以下之一开头：:values。',
    'email' => ':attribute 必须是一个有效的电子邮件地址。',
    'ends_with' => ':attribute 必须以以下之一结尾：:values。',
    'enum' => '选择的 :attribute 无效。',
    'exists' => '选择的 :attribute 无效。',
    'file' => ':attribute 必须是一个文件。',
    'filled' => ':attribute 字段必须有一个值。',
    'gt' => [
        'array' => ':attribute 必须包含超过 :value 项。',
        'file' => ':attribute 必须大于 :value KB。',
        'numeric' => ':attribute 必须大于 :value。',
        'string' => ':attribute 必须包含超过 :value 个字符。',
    ],
    'gte' => [
        'array' => ':attribute 必须包含 :value 项或更多。',
        'file' => ':attribute 必须大于或等于 :value KB。',
        'numeric' => ':attribute 必须大于或等于 :value。',
        'string' => ':attribute 必须包含 :value 个或更多字符。',
    ],
    'image' => ':attribute 必须是一个图像。',
    'in' => '选择的 :attribute 无效。',
    'in_array' => ':attribute 字段不存在于 :other 中。',
    'integer' => ':attribute 必须是一个整数。',
    'ip' => ':attribute 必须是一个有效的IP地址。',
    'ipv4' => ':attribute 必须是一个有效的IPv4地址。',
    'ipv6' => ':attribute 必须是一个有效的IPv6地址。',
    'json' => ':attribute 必须是一个有效的JSON字符串。',
    'lt' => [
        'array' => ':attribute 必须包含少于 :value 项。',
        'file' => ':attribute 必须小于 :value KB。',
        'numeric' => ':attribute 必须小于 :value。',
        'string' => ':attribute 必须包含少于 :value 个字符。',
    ],
    'lte' => [
        'array' => ':attribute 必须包含不超过 :value 项。',
        'file' => ':attribute 必须小于或等于 :value KB。',
        'numeric' => ':attribute 必须小于或等于 :value。',
        'string' => ':attribute 必须包含不超过 :value 个字符。',
    ],
    'mac_address' => ':attribute 必须是一个有效的MAC地址。',
    'max' => [
        'array' => ':attribute 必须不超过 :max 项。',
        'file' => ':attribute 必须不大于 :max KB。',
        'numeric' => ':attribute 必须不大于 :max。',
        'string' => ':attribute 必须不大于 :max 个字符。',
    ],
    'mimes' => ':attribute 必须是类型为 :values 的文件。',
    'mimetypes' => ':attribute 必须是类型为 :values 的文件。',
    'min' => [
        'array' => ':attribute 必须至少包含 :min 项。',
        'file' => ':attribute 必须至少为 :min KB。',
        'numeric' => ':attribute 必须至少为 :min。',
        'string' => ':attribute 必须至少为 :min 个字符。',
    ],
    'multiple_of' => ':attribute 必须是 :value 的倍数。',
    'not_in' => '选择的 :attribute 无效。',
    'not_regex' => ':attribute 格式无效。',
    'numeric' => ':attribute 必须是一个数字。',
    'password' => [
        'letters' => ':attribute 必须包含至少一个字母。',
        'mixed' => ':attribute 必须包含至少一个大写字母和一个小写字母。',
        'numbers' => ':attribute 必须包含至少一个数字。',
        'symbols' => ':attribute 必须包含至少一个符号。',
        'uncompromised' => '给定的 :attribute 在数据泄露中出现过。请选择一个不同的 :attribute。',
    ],
    'present' => ':attribute 字段必须存在。',
    'prohibited' => ':attribute 字段是禁止的。',
    'prohibited_if' => '当 :other 为 :value 时，:attribute 字段是禁止的。',
    'prohibited_unless' => '除非 :other 在 :values 中，否则 :attribute 字段是禁止的。',
    'prohibits' => ':attribute 字段禁止存在 :other。',
    'regex' => ':attribute 格式无效。',
    'required' => ':attribute 字段是必填的。',
    'required_array_keys' => ':attribute 字段必须包含以下项目：:values。',
    'required_if' => '当 :other 为 :value 时，:attribute 字段是必填的。',
    'required_unless' => '除非 :other 在 :values 中，否则 :attribute 字段是必填的。',
    'required_with' => '当 :values 存在时，:attribute 字段是必填的。',
    'required_with_all' => '当 :values 都存在时，:attribute 字段是必填的。',
    'required_without' => '当 :values 不存在时，:attribute 字段是必填的。',
    'required_without_all' => '当 :values 都不存在时，:attribute 字段是必填的。',
    'same' => ':attribute 和 :other 必须匹配。',
    'size' => [
        'array' => ':attribute 必须包含 :size 项。',
        'file' => ':attribute 必须为 :size KB。',
        'numeric' => ':attribute 必须为 :size。',
        'string' => ':attribute 必须为 :size 个字符。',
    ],
    'starts_with' => ':attribute 必须以以下之一开头：:values。',
    'string' => ':attribute 必须是一个字符串。',
    'timezone' => ':attribute 必须是一个有效的时区。',
    'unique' => ':attribute 已被占用。',
    'uploaded' => ':attribute 上传失败。',
    'url' => ':attribute 必须是一个有效的URL。',
    'uuid' => ':attribute 必须是一个有效的UUID。',
    'custom' => [
        'attribute-name' => [
            'rule-name' => '自定义消息',
        ],
    ],

    'attributes' => [],

    'publish_date_required' => '发布日期为必填项',
    'price_numeric_value' => '价格应为数字值',
    'quantity_integer_value' => '数量应为整数值',
    'order_has_Expired' => '订单已过期',
    'expired' => '已过期',
    'eid_required' => 'EID字段为必填项。',
    'eid_string' => 'EID必须是字符串。',
    'otp_required' => 'OTP字段为必填项。',
    'amt_required' => '金额字段为必填项',
    'amt_numeric' => '金额必须为数字',
    'payment_date_required' => '付款日期为必填项。',
    'payment_method_required' => '付款方式为必填项。',
    'total_amount_required' => '总金额为必填项。',
    'total_amount_numeric' => '总金额必须为数字值。',
    'invoice_link_required' => '请将金额关联到至少一张发票。',

    /*
    Request file custom validation messages
    */

    //Common
    'settings_form' => [
        'company' => [
            'required' => '公司字段为必填项。',
        ],
        'website' => [
            'url' => '网站必须是有效的URL地址。',
        ],
        'phone' => [
            'regex' => '电话号码格式无效。',
        ],
        'address' => [
            'required' => '地址字段为必填项。',
            'max' => '地址不能超过300个字符。',
        ],
        'logo' => [
            'mimes' => 'Logo必须是PNG格式文件。',
        ],
        'driver' => [
            'required' => '驱动字段为必填项。',
        ],
        'port' => [
            'integer' => '端口必须是整数。',
        ],
        'email' => [
            'required' => '邮箱字段为必填项。',
            'email' => '邮箱地址必须有效。',
        ],
        'password' => [
            'required' => '密码字段为必填项。',
        ],
        'error_email' => [
            'email' => '错误邮箱必须是有效的邮箱地址。',
        ],
    ],

    'settings_forms' => [
        'company' => [
            'required' => '公司名称为必填项。',
            'max' => '公司名称不能超过50个字符。',
        ],
        'company_email' => [
            'required' => '公司邮箱为必填项。',
            'email' => '公司邮箱必须是有效的邮箱地址。',
        ],
        'title' => [
            'max' => '标题不能超过50个字符。',
        ],
        'website' => [
            'required' => '网站URL为必填项。',
            'url' => '网站必须是有效的URL地址。',
            'regex' => '网站格式无效。',
        ],
        'phone' => [
            'required' => '电话号码为必填项。',
        ],
        'address' => [
            'required' => '地址为必填项。',
        ],
        'state' => [
            'required' => '省/州为必填项。',
        ],
        'country' => [
            'required' => '国家为必填项。',
        ],
        'gstin' => [
            'max' => 'GSTIN不能超过15个字符。',
        ],
        'default_currency' => [
            'required' => '默认货币为必填项。',
        ],
        'admin_logo' => [
            'mimes' => '管理员Logo文件类型必须为：jpeg、png、jpg。',
            'max' => '管理员Logo不能超过2MB。',
        ],
        'fav_icon' => [
            'mimes' => '网站图标文件类型必须为：jpeg、png、jpg。',
            'max' => '网站图标不能超过2MB。',
        ],
        'logo' => [
            'mimes' => 'Logo文件类型必须为：jpeg、png、jpg。',
            'max' => 'Logo不能超过2MB。',
        ],
    ],

    'social_media_form' => [
        'name' => [
            'required' => '名称字段为必填项。',
            'unique' => '该名称已存在。',
            'max' => '名称不能超过50个字符。',
        ],
        'link' => [
            'required' => '链接字段为必填项。',
            'url' => '链接必须是有效的URL地址。',
            'regex' => '链接格式无效。',
        ],
    ],

    //Email
    'custom' => [
        'password' => [
            'required_if' => '所选邮件驱动器需要密码字段。',
        ],
        'port' => [
            'required_if' => 'SMTP需要端口字段。',
        ],
        'encryption' => [
            'required_if' => 'SMTP需要加密字段。',
        ],
        'host' => [
            'required_if' => 'SMTP需要主机字段。',
        ],
        'secret' => [
            'required_if' => '所选邮件驱动器需要密钥字段。',
        ],
        'domain' => [
            'required_if' => 'Mailgun需要域字段。',
        ],
        'key' => [
            'required_if' => 'SES需要密钥字段。',
        ],
        'region' => [
            'required_if' => 'SES需要区域字段。',
        ],
        'email' => [
            'required_if' => '所选邮件驱动器需要邮箱字段。',
            'required' => '邮箱字段为必填项。',
            'email' => '请输入有效的邮箱地址。',
            'not_matching' => '邮箱域名必须与当前网站域名匹配。',
        ],
        'driver' => [
            'required' => '驱动字段为必填项。',
        ],
    ],
    'customer_form' => [
        'first_name' => [
            'required' => '名字字段为必填项。',
        ],
        'last_name' => [
            'required' => '姓氏字段为必填项。',
        ],
        'company' => [
            'required' => '公司字段为必填项。',
        ],
        'mobile' => [
            'regex' => '手机号码格式无效。',
        ],
        'address' => [
            'required' => '地址字段为必填项。',
        ],
        'zip' => [
            'required' => '邮政编码字段为必填项。',
            'min' => '邮政编码至少为5位数字。',
            'numeric' => '邮政编码必须是数字。',
        ],
        'email' => [
            'required' => '邮箱字段为必填项。',
            'email' => '邮箱必须是有效的邮箱地址。',
            'unique' => '该邮箱已被使用。',
        ],
    ],

    'contact_request' => [
        'conName' => '姓名字段为必填项。',
        'email' => '邮箱字段为必填项。',
        'conmessage' => '消息字段为必填项。',
        'Mobile' => '手机号字段为必填项。',
        'country_code' => '手机号字段为必填项。',
        'demoname' => '姓名字段为必填项。',
        'demomessage' => '消息字段为必填项。',
        'demoemail' => '邮箱字段为必填项。',
        'congg-recaptcha-response-1.required' => '机器人验证失败。请重试。',
        'demo-recaptcha-response-1.required' => '机器人验证失败。请重试。',
    ],

    'frontend_pages' => [
        'name' => [
            'required' => '名称字段为必填项。',
            'unique' => '该名称已存在。',
            'max' => '名称不能超过20个字符。',
            'regex' => '名称只能包含字母和空格。',
        ],
        'publish' => [
            'required' => '发布字段为必填项。',
        ],
        'slug' => [
            'required' => '短链接字段为必填项。',
        ],
        'url' => [
            'required' => 'URL字段为必填项。',
            'url' => 'URL必须是有效链接。',
            'regex' => 'URL格式无效。',
        ],
        'content' => [
            'required' => '内容字段为必填项。',
        ],
        'created_at' => [
            'required' => '创建时间字段为必填项。',
        ],
    ],

    //Order form
    'order_form' => [
        'client' => [
            'required' => '客户字段为必填项。',
        ],
        'payment_method' => [
            'required' => '付款方式字段为必填项。',
        ],
        'promotion_code' => [
            'required' => '促销码字段为必填项。',
        ],
        'order_status' => [
            'required' => '订单状态字段为必填项。',
        ],
        'product' => [
            'required' => '产品字段为必填项。',
        ],
        'subscription' => [
            'required' => '订阅字段为必填项。',
        ],
        'price_override' => [
            'numeric' => '价格覆盖必须为数字。',
        ],
        'qty' => [
            'integer' => '数量必须是整数。',
        ],
    ],

    //Payment form
    'coupon_form' => [
        'code' => [
            'required' => '优惠码字段为必填项。',
            'string' => '优惠码必须是字符串。',
            'max' => '优惠码不能超过255个字符。',
        ],
        'type' => [
            'required' => '类型字段为必填项。',
            'in' => '类型无效。允许的值为：percentage，other_type。',
        ],
        'applied' => [
            'required' => '应用产品字段为必填项。',
            'date' => '应用产品字段必须是有效的日期。',
        ],
        'uses' => [
            'required' => '使用次数字段为必填项。',
            'numeric' => '使用次数字段必须是数字。',
            'min' => '使用次数字段不能少于:min。',
        ],
        'start' => [
            'required' => '开始日期字段为必填项。',
            'date' => '开始日期字段必须是有效日期。',
        ],
        'expiry' => [
            'required' => '过期日期字段为必填项。',
            'date' => '过期日期字段必须是有效日期。',
            'after' => '过期日期必须晚于开始日期。',
        ],
        'value' => [
            'required' => '折扣值字段为必填项。',
            'numeric' => '折扣值字段必须是数字。',
            'between' => '如果类型为百分比，折扣值字段必须在:min和:max之间。',
        ],
    ],

    'tax_form' => [
        'name' => [
            'required' => '名称字段为必填项。',
        ],
        'rate' => [
            'required' => '税率字段为必填项。',
            'numeric' => '税率必须为数字。',
        ],
        'level' => [
            'required' => '级别字段为必填项。',
            'integer' => '级别必须是整数。',
        ],
        'country' => [
            'required' => '国家字段为必填项。',
            // 'exists' => '所选国家无效。',
        ],
        'state' => [
            'required' => '州字段为必填项。',
            // 'exists' => '所选州无效。',
        ],
    ],
    //Product
    'subscription_form' => [
        'name' => [
            'required' => '名称字段为必填项。',
        ],
        'subscription' => [
            'required' => '订阅字段为必填项。',
        ],
        'regular_price' => [
            'required' => '原价字段为必填项。',
            'numeric' => '原价必须是数字。',
        ],
        'selling_price' => [
            'required' => '销售价字段为必填项。',
            'numeric' => '销售价必须是数字。',
        ],
        'products' => [
            'required' => '产品字段为必填项。',
        ],
    ],

    'bundle' => [
        'name' => [
            'required' => '名称字段为必填项。',
        ],
        'items' => [
            'required' => '每个项目都是必填项。',
        ],
    ],

    'group' => [
        'name' => [
            'required' => '名称为必填项',
        ],
        'features' => [
            'name' => [
                'required' => '所有功能字段为必填项',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => '价格为必填项',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => '数值为必填项',
            ],
        ],
        'type' => [
            'required_with' => '类型为必填项',
        ],
        'title' => [
            'required_with' => '标题为必填项',
        ],
    ],

    'product' => [
        'name' => [
            'required' => '名称字段为必填项。',
        ],
        'type' => [
            'required' => '类型字段为必填项。',
        ],
        'group' => [
            'required' => '分组字段为必填项。',
        ],
        'subscription' => [
            'required' => '订阅字段为必填项。',
        ],
        'currency' => [
            'required' => '货币字段为必填项。',
        ],
        // 'price' => [
        //     'required' => '价格字段为必填项。',
        // ],
        'file' => [
            'required_without_all' => '如果未提供github_owner或github_repository，则文件字段为必填项。',
            'mimes' => '文件必须为ZIP格式。',
        ],
        'image' => [
            'required_without_all' => '如果未提供github_owner或github_repository，则图片字段为必填项。',
            'mimes' => '图片必须为PNG格式。',
        ],
        'github_owner' => [
            'required_without_all' => '如果未提供文件或图片，则GitHub拥有者字段为必填项。',
        ],
        'github_repository' => [
            'required_without_all' => '如果未提供文件或图片，则GitHub仓库字段为必填项。',
            'required_if' => '当类型为2时，GitHub仓库字段为必填项。',
        ],
    ],

    //User
    'users' => [
        'first_name' => [
            'required' => '名字字段为必填项。',
        ],
        'last_name' => [
            'required' => '姓氏字段为必填项。',
        ],
        'company' => [
            'required' => '公司字段为必填项。',
        ],
        'email' => [
            'required' => '邮箱字段为必填项。',
            'email' => '邮箱必须是有效的邮箱地址。',
            'unique' => '该邮箱已被使用。',
        ],
        'address' => [
            'required' => '地址字段为必填项。',
        ],
        'mobile' => [
            'required' => '手机字段为必填项。',
        ],
        'country' => [
            'required' => '国家字段为必填项。',
            'exists' => '所选国家无效。',
        ],
        'state' => [
            'required_if' => '当国家为印度时，州字段为必填项。',
        ],
        'timezone_id' => [
            'required' => '时区字段为必填项。',
        ],
        'user_name' => [
            'required' => '用户名字段为必填项。',
            'unique' => '用户名已被使用。',
        ],
        'zip' => [
            'regex' => '当国家为印度时，州字段为必填项。',
        ],
    ],

    'profile_form' => [
        'first_name' => [
            'required' => '名字为必填项。',
            'min' => '名字至少为:min个字符。',
            'max' => '名字不能超过:max个字符。',
        ],
        'last_name' => [
            'required' => '姓氏为必填项。',
            'max' => '姓氏不能超过:max个字符。',
        ],
        'company' => [
            'required' => '公司名称为必填项。',
            'max' => '公司名称不能超过:max个字符。',
        ],
        'email' => [
            'required' => '邮箱为必填项。',
            'email' => '请输入有效的邮箱地址。',
            'unique' => '该邮箱已被使用，请选择其他邮箱。',
        ],
        'mobile' => [
            'required' => '手机号为必填项。',
            'regex' => '请输入有效的手机号。',
            'min' => '手机号至少为:min个字符。',
            'max' => '手机号不能超过:max个字符。',
        ],
        'address' => [
            'required' => '地址为必填项。',
        ],
    ],
    'user_name' => [
        'required' => '用户名是必填项。',
        'unique' => '该用户名已被使用。',
    ],
    'timezone_id' => [
        'required' => '时区是必填项。',
    ],
    'country' => [
        'required' => '国家是必填项。',
        'exists' => '所选国家无效。',
    ],
    'state' => [
        'required_if' => '当国家为印度时，州字段是必填项。',
    ],
    'old_password' => [
        'required' => '旧密码是必填项。',
        'min' => '旧密码至少需要 :min 个字符。',
    ],
    'new_password' => [
        'required' => '新密码是必填项。',
        'different' => '新密码必须与旧密码不同。',
    ],
    'confirm_password' => [
        'required' => '确认密码是必填项。',
        'same' => '确认密码必须与新密码一致。',
    ],
    'terms' => [
        'required' => '您必须接受条款。',
    ],
    'password' => [
        'required' => '密码是必填项。',
    ],
    'password_confirmation' => [
        'required' => '密码确认是必填项。',
        'same' => '两次输入的密码不一致。',
    ],
    'mobile_code' => [
        'required' => '请输入国家区号（手机）。',
    ],

    //Invoice form
    'invoice' => [
        'user' => [
            'required' => '客户字段是必填项。',
        ],
        'date' => [
            'required' => '日期字段是必填项。',
            'date' => '日期格式无效。',
        ],
        'domain' => [
            'regex' => '域名格式无效。',
        ],
        'plan' => [
            'required_if' => '订阅字段是必填项。',
        ],
        'price' => [
            'required' => '价格字段是必填项。',
        ],
        'product' => [
            'required' => '产品字段是必填项。',
        ],
    ],

    //LocalizedLicense form
    'domain_form' => [
        'domain' => [
            'required' => '域名字段是必填项。',
            'url' => '域名必须是有效的 URL。',
        ],
    ],

    //Product Renewal form
    'product_renewal' => [
        'domain' => [
            'required' => '域名字段是必填项。',
            'no_http' => '域名不能包含 "http" 或 "https"。',
        ],
    ],

    //Language form
    'language' => [
        'required' => '语言字段是必填项。',
        'invalid' => '所选语言无效。',
    ],

    //UpdateStoragePathRequest form
    'storage_path' => [
        'disk' => [
            'required' => '存储磁盘字段是必填项。',
            'string' => '磁盘必须是字符串。',
        ],
        'path' => [
            'string' => '路径必须是字符串。',
            'nullable' => '路径字段是可选的。',
        ],
    ],

    //ValidateSecretRequest form
    'validate_secret' => [
        'totp' => [
            'required' => '请输入验证码。',
            'digits' => '请输入有效的6位验证码。',
        ],
    ],

    //VerifyOtp form
    'verify_email' => [
        'required' => '电子邮件字段是必填项。',
        'email' => '电子邮件必须是有效的电子邮件地址。',
        'verify_email' => '电子邮件验证失败。',
    ],

    'verify_country_code' => [
        'required' => '国家代码是必填项。',
        'numeric' => '国家代码必须是有效的数字。',
        'verify_country_code' => '国家代码验证失败。',
    ],
    'verify_number' => [
        'required' => '号码是必填项。',
        'numeric' => '号码必须是有效的数字。',
        'verify_number' => '号码验证失败。',
    ],

    'password_otp' => [
        'required' => '密码字段是必填项。',
        'password' => '密码不正确。',
        'invalid' => '密码无效。',
    ],

    //AuthController file
    'auth_controller' => [
        'name_required' => '姓名是必填项。',
        'name_max' => '姓名不能超过 255 个字符。',

        'email_required' => '电子邮件是必填项。',
        'email_email' => '请输入有效的电子邮件地址。',
        'email_max' => '电子邮件不能超过 255 个字符。',
        'email_unique' => '该电子邮件已被注册。',

        'password_required' => '密码是必填项。',
        'password_confirmed' => '密码确认不匹配。',
        'password_min' => '密码必须至少包含 6 个字符。',
    ],

    'resend_otp' => [
        'eid_required' => 'EID 字段是必填项。',
        'eid_string' => 'EID 必须是一个字符串。',
        'type_required' => '类型字段是必填项。',
        'type_string' => '类型必须是一个字符串。',
        'type_in' => '所选类型无效。',
    ],

    'verify_otp' => [
        'eid_required' => '员工ID是必填项。',
        'eid_string' => '员工ID必须是字符串。',
        'otp_required' => 'OTP 是必填项。',
        'otp_size' => 'OTP 必须恰好是 6 个字符。',
        'recaptcha_required' => '请完成验证码。',
        'recaptcha_size' => '验证码响应无效。',
    ],

    'company_validation' => [
        'company_required' => '公司名称是必填项。',
        'company_string' => '公司名称必须是文本。',
        'address_required' => '地址是必填项。',
        'address_string' => '地址必须是文本。',
    ],

    'token_validation' => [
        'token_required' => '令牌是必填项。',
        'password_required' => '密码字段是必填项。',
        'password_confirmed' => '密码确认不匹配。',
    ],

    'custom_email' => [
        'required' => '电子邮件字段是必填项。',
        'email' => '请输入有效的电子邮件地址。',
        'exists' => '该电子邮件未在我们的系统中注册。',
    ],

    'newsletterEmail' => [
        'required' => '新闻通讯电子邮件是必填项。',
        'email' => '请输入有效的电子邮件地址用于新闻通讯。',
    ],

    'widget' => [
        'name_required' => '名称是必填项。',
        'name_max' => '名称不能超过 50 个字符。',
        'publish_required' => '发布状态是必填项。',
        'type_required' => '类型是必填项。',
        'type_unique' => '该类型已存在。',
    ],

    'payment' => [
        'payment_date_required' => '支付日期是必填项。',
        'payment_method_required' => '支付方式是必填项。',
        'amount_required' => '金额是必填项。',
    ],

    'custom_date' => [
        'date_required' => '日期字段是必填项。',
        'total_required' => '总额字段是必填项。',
        'status_required' => '状态字段是必填项。',
    ],

    'plan_renewal' => [
        'plan_required' => '计划字段是必填项。',
        'payment_method_required' => '支付方式字段是必填项。',
        'cost_required' => '费用字段是必填项。',
        'code_not_valid' => '优惠码无效。',
    ],

    'rate' => [
        'required' => '费率是必填项。',
        'numeric' => '费率必须是数字。',
    ],

    'product_validate' => [
        'producttitle_required' => '产品标题是必填的。',
        'version_required' => '版本是必填的。',
        'filename_required' => '请上传一个文件。',
        'dependencies_required' => '依赖字段是必填的。',
    ],
    'product_sku_unique' => '产品SKU必须是唯一的。',
    'product_name_unique' => '名称必须是唯一的。',
    'product_show_agent_required' => '请选择您的购物车页面偏好。',
    'product_controller' => [
        'name_required' => '产品名称是必填的。',
        'name_unique' => '名称必须是唯一的。',
        'type_required' => '产品类型是必填的。',
        'description_required' => '产品描述是必填的。',
        'product_description_required' => '详细的产品描述是必填的。',
        'image_mimes' => '图片必须是jpeg, png, jpg类型的文件。',
        'image_max' => '图片大小不得超过2048KB。',
        'product_sku_required' => '产品SKU是必填的。',
        'group_required' => '产品组是必填的。',
        'show_agent_required' => '请选择您的购物车页面偏好。',
    ],
    'current_domain_required' => '当前域名是必填的。',
    'new_domain_required' => '新域名是必填的。',
    'special_characters_not_allowed' => '域名中不允许使用特殊字符。',
    'orderno_required' => '订单号是必填的。',
    'cloud_central_domain_required' => '云中心域名是必填的。',
    'cloud_cname_required' => '云CNAME是必填的。',
    'cloud_tenant' => [
        'cloud_top_message_required' => '云顶部消息是必填的。',
        'cloud_label_field_required' => '云标签字段是必填的。',
        'cloud_label_radio_required' => '云标签单选框是必填的。',
        'cloud_product_required' => '云产品是必填的。',
        'cloud_free_plan_required' => '云免费计划是必填的。',
        'cloud_product_key_required' => '云产品密钥是必填的。',
    ],
    'reg_till_after' => '注册截止日期必须晚于注册起始日期。',
    'extend_product' => [
        'title_required' => '标题字段是必填的。',
        'version_required' => '版本字段是必填的。',
        'dependencies_required' => '依赖字段是必填的。',
    ],
    'please_enter_recovery_code' => '请输入恢复码。',
    'social_login' => [
        'client_id_required' => 'Google、Github或Linkedin需要提供客户端ID。',
        'client_secret_required' => 'Google、Github或Linkedin需要提供客户端密钥。',
        'api_key_required' => 'Twitter需要提供API密钥。',
        'api_secret_required' => 'Twitter需要提供API密钥。',
        'redirect_url_required' => '需要提供重定向URL。',
    ],
    'thirdparty_api' => [
        'app_name_required' => '应用名称是必填的。',
        'app_key_required' => '应用密钥是必填的。',
        'app_key_size' => '应用密钥必须正好为32个字符。',
        'app_secret_required' => '应用密钥是必填的。',
    ],
    'plan_request' => [
        'name_required' => '名称字段是必填项',
        'product_quant_req' => '当未填写代理人数时，产品数量字段是必填项。',
        'no_agent_req' => '当未填写产品数量时，代理人数字段是必填项。',
        'pro_req' => '产品字段是必填项',
        'offer_price' => '优惠价格不能大于 100',
    ],
    'razorpay_val' => [
        'business_required' => '业务字段是必填项。',
        'cmd_required' => '命令字段是必填项。',
        'paypal_url_required' => 'PayPal URL 是必填项。',
        'paypal_url_invalid' => 'PayPal URL 必须是有效的 URL。',
        'success_url_invalid' => '成功 URL 必须是有效的 URL。',
        'cancel_url_invalid' => '取消 URL 必须是有效的 URL。',
        'notify_url_invalid' => '通知 URL 必须是有效的 URL。',
        'currencies_required' => '货币字段是必填项。',
    ],
    'login_failed' => '登录失败，请检查您输入的电子邮件/用户名和密码是否正确。',
    'forgot_email_validation' => '如果您提供的电子邮件已注册，您将很快收到一封包含重置密码说明的电子邮件。',
    'too_many_login_attempts' => '由于多次登录失败，您已被锁定，请在 :time 后重试。',

];
