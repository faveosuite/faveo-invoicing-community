<?php

return [

    'accepted' => ':attribute harus diterima.',
    'accepted_if' => ':attribute harus diterima ketika :other adalah :value.',
    'active_url' => ':attribute bukan URL yang valid.',
    'after' => ':attribute harus berupa tanggal setelah :date.',
    'after_or_equal' => ':attribute harus berupa tanggal setelah atau sama dengan :date.',
    'alpha' => ':attribute hanya boleh berisi huruf.',
    'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
    'alpha_num' => ':attribute hanya boleh berisi huruf dan angka.',
    'array' => ':attribute harus berupa array.',
    'before' => ':attribute harus berupa tanggal sebelum :date.',
    'before_or_equal' => ':attribute harus berupa tanggal sebelum atau sama dengan :date.',
    'between' => [
        'array' => ':attribute harus memiliki antara :min dan :max item.',
        'file' => ':attribute harus memiliki antara :min dan :max kilobyte.',
        'numeric' => ':attribute harus memiliki antara :min dan :max.',
        'string' => ':attribute harus memiliki antara :min dan :max karakter.',
    ],
    'boolean' => 'Field :attribute harus berupa true atau false.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'current_password' => 'Kata sandi salah.',
    'date' => ':attribute bukan tanggal yang valid.',
    'date_equals' => ':attribute harus berupa tanggal yang sama dengan :date.',
    'date_format' => ':attribute tidak cocok dengan format :format.',
    'declined' => ':attribute harus ditolak.',
    'declined_if' => ':attribute harus ditolak ketika :other adalah :value.',
    'different' => ':attribute dan :other harus berbeda.',
    'digits' => ':attribute harus berupa :digits digit.',
    'digits_between' => ':attribute harus memiliki antara :min dan :max digit.',
    'dimensions' => ':attribute memiliki dimensi gambar yang tidak valid.',
    'distinct' => 'Field :attribute memiliki nilai yang duplikat.',
    'doesnt_start_with' => ':attribute tidak boleh dimulai dengan salah satu dari berikut: :values.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'ends_with' => ':attribute harus diakhiri dengan salah satu dari berikut: :values.',
    'enum' => ':attribute yang dipilih tidak valid.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'file' => ':attribute harus berupa file.',
    'filled' => 'Field :attribute harus memiliki nilai.',
    'gt' => [
        'array' => ':attribute harus memiliki lebih dari :value item.',
        'file' => ':attribute harus lebih besar dari :value kilobyte.',
        'numeric' => ':attribute harus lebih besar dari :value.',
        'string' => ':attribute harus lebih besar dari :value karakter.',
    ],
    'gte' => [
        'array' => ':attribute harus memiliki :value item atau lebih.',
        'file' => ':attribute harus lebih besar dari atau sama dengan :value kilobyte.',
        'numeric' => ':attribute harus lebih besar dari atau sama dengan :value.',
        'string' => ':attribute harus lebih besar dari atau sama dengan :value karakter.',
    ],
    'image' => ':attribute harus berupa gambar.',
    'in' => ':attribute yang dipilih tidak valid.',
    'in_array' => 'Field :attribute tidak ada di :other.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'ip' => ':attribute harus berupa alamat IP yang valid.',
    'ipv4' => ':attribute harus berupa alamat IPv4 yang valid.',
    'ipv6' => ':attribute harus berupa alamat IPv6 yang valid.',
    'json' => ':attribute harus berupa string JSON yang valid.',
    'lt' => [
        'array' => ':attribute harus memiliki kurang dari :value item.',
        'file' => ':attribute harus lebih kecil dari :value kilobyte.',
        'numeric' => ':attribute harus lebih kecil dari :value.',
        'string' => ':attribute harus lebih kecil dari :value karakter.',
    ],
    'lte' => [
        'array' => ':attribute tidak boleh memiliki lebih dari :value item.',
        'file' => ':attribute tidak boleh lebih besar dari atau sama dengan :value kilobyte.',
        'numeric' => ':attribute tidak boleh lebih besar dari atau sama dengan :value.',
        'string' => ':attribute tidak boleh lebih besar dari atau sama dengan :value karakter.',
    ],
    'mac_address' => ':attribute harus berupa alamat MAC yang valid.',
    'max' => [
        'array' => ':attribute tidak boleh memiliki lebih dari :max item.',
        'file' => ':attribute tidak boleh lebih besar dari :max kilobyte.',
        'numeric' => ':attribute tidak boleh lebih besar dari :max.',
        'string' => ':attribute tidak boleh lebih besar dari :max karakter.',
    ],
    'mimes' => ':attribute harus berupa file tipe: :values.',
    'mimetypes' => ':attribute harus berupa file tipe: :values.',
    'min' => [
        'array' => ':attribute harus memiliki minimal :min item.',
        'file' => ':attribute harus memiliki minimal :min kilobyte.',
        'numeric' => ':attribute harus memiliki minimal :min.',
        'string' => ':attribute harus memiliki minimal :min karakter.',
    ],
    'multiple_of' => ':attribute harus kelipatan dari :value.',
    'not_in' => ':attribute yang dipilih tidak valid.',
    'not_regex' => 'Format :attribute tidak valid.',
    'numeric' => ':attribute harus berupa angka.',
    'password' => [
        'letters' => ':attribute harus mengandung setidaknya satu huruf.',
        'mixed' => ':attribute harus mengandung setidaknya satu huruf besar dan satu huruf kecil.',
        'numbers' => ':attribute harus mengandung setidaknya satu angka.',
        'symbols' => ':attribute harus mengandung setidaknya satu simbol.',
        'uncompromised' => ':attribute yang diberikan telah muncul dalam kebocoran data. Silakan pilih :attribute lain.',
    ],
    'present' => 'Field :attribute harus ada.',
    'prohibited' => 'Field :attribute dilarang.',
    'prohibited_if' => 'Field :attribute dilarang ketika :other adalah :value.',
    'prohibited_unless' => 'Field :attribute dilarang kecuali :other ada dalam :values.',
    'prohibits' => 'Field :attribute melarang :other untuk hadir.',
    'regex' => 'Format :attribute tidak valid.',
    'required' => 'Field :attribute wajib diisi.',
    'required_array_keys' => 'Field :attribute harus berisi entri untuk: :values.',
    'required_if' => 'Field :attribute wajib diisi ketika :other adalah :value.',
    'required_unless' => 'Field :attribute wajib diisi kecuali :other ada dalam :values.',
    'required_with' => 'Field :attribute wajib diisi ketika :values ada.',
    'required_with_all' => 'Field :attribute wajib diisi ketika :values ada.',
    'required_without' => 'Field :attribute wajib diisi ketika :values tidak ada.',
    'required_without_all' => 'Field :attribute wajib diisi ketika tidak ada :values.',
    'same' => ':attribute dan :other harus cocok.',
    'size' => [
        'array' => ':attribute harus mengandung :size item.',
        'file' => ':attribute harus berukuran :size kilobyte.',
        'numeric' => ':attribute harus bernilai :size.',
        'string' => ':attribute harus mengandung :size karakter.',
    ],
    'starts_with' => ':attribute harus dimulai dengan salah satu dari berikut: :values.',
    'string' => ':attribute harus berupa string.',
    'timezone' => ':attribute harus berupa zona waktu yang valid.',
    'unique' => ':attribute sudah terpakai.',
    'uploaded' => 'Upload :attribute gagal.',
    'url' => ':attribute harus berupa URL yang valid.',
    'uuid' => ':attribute harus berupa UUID yang valid.',

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
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],
    'publish_date_required' => 'Tanggal terbit wajib diisi',
    'price_numeric_value' => 'Harga harus berupa nilai numerik',
    'quantity_integer_value' => 'Kuantitas harus berupa bilangan bulat',
    'order_has_Expired' => 'Pesanan telah kedaluwarsa',
    'expired' => 'Kedaluwarsa',
    'eid_required' => 'Kolom EID wajib diisi.',
    'eid_string' => 'EID harus berupa string.',
    'otp_required' => 'Kolom OTP wajib diisi.',
    'amt_required' => 'Kolom jumlah wajib diisi',
    'amt_numeric' => 'Jumlah harus berupa angka',
    'payment_date_required' => 'Tanggal pembayaran wajib diisi.',
    'payment_method_required' => 'Metode pembayaran wajib diisi.',
    'total_amount_required' => 'Jumlah total wajib diisi.',
    'total_amount_numeric' => 'Jumlah total harus berupa nilai numerik.',
    'invoice_link_required' => 'Harap tautkan jumlah dengan setidaknya satu Faktur.',

    /*
    Request file custom validation messages
    */

//Common
    'settings_form' => [
        'company' => [
            'required' => 'Kolom perusahaan wajib diisi.',
        ],
        'website' => [
            'url' => 'Website harus berupa URL yang valid.',
        ],
        'phone' => [
            'regex' => 'Format nomor telepon tidak valid.',
        ],
        'address' => [
            'required' => 'Kolom alamat wajib diisi.',
            'max' => 'Alamat tidak boleh lebih dari 300 karakter.',
        ],
        'logo' => [
            'mimes' => 'Logo harus berupa file PNG.',
        ],
        'driver' => [
            'required' => 'Kolom driver wajib diisi.',
        ],
        'port' => [
            'integer' => 'Port harus berupa bilangan bulat.',
        ],
        'email' => [
            'required' => 'Kolom email wajib diisi.',
            'email' => 'Email harus berupa alamat email yang valid.',
        ],
        'password' => [
            'required' => 'Kolom kata sandi wajib diisi.',
        ],
        'error_email' => [
            'email' => 'Email kesalahan harus berupa alamat email yang valid.',
        ],
    ],

    'settings_forms' => [
        'company' => [
            'required' => 'Nama perusahaan wajib diisi.',
            'max' => 'Nama perusahaan tidak boleh melebihi 50 karakter.',
        ],
        'company_email' => [
            'required' => 'Email perusahaan wajib diisi.',
            'email' => 'Email perusahaan harus berupa alamat email yang valid.',
        ],
        'title' => [
            'max' => 'Judul tidak boleh melebihi 50 karakter.',
        ],
        'website' => [
            'required' => 'URL situs web wajib diisi.',
            'url' => 'Website harus berupa URL yang valid.',
            'regex' => 'Format website tidak valid.',
        ],
        'phone' => [
            'required' => 'Nomor telepon wajib diisi.',
        ],
        'address' => [
            'required' => 'Alamat wajib diisi.',
        ],
        'state' => [
            'required' => 'Negara bagian wajib diisi.',
        ],
        'country' => [
            'required' => 'Negara wajib diisi.',
        ],
        'gstin' => [
            'max' => 'GSTIN tidak boleh melebihi 15 karakter.',
        ],
        'default_currency' => [
            'required' => 'Mata uang default wajib diisi.',
        ],
        'admin_logo' => [
            'mimes' => 'Logo admin harus berupa file dengan tipe: jpeg, png, jpg.',
            'max' => 'Logo admin tidak boleh lebih dari 2MB.',
        ],
        'fav_icon' => [
            'mimes' => 'Fav icon harus berupa file dengan tipe: jpeg, png, jpg.',
            'max' => 'Fav icon tidak boleh lebih dari 2MB.',
        ],
        'logo' => [
            'mimes' => 'Logo harus berupa file dengan tipe: jpeg, png, jpg.',
            'max' => 'Logo tidak boleh lebih dari 2MB.',
        ],
    ],

    'social_media_form' => [
        'name' => [
            'required' => 'Kolom nama wajib diisi.',
            'unique' => 'Nama ini sudah digunakan.',
            'max' => 'Nama tidak boleh lebih dari 50 karakter.',
        ],
        'link' => [
            'required' => 'Kolom tautan wajib diisi.',
            'url' => 'Tautan harus berupa URL yang valid.',
            'regex' => 'Format tautan tidak valid.',
        ],
    ],

//Email
    'custom' => [
        'password' => [
            'required_if' => 'Kolom kata sandi wajib diisi untuk driver email yang dipilih.',
        ],
        'port' => [
            'required_if' => 'Kolom port wajib diisi untuk SMTP.',
        ],
        'encryption' => [
            'required_if' => 'Kolom enkripsi wajib diisi untuk SMTP.',
        ],
        'host' => [
            'required_if' => 'Kolom host wajib diisi untuk SMTP.',
        ],
        'secret' => [
            'required_if' => 'Kolom secret wajib diisi untuk driver email yang dipilih.',
        ],
        'domain' => [
            'required_if' => 'Kolom domain wajib diisi untuk Mailgun.',
        ],
        'key' => [
            'required_if' => 'Kolom key wajib diisi untuk SES.',
        ],
        'region' => [
            'required_if' => 'Kolom region wajib diisi untuk SES.',
        ],
        'email' => [
            'required_if' => 'Kolom email wajib diisi untuk driver email yang dipilih.',
            'required' => 'Kolom email wajib diisi.',
            'email' => 'Silakan masukkan alamat email yang valid.',
            'not_matching' => 'Domain email harus sesuai dengan domain situs saat ini.',
        ],
        'driver' => [
            'required' => 'Kolom driver wajib diisi.',
        ],
    ],

    'customer_form' => [
        'first_name' => [
            'required' => 'Kolom nama depan wajib diisi.',
        ],
        'last_name' => [
            'required' => 'Kolom nama belakang wajib diisi.',
        ],
        'company' => [
            'required' => 'Kolom perusahaan wajib diisi.',
        ],
        'mobile' => [
            'regex' => 'Format nomor ponsel tidak valid.',
        ],
        'address' => [
            'required' => 'Kolom alamat wajib diisi.',
        ],
        'zip' => [
            'required' => 'Kolom kode pos wajib diisi.',
            'min' => 'Kode pos harus terdiri dari minimal 5 digit.',
            'numeric' => 'Kode pos harus berupa angka.',
        ],
        'email' => [
            'required' => 'Kolom email wajib diisi.',
            'email' => 'Email harus berupa alamat email yang valid.',
            'unique' => 'Email ini sudah digunakan.',
        ],
    ],
    'contact_request' => [
        'conName' => 'Kolom nama wajib diisi.',
        'email' => 'Kolom email wajib diisi.',
        'conmessage' => 'Kolom pesan wajib diisi.',
        'Mobile' => 'Kolom nomor ponsel wajib diisi.',
        'country_code' => 'Kolom nomor ponsel wajib diisi.',
        'demoname' => 'Kolom nama wajib diisi.',
        'demomessage' => 'Kolom pesan wajib diisi.',
        'demoemail' => 'Kolom email wajib diisi.',
        'congg-recaptcha-response-1.required' => 'Verifikasi robot gagal. Silakan coba lagi.',
        'demo-recaptcha-response-1.required' => 'Verifikasi robot gagal. Silakan coba lagi.',
    ],

    'frontend_pages' => [
        'name' => [
            'required' => 'Kolom nama wajib diisi.',
            'unique' => 'Nama ini sudah ada.',
            'max' => 'Nama tidak boleh lebih dari 20 karakter.',
            'regex' => 'Nama hanya boleh berisi huruf dan spasi.',
        ],
        'publish' => [
            'required' => 'Kolom publikasi wajib diisi.',
        ],
        'slug' => [
            'required' => 'Kolom slug wajib diisi.',
        ],
        'url' => [
            'required' => 'Kolom URL wajib diisi.',
            'url' => 'URL harus berupa tautan yang valid.',
            'regex' => 'Format URL tidak valid.',
        ],
        'content' => [
            'required' => 'Kolom konten wajib diisi.',
        ],
        'created_at' => [
            'required' => 'Kolom tanggal dibuat wajib diisi.',
        ],
    ],

//Order form
    'order_form' => [
        'client' => [
            'required' => 'Kolom klien wajib diisi.',
        ],
        'payment_method' => [
            'required' => 'Kolom metode pembayaran wajib diisi.',
        ],
        'promotion_code' => [
            'required' => 'Kolom kode promosi wajib diisi.',
        ],
        'order_status' => [
            'required' => 'Kolom status pesanan wajib diisi.',
        ],
        'product' => [
            'required' => 'Kolom produk wajib diisi.',
        ],
        'subscription' => [
            'required' => 'Kolom langganan wajib diisi.',
        ],
        'price_override' => [
            'numeric' => 'Harga override harus berupa angka.',
        ],
        'qty' => [
            'integer' => 'Jumlah harus berupa bilangan bulat.',
        ],
    ],

//Payment form
    'coupon_form' => [
        'code' => [
            'required' => 'Kolom kode kupon wajib diisi.',
            'string' => 'Kode kupon harus berupa string.',
            'max' => 'Kode kupon tidak boleh melebihi 255 karakter.',
        ],
        'type' => [
            'required' => 'Kolom tipe wajib diisi.',
            'in' => 'Tipe tidak valid. Nilai yang diperbolehkan: percentage, other_type.',
        ],
        'applied' => [
            'required' => 'Kolom penerapan untuk produk wajib diisi.',
            'date' => 'Kolom penerapan harus berupa tanggal yang valid.',
        ],
        'uses' => [
            'required' => 'Kolom penggunaan wajib diisi.',
            'numeric' => 'Kolom penggunaan harus berupa angka.',
            'min' => 'Kolom penggunaan minimal harus :min.',
        ],
        'start' => [
            'required' => 'Kolom mulai wajib diisi.',
            'date' => 'Kolom mulai harus berupa tanggal yang valid.',
        ],
        'expiry' => [
            'required' => 'Kolom kedaluwarsa wajib diisi.',
            'date' => 'Kolom kedaluwarsa harus berupa tanggal yang valid.',
            'after' => 'Tanggal kedaluwarsa harus setelah tanggal mulai.',
        ],
        'value' => [
            'required' => 'Kolom nilai diskon wajib diisi.',
            'numeric' => 'Kolom nilai diskon harus berupa angka.',
            'between' => 'Kolom nilai diskon harus antara :min dan :max jika tipenya adalah persentase.',
        ],
    ],

    'tax_form' => [
        'name' => [
            'required' => 'Kolom nama wajib diisi.',
        ],
        'rate' => [
            'required' => 'Kolom tarif wajib diisi.',
            'numeric' => 'Tarif harus berupa angka.',
        ],
        'level' => [
            'required' => 'Kolom tingkat wajib diisi.',
            'integer' => 'Tingkat harus berupa bilangan bulat.',
        ],
        'country' => [
            'required' => 'Kolom negara wajib diisi.',
            // 'exists' => 'Negara yang dipilih tidak valid.',
        ],
        'state' => [
            'required' => 'Kolom negara bagian wajib diisi.',
            // 'exists' => 'Negara bagian yang dipilih tidak valid.',
        ],
    ],

//Product
    'subscription_form' => [
        'name' => [
            'required' => 'Kolom nama wajib diisi.',
        ],
        'subscription' => [
            'required' => 'Kolom langganan wajib diisi.',
        ],
        'regular_price' => [
            'required' => 'Kolom harga normal wajib diisi.',
            'numeric' => 'Harga normal harus berupa angka.',
        ],
        'selling_price' => [
            'required' => 'Kolom harga jual wajib diisi.',
            'numeric' => 'Harga jual harus berupa angka.',
        ],
        'products' => [
            'required' => 'Kolom produk wajib diisi.',
        ],
    ],
    'bundle' => [
        'name' => [
            'required' => 'Kolom nama wajib diisi.',
        ],
        'items' => [
            'required' => 'Setiap item wajib diisi.',
        ],
    ],

    'group' => [
        'name' => [
            'required' => 'Nama wajib diisi',
        ],
        'features' => [
            'name' => [
                'required' => 'Semua kolom fitur wajib diisi',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => 'Harga wajib diisi',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => 'Nilai wajib diisi',
            ],
        ],
        'type' => [
            'required_with' => 'Tipe wajib diisi',
        ],
        'title' => [
            'required_with' => 'Judul wajib diisi',
        ],
    ],

    'product' => [
        'name' => [
            'required' => 'Kolom nama wajib diisi.',
        ],
        'type' => [
            'required' => 'Kolom tipe wajib diisi.',
        ],
        'group' => [
            'required' => 'Kolom grup wajib diisi.',
        ],
        'subscription' => [
            'required' => 'Kolom langganan wajib diisi.',
        ],
        'currency' => [
            'required' => 'Kolom mata uang wajib diisi.',
        ],
        'file' => [
            'required_without_all' => 'Kolom file wajib diisi jika github_owner atau github_repository tidak tersedia.',
            'mimes' => 'File harus berupa file zip.',
        ],
        'image' => [
            'required_without_all' => 'Kolom gambar wajib diisi jika github_owner atau github_repository tidak tersedia.',
            'mimes' => 'Gambar harus berupa file PNG.',
        ],
        'github_owner' => [
            'required_without_all' => 'Kolom pemilik GitHub wajib diisi jika file atau gambar tidak tersedia.',
        ],
        'github_repository' => [
            'required_without_all' => 'Kolom repositori GitHub wajib diisi jika file atau gambar tidak tersedia.',
            'required_if' => 'Kolom repositori GitHub wajib diisi jika tipe adalah 2.',
        ],
    ],

//User
    'users' => [
        'first_name' => [
            'required' => 'Kolom nama depan wajib diisi.',
        ],
        'last_name' => [
            'required' => 'Kolom nama belakang wajib diisi.',
        ],
        'company' => [
            'required' => 'Kolom perusahaan wajib diisi.',
        ],
        'email' => [
            'required' => 'Kolom email wajib diisi.',
            'email' => 'Email harus berupa alamat email yang valid.',
            'unique' => 'Email sudah digunakan.',
        ],
        'address' => [
            'required' => 'Kolom alamat wajib diisi.',
        ],
        'mobile' => [
            'required' => 'Kolom nomor ponsel wajib diisi.',
        ],
        'country' => [
            'required' => 'Kolom negara wajib diisi.',
            'exists' => 'Negara yang dipilih tidak valid.',
        ],
        'state' => [
            'required_if' => 'Kolom provinsi wajib diisi jika negara adalah India.',
        ],
        'timezone_id' => [
            'required' => 'Kolom zona waktu wajib diisi.',
        ],
        'user_name' => [
            'required' => 'Kolom nama pengguna wajib diisi.',
            'unique' => 'Nama pengguna sudah digunakan.',
        ],
        'zip' => [
            'regex' => 'Kolom provinsi wajib diisi jika negara adalah India.',
        ]
    ],

    'profile_form' => [
        'first_name' => [
            'required' => 'Nama depan wajib diisi.',
            'min' => 'Nama depan minimal :min karakter.',
            'max' => 'Nama depan tidak boleh lebih dari :max karakter.',
        ],
        'last_name' => [
            'required' => 'Nama belakang wajib diisi.',
            'max' => 'Nama belakang tidak boleh lebih dari :max karakter.',
        ],
        'company' => [
            'required' => 'Nama perusahaan wajib diisi.',
            'max' => 'Nama perusahaan tidak boleh lebih dari :max karakter.',
        ],
        'email' => [
            'required' => 'Email wajib diisi.',
            'email' => 'Masukkan alamat email yang valid.',
            'unique' => 'Alamat email sudah digunakan. Silakan gunakan email lain.',
        ],
        'mobile' => [
            'required' => 'Nomor ponsel wajib diisi.',
            'regex' => 'Masukkan nomor ponsel yang valid.',
            'min' => 'Nomor ponsel minimal :min karakter.',
            'max' => 'Nomor ponsel tidak boleh lebih dari :max karakter.',
        ],
        'address' => [
            'required' => 'Alamat wajib diisi.',
        ],
        'user_name' => [
            'required' => 'Nama pengguna wajib diisi.',
            'unique' => 'Nama pengguna ini sudah digunakan.',
        ],
    ],
    'timezone_id' => [
        'required' => 'Zona waktu wajib diisi.',
    ],
    'country' => [
        'required' => 'Negara wajib diisi.',
        'exists' => 'Negara yang dipilih tidak valid.',
    ],
    'state' => [
        'required_if' => 'Kolom provinsi wajib diisi jika negara adalah India.',
    ],
    'old_password' => [
        'required' => 'Kata sandi lama wajib diisi.',
        'min' => 'Kata sandi lama minimal :min karakter.',
    ],
    'new_password' => [
        'required' => 'Kata sandi baru wajib diisi.',
        'different' => 'Kata sandi baru harus berbeda dari kata sandi lama.',
    ],
    'confirm_password' => [
        'required' => 'Konfirmasi kata sandi wajib diisi.',
        'same' => 'Konfirmasi kata sandi harus sama dengan kata sandi baru.',
    ],
    'terms' => [
        'required' => 'Anda harus menyetujui syarat dan ketentuan.',
    ],
    'password' => [
        'required' => 'Kata sandi wajib diisi.',
    ],
    'password_confirmation' => [
        'required' => 'Konfirmasi kata sandi wajib diisi.',
        'same' => 'Kata sandi tidak cocok.',
    ],
    'mobile_code' => [
        'required' => 'Masukkan kode negara (ponsel)',
    ],

//Invoice form
    'invoice' => [
        'user' => [
            'required' => 'Kolom klien wajib diisi.',
        ],
        'date' => [
            'required' => 'Kolom tanggal wajib diisi.',
            'date' => 'Tanggal harus berupa tanggal yang valid.',
        ],
        'domain' => [
            'regex' => 'Format domain tidak valid.',
        ],
        'plan' => [
            'required_if' => 'Kolom langganan wajib diisi.',
        ],
        'price' => [
            'required' => 'Kolom harga wajib diisi.',
        ],
        'product' => [
            'required' => 'Kolom produk wajib diisi.',
        ],
    ],

//LocalizedLicense form
    'domain_form' => [
        'domain' => [
            'required' => 'Kolom domain wajib diisi.',
            'url' => 'Domain harus berupa URL yang valid.',
        ],
    ],

//Product Renewal form
    'product_renewal' => [
        'domain' => [
            'required' => 'Kolom domain wajib diisi.',
            'no_http' => 'Domain tidak boleh mengandung "http" atau "https".',
        ],
    ],

//Language form
    'language' => [
        'required' => 'Kolom bahasa wajib diisi.',
        'invalid' => 'Bahasa yang dipilih tidak valid.',
    ],

//UpdateSroragePathRequest form
    'storage_path' => [
        'disk' => [
            'required' => 'Kolom penyimpanan wajib diisi.',
            'string' => 'Penyimpanan harus berupa teks.',
        ],
        'path' => [
            'string' => 'Path harus berupa teks.',
            'nullable' => 'Kolom path bersifat opsional.',
        ],
    ],

//ValidateSecretRequest form
    'validate_secret' => [
        'totp' => [
            'required' => 'Silakan masukkan kode',
            'digits' => 'Silakan masukkan kode 6 digit yang valid',
        ],
    ],

//VerifyOtp form
    'verify_email' => [
        'required' => 'Kolom email wajib diisi.',
        'email' => 'Email harus berupa alamat email yang valid.',
        'verify_email' => 'Verifikasi email gagal.',
    ],

    'verify_country_code' => [
        'required' => 'Kode negara wajib diisi.',
        'numeric' => 'Kode negara harus berupa angka yang valid.',
        'verify_country_code' => 'Verifikasi kode negara gagal.',
    ],

    'verify_number' => [
        'required' => 'Nomor wajib diisi.',
        'numeric' => 'Nomor harus berupa angka yang valid.',
        'verify_number' => 'Verifikasi nomor gagal.',
    ],

    'password_otp' => [
        'required' => 'Kolom kata sandi wajib diisi.',
        'password' => 'Kata sandi salah.',
        'invalid' => 'Kata sandi tidak valid.',
    ],

//AuthController file
    'auth_controller' => [
        'name_required' => 'Nama wajib diisi.',
        'name_max' => 'Nama tidak boleh lebih dari 255 karakter.',

        'email_required' => 'Email wajib diisi.',
        'email_email' => 'Masukkan alamat email yang valid.',
        'email_max' => 'Email tidak boleh lebih dari 255 karakter.',
        'email_unique' => 'Email ini sudah terdaftar.',

        'password_required' => 'Kata sandi wajib diisi.',
        'password_confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        'password_min' => 'Kata sandi minimal 6 karakter.',
    ],

    'resend_otp' => [
        'eid_required' => 'Kolom EID wajib diisi.',
        'eid_string' => 'EID harus berupa teks.',
        'type_required' => 'Kolom tipe wajib diisi.',
        'type_string' => 'Tipe harus berupa teks.',
        'type_in' => 'Tipe yang dipilih tidak valid.',
    ],

    'verify_otp' => [
        'eid_required' => 'ID karyawan wajib diisi.',
        'eid_string' => 'ID karyawan harus berupa teks.',
        'otp_required' => 'OTP wajib diisi.',
        'otp_size' => 'OTP harus terdiri dari tepat 6 karakter.',
        'recaptcha_required' => 'Silakan selesaikan CAPTCHA.',
        'recaptcha_size' => 'Respon CAPTCHA tidak valid.',
    ],

    'company_validation' => [
        'company_required' => 'Nama perusahaan wajib diisi.',
        'company_string' => 'Nama perusahaan harus berupa teks.',
        'address_required' => 'Alamat wajib diisi.',
        'address_string' => 'Alamat harus berupa teks.',
    ],

    'token_validation' => [
        'token_required' => 'Token wajib diisi.',
        'password_required' => 'Kolom kata sandi wajib diisi.',
        'password_confirmed' => 'Konfirmasi kata sandi tidak cocok.',
    ],

    'custom_email' => [
        'required' => 'Kolom email wajib diisi.',
        'email' => 'Masukkan alamat email yang valid.',
        'exists' => 'Email ini tidak terdaftar di sistem kami.',
    ],

    'newsletterEmail' => [
        'required' => 'Email newsletter wajib diisi.',
        'email' => 'Masukkan alamat email yang valid untuk newsletter.',
    ],

    'widget' => [
        'name_required' => 'Nama wajib diisi.',
        'name_max' => 'Nama tidak boleh lebih dari 50 karakter.',
        'publish_required' => 'Status publikasi wajib diisi.',
        'type_required' => 'Tipe wajib diisi.',
        'type_unique' => 'Tipe ini sudah ada.',
    ],

    'payment' => [
        'payment_date_required' => 'Tanggal pembayaran wajib diisi.',
        'payment_method_required' => 'Metode pembayaran wajib diisi.',
        'amount_required' => 'Jumlah wajib diisi.',
    ],

    'custom_date' => [
        'date_required' => 'Kolom tanggal wajib diisi.',
        'total_required' => 'Kolom total wajib diisi.',
        'status_required' => 'Kolom status wajib diisi.',
    ],

    'plan_renewal' => [
        'plan_required' => 'Kolom paket wajib diisi.',
        'payment_method_required' => 'Kolom metode pembayaran wajib diisi.',
        'cost_required' => 'Kolom biaya wajib diisi.',
        'code_not_valid' => 'Kode promo tidak valid.',
    ],

    'rate' => [
        'required' => 'Tarif wajib diisi.',
        'numeric' => 'Tarif harus berupa angka.',
    ],

    'product_validate' => [
        'producttitle_required' => 'Judul produk wajib diisi.',
        'version_required' => 'Versi wajib diisi.',
        'filename_required' => 'Silakan unggah file.',
        'dependencies_required' => 'Kolom dependensi wajib diisi.',
    ],
    'product_sku_unique' => 'SKU produk harus unik',
    'product_name_unique' => 'Nama harus unik',
    'product_show_agent_required' => 'Pilih preferensi halaman keranjang Anda',
    'product_controller' => [
        'name_required' => 'Nama produk wajib diisi.',
        'name_unique' => 'Nama harus unik.',
        'type_required' => 'Jenis produk wajib diisi.',
        'description_required' => 'Deskripsi produk wajib diisi.',
        'product_description_required' => 'Deskripsi lengkap produk wajib diisi.',
        'image_mimes' => 'Gambar harus berupa file dengan tipe: jpeg, png, jpg.',
        'image_max' => 'Ukuran gambar tidak boleh lebih dari 2048 kilobyte.',
        'product_sku_required' => 'SKU produk wajib diisi.',
        'group_required' => 'Grup produk wajib diisi.',
        'show_agent_required' => 'Pilih preferensi halaman keranjang Anda.',
    ],
    'current_domain_required' => 'Domain saat ini wajib diisi.',
    'new_domain_required' => 'Domain baru wajib diisi.',
    'special_characters_not_allowed' => 'Karakter khusus tidak diperbolehkan dalam nama domain',
    'orderno_required' => 'Nomor pesanan wajib diisi',
    'cloud_central_domain_required' => 'Domain pusat cloud wajib diisi.',
    'cloud_cname_required' => 'CNAME Cloud wajib diisi.',
    'cloud_tenant' => [
        'cloud_top_message_required' => 'Pesan atas cloud wajib diisi.',
        'cloud_label_field_required' => 'Label kolom cloud wajib diisi.',
        'cloud_label_radio_required' => 'Label radio cloud wajib diisi.',
        'cloud_product_required' => 'Produk cloud wajib diisi.',
        'cloud_free_plan_required' => 'Paket gratis cloud wajib diisi.',
        'cloud_product_key_required' => 'Kunci produk cloud wajib diisi.',
    ],
    'reg_till_after' => 'Tanggal "hingga" pendaftaran harus setelah tanggal "dari" pendaftaran.',
    'extend_product' => [
        'title_required' => 'Kolom judul wajib diisi.',
        'version_required' => 'Kolom versi wajib diisi.',
        'dependencies_required' => 'Kolom dependensi wajib diisi.',
    ],
    'please_enter_recovery_code' => 'Silakan masukkan kode pemulihan',
    'social_login' => [
        'client_id_required' => 'Client ID wajib diisi untuk Google, Github, atau Linkedin.',
        'client_secret_required' => 'Client Secret wajib diisi untuk Google, Github, atau Linkedin.',
        'api_key_required' => 'API Key wajib diisi untuk Twitter.',
        'api_secret_required' => 'API Secret wajib diisi untuk Twitter.',
        'redirect_url_required' => 'Redirect URL wajib diisi.',
    ],
    'thirdparty_api' => [
        'app_name_required' => 'Nama aplikasi wajib diisi.',
        'app_key_required' => 'Kunci aplikasi wajib diisi.',
        'app_key_size' => 'Kunci aplikasi harus tepat 32 karakter.',
        'app_secret_required' => 'Rahasia aplikasi wajib diisi.',
    ],

];
