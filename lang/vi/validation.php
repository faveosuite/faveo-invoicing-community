<?php

return [

    'accepted' => ':attribute phải được chấp nhận.',
    'accepted_if' => ':attribute phải được chấp nhận khi :other là :value.',
    'active_url' => ':attribute không phải là một URL hợp lệ.',
    'after' => ':attribute phải là một ngày sau :date.',
    'after_or_equal' => ':attribute phải là một ngày sau hoặc bằng :date.',
    'alpha' => ':attribute chỉ được chứa các chữ cái.',
    'alpha_dash' => ':attribute chỉ được chứa chữ cái, số, dấu gạch nối và dấu gạch dưới.',
    'alpha_num' => ':attribute chỉ được chứa chữ cái và số.',
    'array' => ':attribute phải là một mảng.',
    'before' => ':attribute phải là một ngày trước :date.',
    'before_or_equal' => ':attribute phải là một ngày trước hoặc bằng :date.',
    'between' => [
        'array' => ':attribute phải có từ :min đến :max mục.',
        'file' => ':attribute phải có kích thước từ :min đến :max kilobytes.',
        'numeric' => ':attribute phải nằm trong khoảng từ :min đến :max.',
        'string' => ':attribute phải có từ :min đến :max ký tự.',
    ],
    'boolean' => 'Trường :attribute phải là đúng hoặc sai.',
    'confirmed' => 'Xác nhận :attribute không khớp.',
    'current_password' => 'Mật khẩu không chính xác.',
    'date' => ':attribute không phải là một ngày hợp lệ.',
    'date_equals' => ':attribute phải là một ngày bằng với :date.',
    'date_format' => ':attribute không khớp với định dạng :format.',
    'declined' => ':attribute phải bị từ chối.',
    'declined_if' => ':attribute phải bị từ chối khi :other là :value.',
    'different' => ':attribute và :other phải khác nhau.',
    'digits' => ':attribute phải có :digits chữ số.',
    'digits_between' => ':attribute phải có từ :min đến :max chữ số.',
    'dimensions' => ':attribute có kích thước ảnh không hợp lệ.',
    'distinct' => 'Trường :attribute có giá trị trùng lặp.',
    'doesnt_start_with' => ':attribute không được bắt đầu với một trong các giá trị sau: :values.',
    'email' => ':attribute phải là một địa chỉ email hợp lệ.',
    'ends_with' => ':attribute phải kết thúc với một trong các giá trị sau: :values.',
    'enum' => 'Giá trị đã chọn :attribute là không hợp lệ.',
    'exists' => 'Giá trị đã chọn :attribute không hợp lệ.',
    'file' => ':attribute phải là một tệp tin.',
    'filled' => 'Trường :attribute phải có giá trị.',
    'gt' => [
        'array' => ':attribute phải có nhiều hơn :value mục.',
        'file' => ':attribute phải lớn hơn :value kilobytes.',
        'numeric' => ':attribute phải lớn hơn :value.',
        'string' => ':attribute phải có nhiều hơn :value ký tự.',
    ],
    'gte' => [
        'array' => ':attribute phải có từ :value mục trở lên.',
        'file' => ':attribute phải lớn hơn hoặc bằng :value kilobytes.',
        'numeric' => ':attribute phải lớn hơn hoặc bằng :value.',
        'string' => ':attribute phải có từ :value ký tự trở lên.',
    ],
    'image' => ':attribute phải là một hình ảnh.',
    'in' => 'Giá trị đã chọn :attribute không hợp lệ.',
    'in_array' => 'Trường :attribute không tồn tại trong :other.',
    'integer' => ':attribute phải là một số nguyên.',
    'ip' => ':attribute phải là một địa chỉ IP hợp lệ.',
    'ipv4' => ':attribute phải là một địa chỉ IPv4 hợp lệ.',
    'ipv6' => ':attribute phải là một địa chỉ IPv6 hợp lệ.',
    'json' => ':attribute phải là một chuỗi JSON hợp lệ.',
    'lt' => [
        'array' => ':attribute phải có ít hơn :value mục.',
        'file' => ':attribute phải nhỏ hơn :value kilobytes.',
        'numeric' => ':attribute phải nhỏ hơn :value.',
        'string' => ':attribute phải có ít hơn :value ký tự.',
    ],
    'lte' => [
        'array' => ':attribute không được có nhiều hơn :value mục.',
        'file' => ':attribute phải nhỏ hơn hoặc bằng :value kilobytes.',
        'numeric' => ':attribute phải nhỏ hơn hoặc bằng :value.',
        'string' => ':attribute phải có ít hơn hoặc bằng :value ký tự.',
    ],
    'mac_address' => ':attribute phải là một địa chỉ MAC hợp lệ.',
    'max' => [
        'array' => ':attribute không được có nhiều hơn :max mục.',
        'file' => ':attribute không được lớn hơn :max kilobytes.',
        'numeric' => ':attribute không được lớn hơn :max.',
        'string' => ':attribute không được lớn hơn :max ký tự.',
    ],
    'mimes' => ':attribute phải là một tệp thuộc các loại: :values.',
    'mimetypes' => ':attribute phải là một tệp thuộc các loại: :values.',
    'min' => [
        'array' => ':attribute phải có ít nhất :min mục.',
        'file' => ':attribute phải có ít nhất :min kilobytes.',
        'numeric' => ':attribute phải ít nhất :min.',
        'string' => ':attribute phải có ít nhất :min ký tự.',
    ],
    'multiple_of' => ':attribute phải là bội số của :value.',
    'not_in' => 'Giá trị đã chọn :attribute không hợp lệ.',
    'not_regex' => 'Định dạng của :attribute không hợp lệ.',
    'numeric' => ':attribute phải là một số.',
    'password' => [
        'letters' => ':attribute phải chứa ít nhất một chữ cái.',
        'mixed' => ':attribute phải chứa ít nhất một chữ cái viết hoa và một chữ cái viết thường.',
        'numbers' => ':attribute phải chứa ít nhất một số.',
        'symbols' => ':attribute phải chứa ít nhất một ký tự đặc biệt.',
        'uncompromised' => ':attribute đã xuất hiện trong một vụ rò rỉ dữ liệu. Vui lòng chọn :attribute khác.',
    ],
    'present' => 'Trường :attribute phải có mặt.',
    'prohibited' => 'Trường :attribute bị cấm.',
    'prohibited_if' => 'Trường :attribute bị cấm khi :other là :value.',
    'prohibited_unless' => 'Trường :attribute bị cấm trừ khi :other nằm trong :values.',
    'prohibits' => 'Trường :attribute cấm sự có mặt của :other.',
    'regex' => 'Định dạng của :attribute không hợp lệ.',
    'required' => 'Trường :attribute là bắt buộc.',
    'required_array_keys' => 'Trường :attribute phải chứa các mục cho: :values.',
    'required_if' => 'Trường :attribute là bắt buộc khi :other là :value.',
    'required_unless' => 'Trường :attribute là bắt buộc trừ khi :other nằm trong :values.',
    'required_with' => 'Trường :attribute là bắt buộc khi :values có mặt.',
    'required_with_all' => 'Trường :attribute là bắt buộc khi tất cả :values có mặt.',
    'required_without' => 'Trường :attribute là bắt buộc khi :values không có mặt.',
    'required_without_all' => 'Trường :attribute là bắt buộc khi không có mục nào trong :values có mặt.',
    'same' => ':attribute và :other phải khớp.',
    'size' => [
        'array' => ':attribute phải chứa :size mục.',
        'file' => ':attribute phải có kích thước :size kilobytes.',
        'numeric' => ':attribute phải là :size.',
        'string' => ':attribute phải có :size ký tự.',
    ],
    'starts_with' => ':attribute phải bắt đầu với một trong các giá trị sau: :values.',
    'string' => ':attribute phải là một chuỗi.',
    'timezone' => ':attribute phải là một múi giờ hợp lệ.',
    'unique' => ':attribute đã được sử dụng.',
    'uploaded' => ':attribute đã tải lên không thành công.',
    'url' => ':attribute phải là một URL hợp lệ.',
    'uuid' => ':attribute phải là một UUID hợp lệ.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Bạn có thể chỉ định các thông báo xác thực tùy chỉnh cho các thuộc tính sử dụng
    | quy tắc "attribute.rule" để đặt tên các dòng. Điều này giúp bạn nhanh chóng
    | chỉ định một dòng ngôn ngữ tùy chỉnh cho một quy tắc thuộc tính nhất định.
    |
    */

    'custom_dup' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | Các dòng ngôn ngữ sau được sử dụng để thay thế các placeholder thuộc tính
    | bằng một cái tên dễ đọc hơn như "Địa chỉ email" thay vì "email".
    | Điều này giúp chúng ta làm cho thông báo dễ hiểu hơn.
    |
    */

    'attributes' => [],
    'publish_date_required' => 'Ngày xuất bản là bắt buộc',
    'price_numeric_value' => 'Giá phải là một giá trị số',
    'quantity_integer_value' => 'Số lượng phải là một số nguyên',
    'order_has_Expired' => 'Đơn hàng đã hết hạn',
    'expired' => 'Hết hạn',
    'eid_required' => 'Trường EID là bắt buộc.',
    'eid_string' => 'EID phải là một chuỗi.',
    'otp_required' => 'Trường OTP là bắt buộc.',
    'amt_required' => 'Trường số tiền là bắt buộc',
    'amt_numeric' => 'Số tiền phải là một con số',
    'payment_date_required' => 'Ngày thanh toán là bắt buộc.',
    'payment_method_required' => 'Phương thức thanh toán là bắt buộc.',
    'total_amount_required' => 'Tổng số tiền là bắt buộc.',
    'total_amount_numeric' => 'Tổng số tiền phải là một giá trị số.',
    'invoice_link_required' => 'Vui lòng liên kết số tiền với ít nhất một hóa đơn.',

    /*
    Request file custom validation messages
    */

    // Common
    'settings_form' => [
        'company' => [
            'required' => 'Trường công ty là bắt buộc.',
        ],
        'website' => [
            'url' => 'Website phải là một URL hợp lệ.',
        ],
        'phone' => [
            'regex' => 'Định dạng số điện thoại không hợp lệ.',
        ],
        'address' => [
            'required' => 'Trường địa chỉ là bắt buộc.',
            'max' => 'Địa chỉ không được vượt quá 300 ký tự.',
        ],
        'logo' => [
            'mimes' => 'Logo phải là tệp PNG.',
        ],
        'driver' => [
            'required' => 'Trường driver là bắt buộc.',
        ],
        'port' => [
            'integer' => 'Cổng phải là một số nguyên.',
        ],
        'email' => [
            'required' => 'Trường email là bắt buộc.',
            'email' => 'Email phải là một địa chỉ email hợp lệ.',
        ],
        'password' => [
            'required' => 'Trường mật khẩu là bắt buộc.',
        ],
        'error_email' => [
            'email' => 'Email lỗi phải là một địa chỉ email hợp lệ.',
        ],
    ],

    'settings_forms' => [
        'company' => [
            'required' => 'Tên công ty là bắt buộc.',
            'max' => 'Tên công ty không được vượt quá 50 ký tự.',
        ],
        'company_email' => [
            'required' => 'Email công ty là bắt buộc.',
            'email' => 'Email công ty phải là một địa chỉ email hợp lệ.',
        ],
        'title' => [
            'max' => 'Tiêu đề không được vượt quá 50 ký tự.',
        ],
        'website' => [
            'required' => 'URL website là bắt buộc.',
            'url' => 'Website phải là một URL hợp lệ.',
            'regex' => 'Định dạng website không hợp lệ.',
        ],
        'phone' => [
            'required' => 'Số điện thoại là bắt buộc.',
        ],
        'address' => [
            'required' => 'Địa chỉ là bắt buộc.',
        ],
        'state' => [
            'required' => 'Tỉnh/bang là bắt buộc.',
        ],
        'country' => [
            'required' => 'Quốc gia là bắt buộc.',
        ],
        'gstin' => [
            'max' => 'GSTIN không được vượt quá 15 ký tự.',
        ],
        'default_currency' => [
            'required' => 'Loại tiền tệ mặc định là bắt buộc.',
        ],
        'admin_logo' => [
            'mimes' => 'Logo quản trị phải là tệp loại: jpeg, png, jpg.',
            'max' => 'Logo quản trị không được vượt quá 2MB.',
        ],
        'fav_icon' => [
            'mimes' => 'Biểu tượng yêu thích phải là tệp loại: jpeg, png, jpg.',
            'max' => 'Biểu tượng yêu thích không được vượt quá 2MB.',
        ],
        'logo' => [
            'mimes' => 'Logo phải là tệp loại: jpeg, png, jpg.',
            'max' => 'Logo không được vượt quá 2MB.',
        ],
    ],

    'social_media_form' => [
        'name' => [
            'required' => 'Trường tên là bắt buộc.',
            'unique' => 'Tên này đã tồn tại.',
            'max' => 'Tên không được vượt quá 50 ký tự.',
        ],
        'link' => [
            'required' => 'Trường liên kết là bắt buộc.',
            'url' => 'Liên kết phải là một URL hợp lệ.',
            'regex' => 'Định dạng liên kết không hợp lệ.',
        ],
    ],

    // Email
    'custom' => [
        'password' => [
            'required_if' => 'Trường mật khẩu là bắt buộc đối với driver thư đã chọn.',
        ],
        'port' => [
            'required_if' => 'Trường cổng là bắt buộc đối với SMTP.',
        ],
        'encryption' => [
            'required_if' => 'Trường mã hóa là bắt buộc đối với SMTP.',
        ],
        'host' => [
            'required_if' => 'Trường máy chủ là bắt buộc đối với SMTP.',
        ],
        'secret' => [
            'required_if' => 'Trường bí mật là bắt buộc đối với driver thư đã chọn.',
        ],
        'domain' => [
            'required_if' => 'Trường tên miền là bắt buộc đối với Mailgun.',
        ],
        'key' => [
            'required_if' => 'Trường khóa là bắt buộc đối với SES.',
        ],
        'region' => [
            'required_if' => 'Trường khu vực là bắt buộc đối với SES.',
        ],
        'email' => [
            'required_if' => 'Trường email là bắt buộc đối với driver thư đã chọn.',
            'required' => 'Trường email là bắt buộc.',
            'email' => 'Vui lòng nhập một địa chỉ email hợp lệ.',
            'not_matching' => 'Tên miền email phải khớp với tên miền của trang web hiện tại.',
        ],
    ],
    'driver' => [
        'required' => 'Trường driver là bắt buộc.',
    ],

    'customer_form' => [
        'first_name' => [
            'required' => 'Trường tên là bắt buộc.',
        ],
        'last_name' => [
            'required' => 'Trường họ là bắt buộc.',
        ],
        'company' => [
            'required' => 'Trường công ty là bắt buộc.',
        ],
        'mobile' => [
            'regex' => 'Định dạng số điện thoại không hợp lệ.',
        ],
        'address' => [
            'required' => 'Trường địa chỉ là bắt buộc.',
        ],
        'zip' => [
            'required' => 'Trường mã bưu điện là bắt buộc.',
            'min' => 'Mã bưu điện phải có ít nhất 5 chữ số.',
            'numeric' => 'Mã bưu điện phải là số.',
        ],
        'email' => [
            'required' => 'Trường email là bắt buộc.',
            'email' => 'Email phải là địa chỉ hợp lệ.',
            'unique' => 'Email này đã được sử dụng.',
        ],
    ],

    'contact_request' => [
        'conName' => 'Trường tên là bắt buộc.',
        'email' => 'Trường email là bắt buộc.',
        'conmessage' => 'Trường tin nhắn là bắt buộc.',
        'Mobile' => 'Trường điện thoại là bắt buộc.',
        'country_code' => 'Trường điện thoại là bắt buộc.',
        'demoname' => 'Trường tên là bắt buộc.',
        'demomessage' => 'Trường tin nhắn là bắt buộc.',
        'demoemail' => 'Trường email là bắt buộc.',
        'congg-recaptcha-response-1.required' => 'Xác minh robot không thành công. Vui lòng thử lại.',
        'demo-recaptcha-response-1.required' => 'Xác minh robot không thành công. Vui lòng thử lại.',
    ],

    'frontend_pages' => [
        'name' => [
            'required' => 'Trường tên là bắt buộc.',
            'unique' => 'Tên này đã tồn tại.',
            'max' => 'Tên không được vượt quá 20 ký tự.',
            'regex' => 'Tên chỉ được chứa chữ cái và khoảng trắng.',
        ],
        'publish' => [
            'required' => 'Trường xuất bản là bắt buộc.',
        ],
        'slug' => [
            'required' => 'Trường đường dẫn là bắt buộc.',
        ],
        'url' => [
            'required' => 'Trường URL là bắt buộc.',
            'url' => 'URL phải là một liên kết hợp lệ.',
            'regex' => 'Định dạng URL không hợp lệ.',
        ],
        'content' => [
            'required' => 'Trường nội dung là bắt buộc.',
        ],
        'created_at' => [
            'required' => 'Trường ngày tạo là bắt buộc.',
        ],
    ],

    // Order form
    'order_form' => [
        'client' => [
            'required' => 'Trường khách hàng là bắt buộc.',
        ],
        'payment_method' => [
            'required' => 'Trường phương thức thanh toán là bắt buộc.',
        ],
        'promotion_code' => [
            'required' => 'Trường mã khuyến mãi là bắt buộc.',
        ],
        'order_status' => [
            'required' => 'Trường trạng thái đơn hàng là bắt buộc.',
        ],
        'product' => [
            'required' => 'Trường sản phẩm là bắt buộc.',
        ],
        'subscription' => [
            'required' => 'Trường đăng ký là bắt buộc.',
        ],
        'price_override' => [
            'numeric' => 'Giá thay thế phải là một số.',
        ],
        'qty' => [
            'integer' => 'Số lượng phải là một số nguyên.',
        ],
    ],

    // Payment form
    'coupon_form' => [
        'code' => [
            'required' => 'Trường mã giảm giá là bắt buộc.',
            'string' => 'Mã giảm giá phải là một chuỗi.',
            'max' => 'Mã giảm giá không được vượt quá 255 ký tự.',
        ],
        'type' => [
            'required' => 'Trường loại là bắt buộc.',
            'in' => 'Loại không hợp lệ. Giá trị cho phép là: percentage, other_type.',
        ],
        'applied' => [
            'required' => 'Trường áp dụng cho sản phẩm là bắt buộc.',
            'date' => 'Trường áp dụng cho sản phẩm phải là ngày hợp lệ.',
        ],
        'uses' => [
            'required' => 'Trường số lần sử dụng là bắt buộc.',
            'numeric' => 'Trường số lần sử dụng phải là một số.',
            'min' => 'Trường số lần sử dụng phải ít nhất là :min.',
        ],
        'start' => [
            'required' => 'Trường bắt đầu là bắt buộc.',
            'date' => 'Trường bắt đầu phải là ngày hợp lệ.',
        ],
        'expiry' => [
            'required' => 'Trường ngày hết hạn là bắt buộc.',
            'date' => 'Trường ngày hết hạn phải là ngày hợp lệ.',
            'after' => 'Ngày hết hạn phải sau ngày bắt đầu.',
        ],
        'value' => [
            'required' => 'Trường giá trị giảm giá là bắt buộc.',
            'numeric' => 'Giá trị giảm giá phải là một số.',
            'between' => 'Giá trị giảm giá phải nằm trong khoảng từ :min đến :max nếu loại là phần trăm.',
        ],
    ],

    'tax_form' => [
        'name' => [
            'required' => 'Trường tên là bắt buộc.',
        ],
        'rate' => [
            'required' => 'Trường thuế suất là bắt buộc.',
            'numeric' => 'Thuế suất phải là một số.',
        ],
        'level' => [
            'required' => 'Trường cấp độ là bắt buộc.',
            'integer' => 'Cấp độ phải là một số nguyên.',
        ],
    ],
    'country' => [
        'required' => 'Trường quốc gia là bắt buộc.',
        // 'exists' => 'Quốc gia đã chọn không hợp lệ.',
    ],
    'state' => [
        'required' => 'Trường tiểu bang là bắt buộc.',
        // 'exists' => 'Tiểu bang đã chọn không hợp lệ.',
    ],

    // Product
    'subscription_form' => [
        'name' => [
            'required' => 'Trường tên là bắt buộc.',
        ],
        'subscription' => [
            'required' => 'Trường đăng ký là bắt buộc.',
        ],
        'regular_price' => [
            'required' => 'Trường giá gốc là bắt buộc.',
            'numeric' => 'Giá gốc phải là một số.',
        ],
        'selling_price' => [
            'required' => 'Trường giá bán là bắt buộc.',
            'numeric' => 'Giá bán phải là một số.',
        ],
        'products' => [
            'required' => 'Trường sản phẩm là bắt buộc.',
        ],
    ],

    'bundle' => [
        'name' => [
            'required' => 'Trường tên là bắt buộc.',
        ],
        'items' => [
            'required' => 'Mỗi mục là bắt buộc.',
        ],
    ],

    'group' => [
        'name' => [
            'required' => 'Tên là bắt buộc',
        ],
        'features' => [
            'name' => [
                'required' => 'Tất cả các trường tính năng là bắt buộc',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => 'Giá là bắt buộc',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => 'Giá trị là bắt buộc',
            ],
        ],
        'type' => [
            'required_with' => 'Loại là bắt buộc',
        ],
        'title' => [
            'required_with' => 'Tiêu đề là bắt buộc',
        ],
    ],

    'product' => [
        'name' => [
            'required' => 'Trường tên là bắt buộc.',
        ],
        'type' => [
            'required' => 'Trường loại là bắt buộc.',
        ],
        'group' => [
            'required' => 'Trường nhóm là bắt buộc.',
        ],
        'subscription' => [
            'required' => 'Trường đăng ký là bắt buộc.',
        ],
        'currency' => [
            'required' => 'Trường tiền tệ là bắt buộc.',
        ],
        // 'price' => [
        //     'required' => 'Trường giá là bắt buộc.',
        // ],
        'file' => [
            'required_without_all' => 'Trường tệp là bắt buộc nếu không có github_owner hoặc github_repository.',
            'mimes' => 'Tệp phải là tệp zip.',
        ],
        'image' => [
            'required_without_all' => 'Trường hình ảnh là bắt buộc nếu không có github_owner hoặc github_repository.',
            'mimes' => 'Hình ảnh phải là tệp PNG.',
        ],
        'github_owner' => [
            'required_without_all' => 'Trường chủ sở hữu GitHub là bắt buộc nếu không có tệp hoặc hình ảnh.',
        ],
        'github_repository' => [
            'required_without_all' => 'Trường kho GitHub là bắt buộc nếu không có tệp hoặc hình ảnh.',
            'required_if' => 'Trường kho GitHub là bắt buộc nếu loại là 2.',
        ],
    ],

    // User
    'users' => [
        'first_name' => [
            'required' => 'Trường tên là bắt buộc.',
        ],
        'last_name' => [
            'required' => 'Trường họ là bắt buộc.',
        ],
        'company' => [
            'required' => 'Trường công ty là bắt buộc.',
        ],
        'email' => [
            'required' => 'Trường email là bắt buộc.',
            'email' => 'Email phải là một địa chỉ hợp lệ.',
            'unique' => 'Email này đã được sử dụng.',
        ],
        'address' => [
            'required' => 'Trường địa chỉ là bắt buộc.',
        ],
        'mobile' => [
            'required' => 'Trường điện thoại là bắt buộc.',
        ],
        'country' => [
            'required' => 'Trường quốc gia là bắt buộc.',
            'exists' => 'Quốc gia đã chọn không hợp lệ.',
        ],
        'state' => [
            'required_if' => 'Trường tiểu bang là bắt buộc khi quốc gia là Ấn Độ.',
        ],
        'timezone_id' => [
            'required' => 'Trường múi giờ là bắt buộc.',
        ],
        'user_name' => [
            'required' => 'Trường tên người dùng là bắt buộc.',
            'unique' => 'Tên người dùng này đã được sử dụng.',
        ],
        'zip' => [
            'regex' => 'Trường tiểu bang là bắt buộc khi quốc gia là Ấn Độ.',
        ],
    ],

    'profile_form' => [
        'first_name' => [
            'required' => 'Tên là bắt buộc.',
            'min' => 'Tên phải có ít nhất :min ký tự.',
            'max' => 'Tên không được vượt quá :max ký tự.',
        ],
        'last_name' => [
            'required' => 'Họ là bắt buộc.',
            'max' => 'Họ không được vượt quá :max ký tự.',
        ],
        'company' => [
            'required' => 'Tên công ty là bắt buộc.',
            'max' => 'Tên công ty không được vượt quá :max ký tự.',
        ],
        'email' => [
            'required' => 'Email là bắt buộc.',
            'email' => 'Vui lòng nhập địa chỉ email hợp lệ.',
            'unique' => 'Địa chỉ email đã được sử dụng. Vui lòng chọn email khác.',
        ],
        'mobile' => [
            'required' => 'Số điện thoại là bắt buộc.',
            'regex' => 'Vui lòng nhập số điện thoại hợp lệ.',
            'min' => 'Số điện thoại phải có ít nhất :min ký tự.',
            'max' => 'Số điện thoại không được vượt quá :max ký tự.',
        ],
        'address' => [
            'required' => 'Địa chỉ là bắt buộc.',
        ],
    ],
    'user_name' => [
        'required' => 'Tên người dùng là bắt buộc.',
        'unique' => 'Tên người dùng này đã được sử dụng.',
    ],
    'timezone_id' => [
        'required' => 'Múi giờ là bắt buộc.',
    ],
    'country' => [
        'required' => 'Quốc gia là bắt buộc.',
        'exists' => 'Quốc gia đã chọn không hợp lệ.',
    ],
    'state' => [
        'required_if' => 'Trường tiểu bang là bắt buộc khi quốc gia là Ấn Độ.',
    ],
    'old_password' => [
        'required' => 'Mật khẩu cũ là bắt buộc.',
        'min' => 'Mật khẩu cũ phải có ít nhất :min ký tự.',
    ],
    'new_password' => [
        'required' => 'Mật khẩu mới là bắt buộc.',
        'different' => 'Mật khẩu mới phải khác mật khẩu cũ.',
    ],
    'confirm_password' => [
        'required' => 'Xác nhận mật khẩu là bắt buộc.',
        'same' => 'Xác nhận mật khẩu phải khớp với mật khẩu mới.',
    ],
    'terms' => [
        'required' => 'Bạn phải chấp nhận điều khoản.',
    ],
    'password' => [
        'required' => 'Mật khẩu là bắt buộc.',
    ],
    'password_confirmation' => [
        'required' => 'Xác nhận mật khẩu là bắt buộc.',
        'same' => 'Mật khẩu không khớp.',
    ],
    'mobile_code' => [
        'required' => 'Nhập mã quốc gia (điện thoại)',
    ],

    // Invoice form
    'invoice' => [
        'user' => [
            'required' => 'Trường khách hàng là bắt buộc.',
        ],
        'date' => [
            'required' => 'Trường ngày là bắt buộc.',
            'date' => 'Ngày không hợp lệ.',
        ],
        'domain' => [
            'regex' => 'Định dạng tên miền không hợp lệ.',
        ],
        'plan' => [
            'required_if' => 'Trường đăng ký là bắt buộc.',
        ],
        'price' => [
            'required' => 'Trường giá là bắt buộc.',
        ],
        'product' => [
            'required' => 'Trường sản phẩm là bắt buộc.',
        ],
    ],

    // LocalizedLicense form
    'domain_form' => [
        'domain' => [
            'required' => 'Trường tên miền là bắt buộc.',
            'url' => 'Tên miền phải là một URL hợp lệ.',
        ],
    ],

    // Product Renewal form
    'product_renewal' => [
        'domain' => [
            'required' => 'Trường tên miền là bắt buộc.',
            'no_http' => 'Tên miền không được chứa "http" hoặc "https".',
        ],
    ],

    // Language form
    'language' => [
        'required' => 'Trường ngôn ngữ là bắt buộc.',
        'invalid' => 'Ngôn ngữ đã chọn không hợp lệ.',
    ],

    // UpdateStoragePathRequest form
    'storage_path' => [
        'disk' => [
            'required' => 'Trường đĩa lưu trữ là bắt buộc.',
            'string' => 'Đĩa lưu trữ phải là chuỗi.',
        ],
        'path' => [
            'string' => 'Đường dẫn phải là chuỗi.',
            'nullable' => 'Trường đường dẫn là tùy chọn.',
        ],
    ],

    // ValidateSecretRequest form
    'validate_secret' => [
        'totp' => [
            'required' => 'Vui lòng nhập mã',
            'digits' => 'Vui lòng nhập mã gồm 6 chữ số hợp lệ',
        ],
    ],

    // VerifyOtp form
    'verify_email' => [
        'required' => 'Trường email là bắt buộc.',
        'email' => 'Email phải là một địa chỉ hợp lệ.',
        'verify_email' => 'Xác minh email không thành công.',
    ],

    'verify_country_code' => [
        'required' => 'Trường mã quốc gia là bắt buộc.',
        'numeric' => 'Mã quốc gia phải là một số hợp lệ.',
        'verify_country_code' => 'Xác minh mã quốc gia không thành công.',
    ],

    'verify_number' => [
        'required' => 'Trường số là bắt buộc.',
        'numeric' => 'Số phải là một số hợp lệ.',
        'verify_number' => 'Xác minh số không thành công.',
    ],

    'password_otp' => [
        'required' => 'Trường mật khẩu là bắt buộc.',
        'password' => 'Mật khẩu không chính xác.',
        'invalid' => 'Mật khẩu không hợp lệ.',
    ],
    //AuthController file
    'auth_controller' => [
        'name_required' => 'Tên là bắt buộc.',
        'name_max' => 'Tên không được vượt quá 255 ký tự.',

        'email_required' => 'Email là bắt buộc.',
        'email_email' => 'Vui lòng nhập một địa chỉ email hợp lệ.',
        'email_max' => 'Email không được vượt quá 255 ký tự.',
        'email_unique' => 'Email này đã được đăng ký.',

        'password_required' => 'Mật khẩu là bắt buộc.',
        'password_confirmed' => 'Xác nhận mật khẩu không khớp.',
        'password_min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
    ],

    'resend_otp' => [
        'eid_required' => 'Trường EID là bắt buộc.',
        'eid_string' => 'EID phải là một chuỗi ký tự.',
        'type_required' => 'Trường loại là bắt buộc.',
        'type_string' => 'Loại phải là một chuỗi ký tự.',
        'type_in' => 'Loại được chọn không hợp lệ.',
    ],

    'verify_otp' => [
        'eid_required' => 'Mã nhân viên là bắt buộc.',
        'eid_string' => 'Mã nhân viên phải là một chuỗi ký tự.',
        'otp_required' => 'OTP là bắt buộc.',
        'otp_size' => 'OTP phải gồm chính xác 6 ký tự.',
        'recaptcha_required' => 'Vui lòng hoàn thành CAPTCHA.',
        'recaptcha_size' => 'Phản hồi CAPTCHA không hợp lệ.',
    ],

    'company_validation' => [
        'company_required' => 'Tên công ty là bắt buộc.',
        'company_string' => 'Tên công ty phải là dạng văn bản.',
        'address_required' => 'Địa chỉ là bắt buộc.',
        'address_string' => 'Địa chỉ phải là dạng văn bản.',
    ],

    'token_validation' => [
        'token_required' => 'Token là bắt buộc.',
        'password_required' => 'Trường mật khẩu là bắt buộc.',
        'password_confirmed' => 'Xác nhận mật khẩu không khớp.',
    ],

    'custom_email' => [
        'required' => 'Trường email là bắt buộc.',
        'email' => 'Vui lòng nhập địa chỉ email hợp lệ.',
        'exists' => 'Email này chưa được đăng ký với chúng tôi.',
    ],

    'newsletterEmail' => [
        'required' => 'Email bản tin là bắt buộc.',
        'email' => 'Vui lòng nhập địa chỉ email hợp lệ cho bản tin.',
    ],

    'widget' => [
        'name_required' => 'Tên là bắt buộc.',
        'name_max' => 'Tên không được vượt quá 50 ký tự.',
        'publish_required' => 'Trạng thái xuất bản là bắt buộc.',
        'type_required' => 'Loại là bắt buộc.',
        'type_unique' => 'Loại này đã tồn tại.',
    ],

    'payment' => [
        'payment_date_required' => 'Ngày thanh toán là bắt buộc.',
        'payment_method_required' => 'Phương thức thanh toán là bắt buộc.',
        'amount_required' => 'Số tiền là bắt buộc.',
    ],

    'custom_date' => [
        'date_required' => 'Trường ngày là bắt buộc.',
        'total_required' => 'Trường tổng là bắt buộc.',
        'status_required' => 'Trường trạng thái là bắt buộc.',
    ],

    'plan_renewal' => [
        'plan_required' => 'Trường gói là bắt buộc.',
        'payment_method_required' => 'Trường phương thức thanh toán là bắt buộc.',
        'cost_required' => 'Trường chi phí là bắt buộc.',
        'code_not_valid' => 'Mã khuyến mãi không hợp lệ.',
    ],

    'rate' => [
        'required' => 'Tỷ lệ là bắt buộc.',
        'numeric' => 'Tỷ lệ phải là một số.',
    ],

    'product_validate' => [
        'producttitle_required' => 'Tiêu đề sản phẩm là bắt buộc.',
        'version_required' => 'Phiên bản là bắt buộc.',
        'filename_required' => 'Vui lòng tải lên một tệp.',
        'dependencies_required' => 'Trường phụ thuộc là bắt buộc.',
    ],
    'product_sku_unique' => 'SKU sản phẩm phải là duy nhất.',
    'product_name_unique' => 'Tên phải là duy nhất.',
    'product_show_agent_required' => 'Chọn tùy chọn trang giỏ hàng của bạn.',
    'product_controller' => [
        'name_required' => 'Tên sản phẩm là bắt buộc.',
        'name_unique' => 'Tên phải là duy nhất.',
        'type_required' => 'Loại sản phẩm là bắt buộc.',
        'description_required' => 'Mô tả sản phẩm là bắt buộc.',
        'product_description_required' => 'Mô tả chi tiết sản phẩm là bắt buộc.',
        'image_mimes' => 'Hình ảnh phải là tệp có loại: jpeg, png, jpg.',
        'image_max' => 'Kích thước hình ảnh không được vượt quá 2048 kilobytes.',
        'product_sku_required' => 'SKU sản phẩm là bắt buộc.',
        'group_required' => 'Nhóm sản phẩm là bắt buộc.',
        'show_agent_required' => 'Chọn tùy chọn trang giỏ hàng của bạn.',
    ],
    'current_domain_required' => 'Tên miền hiện tại là bắt buộc.',
    'new_domain_required' => 'Tên miền mới là bắt buộc.',
    'special_characters_not_allowed' => 'Không được phép sử dụng ký tự đặc biệt trong tên miền.',
    'orderno_required' => 'Số đơn hàng là bắt buộc.',
    'cloud_central_domain_required' => 'Tên miền trung tâm đám mây là bắt buộc.',
    'cloud_cname_required' => 'CNAME đám mây là bắt buộc.',
    'cloud_tenant' => [
        'cloud_top_message_required' => 'Thông điệp trên đám mây là bắt buộc.',
        'cloud_label_field_required' => 'Trường nhãn đám mây là bắt buộc.',
        'cloud_label_radio_required' => 'Radio nhãn đám mây là bắt buộc.',
        'cloud_product_required' => 'Sản phẩm đám mây là bắt buộc.',
        'cloud_free_plan_required' => 'Kế hoạch miễn phí đám mây là bắt buộc.',
        'cloud_product_key_required' => 'Khóa sản phẩm đám mây là bắt buộc.',
    ],
    'reg_till_after' => 'Ngày đăng ký đến phải sau ngày đăng ký từ.',
    'extend_product' => [
        'title_required' => 'Trường tiêu đề là bắt buộc.',
        'version_required' => 'Trường phiên bản là bắt buộc.',
        'dependencies_required' => 'Trường phụ thuộc là bắt buộc.',
    ],
    'please_enter_recovery_code' => 'Vui lòng nhập mã khôi phục.',
    'social_login' => [
        'client_id_required' => 'ID khách hàng là bắt buộc đối với Google, Github hoặc Linkedin.',
        'client_secret_required' => 'Mật khẩu khách hàng là bắt buộc đối với Google, Github hoặc Linkedin.',
        'api_key_required' => 'Khóa API là bắt buộc đối với Twitter.',
        'api_secret_required' => 'Mật khẩu API là bắt buộc đối với Twitter.',
        'redirect_url_required' => 'URL chuyển hướng là bắt buộc.',
    ],
    'thirdparty_api' => [
        'app_name_required' => 'Tên ứng dụng là bắt buộc.',
        'app_key_required' => 'Khóa ứng dụng là bắt buộc.',
        'app_key_size' => 'Khóa ứng dụng phải có đúng 32 ký tự.',
        'app_secret_required' => 'Mật khẩu ứng dụng là bắt buộc.',
    ],

];
