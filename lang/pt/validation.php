<?php

return [

    'accepted' => 'O :attribute deve ser aceito.',
    'accepted_if' => 'O :attribute deve ser aceito quando :other for :value.',
    'active_url' => 'O :attribute não é uma URL válida.',
    'after' => 'O :attribute deve ser uma data após :date.',
    'after_or_equal' => 'O :attribute deve ser uma data após ou igual a :date.',
    'alpha' => 'O :attribute deve conter apenas letras.',
    'alpha_dash' => 'O :attribute deve conter apenas letras, números, traços e sublinhados.',
    'alpha_num' => 'O :attribute deve conter apenas letras e números.',
    'array' => 'O :attribute deve ser um array.',
    'before' => 'O :attribute deve ser uma data antes de :date.',
    'before_or_equal' => 'O :attribute deve ser uma data antes ou igual a :date.',
    'between' => [
        'array' => 'O :attribute deve ter entre :min e :max itens.',
        'file' => 'O :attribute deve ter entre :min e :max kilobytes.',
        'numeric' => 'O :attribute deve estar entre :min e :max.',
        'string' => 'O :attribute deve ter entre :min e :max caracteres.',
    ],
    'boolean' => 'O campo :attribute deve ser verdadeiro ou falso.',
    'confirmed' => 'A confirmação de :attribute não corresponde.',
    'current_password' => 'A senha está incorreta.',
    'date' => 'O :attribute não é uma data válida.',
    'date_equals' => 'O :attribute deve ser uma data igual a :date.',
    'date_format' => 'O :attribute não corresponde ao formato :format.',
    'declined' => 'O :attribute deve ser recusado.',
    'declined_if' => 'O :attribute deve ser recusado quando :other for :value.',
    'different' => 'O :attribute e :other devem ser diferentes.',
    'digits' => 'O :attribute deve ter :digits dígitos.',
    'digits_between' => 'O :attribute deve ter entre :min e :max dígitos.',
    'dimensions' => 'O :attribute tem dimensões de imagem inválidas.',
    'distinct' => 'O campo :attribute tem um valor duplicado.',
    'doesnt_start_with' => 'O :attribute não pode começar com um dos seguintes: :values.',
    'email' => 'O :attribute deve ser um endereço de e-mail válido.',
    'ends_with' => 'O :attribute deve terminar com um dos seguintes: :values.',
    'enum' => 'O :attribute selecionado é inválido.',
    'exists' => 'O :attribute selecionado é inválido.',
    'file' => 'O :attribute deve ser um arquivo.',
    'filled' => 'O campo :attribute deve ter um valor.',
    'gt' => [
        'array' => 'O :attribute deve ter mais de :value itens.',
        'file' => 'O :attribute deve ser maior que :value kilobytes.',
        'numeric' => 'O :attribute deve ser maior que :value.',
        'string' => 'O :attribute deve ter mais de :value caracteres.',
    ],
    'gte' => [
        'array' => 'O :attribute deve ter :value itens ou mais.',
        'file' => 'O :attribute deve ser maior ou igual a :value kilobytes.',
        'numeric' => 'O :attribute deve ser maior ou igual a :value.',
        'string' => 'O :attribute deve ter :value caracteres ou mais.',
    ],
    'image' => 'O :attribute deve ser uma imagem.',
    'in' => 'O :attribute selecionado é inválido.',
    'in_array' => 'O campo :attribute não existe em :other.',
    'integer' => 'O :attribute deve ser um número inteiro.',
    'ip' => 'O :attribute deve ser um endereço IP válido.',
    'ipv4' => 'O :attribute deve ser um endereço IPv4 válido.',
    'ipv6' => 'O :attribute deve ser um endereço IPv6 válido.',
    'json' => 'O :attribute deve ser uma string JSON válida.',
    'lt' => [
        'array' => 'O :attribute deve ter menos de :value itens.',
        'file' => 'O :attribute deve ser menor que :value kilobytes.',
        'numeric' => 'O :attribute deve ser menor que :value.',
        'string' => 'O :attribute deve ter menos de :value caracteres.',
    ],
    'lte' => [
        'array' => 'O :attribute não pode ter mais de :value itens.',
        'file' => 'O :attribute não pode ser maior que :value kilobytes.',
        'numeric' => 'O :attribute não pode ser maior que :value.',
        'string' => 'O :attribute não pode ter mais de :value caracteres.',
    ],
    'max' => [
        'array' => 'O :attribute não pode ter mais de :max itens.',
        'file' => 'O :attribute não pode ser maior que :max kilobytes.',
        'numeric' => 'O :attribute não pode ser maior que :max.',
        'string' => 'O :attribute não pode ter mais de :max caracteres.',
    ],
    'mimes' => 'O :attribute deve ser um arquivo do tipo: :values.',
    'mimetypes' => 'O :attribute deve ser um arquivo do tipo: :values.',
    'min' => [
        'array' => 'O :attribute deve ter pelo menos :min itens.',
        'file' => 'O :attribute deve ter pelo menos :min kilobytes.',
        'numeric' => 'O :attribute deve ser pelo menos :min.',
        'string' => 'O :attribute deve ter pelo menos :min caracteres.',
    ],
    'not_in' => 'O :attribute selecionado é inválido.',
    'numeric' => 'O :attribute deve ser um número.',
    'password' => [
        'letters' => 'O :attribute deve conter pelo menos uma letra.',
        'mixed' => 'O :attribute deve conter pelo menos uma letra maiúscula e uma letra minúscula.',
        'numbers' => 'O :attribute deve conter pelo menos um número.',
        'symbols' => 'O :attribute deve conter pelo menos um símbolo.',
        'uncompromised' => 'O :attribute fornecido foi comprometido. Escolha outro :attribute.',
    ],
    'regex' => 'O formato de :attribute é inválido.',
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O :attribute deve ser uma string.',
    'url' => 'O :attribute deve ser uma URL válida.',
    'uuid' => 'O :attribute deve ser um UUID válido.',
    'attributes' => [],
    'publish_date_required' => 'A data de publicação é obrigatória',
    'price_numeric_value' => 'O preço deve ser um valor numérico',
    'quantity_integer_value' => 'A quantidade deve ser um valor inteiro',
    'order_has_Expired' => 'O pedido expirou',
    'expired' => 'Expirado',
    'eid_required' => 'O campo EID é obrigatório.',
    'eid_string' => 'O EID deve ser uma string.',
    'otp_required' => 'O campo OTP é obrigatório.',
    'amt_required' => 'O campo de valor é obrigatório',
    'amt_numeric' => 'O valor deve ser um número',
    'payment_date_required' => 'A data de pagamento é obrigatória.',
    'payment_method_required' => 'O método de pagamento é obrigatório.',
    'total_amount_required' => 'O valor total é obrigatório.',
    'total_amount_numeric' => 'O valor total deve ser um valor numérico.',
    'invoice_link_required' => 'Por favor, vincule o valor com pelo menos uma fatura.',
    /*
       Mensagens de validação personalizada de arquivo de solicitação
       */

    'settings_form' => [
        'company' => [
            'required' => 'O campo empresa é obrigatório.',
        ],
        'website' => [
            'url' => 'O website deve ser uma URL válida.',
        ],
        'phone' => [
            'regex' => 'O formato do número de telefone é inválido.',
        ],
        'address' => [
            'required' => 'O campo endereço é obrigatório.',
            'max' => 'O endereço não pode ser maior que 300 caracteres.',
        ],
        'logo' => [
            'mimes' => 'O logo deve ser um arquivo PNG.',
        ],
        'driver' => [
            'required' => 'O campo motorista é obrigatório.',
        ],
        'port' => [
            'integer' => 'A porta deve ser um número inteiro.',
        ],
        'email' => [
            'required' => 'O campo e-mail é obrigatório.',
            'email' => 'O e-mail deve ser um endereço de e-mail válido.',
        ],
        'password' => [
            'required' => 'O campo senha é obrigatório.',
        ],
        'error_email' => [
            'email' => 'O e-mail de erro deve ser um endereço de e-mail válido.',
        ],
    ],

    'settings_forms' => [
        'company' => [
            'required' => 'O nome da empresa é obrigatório.',
            'max' => 'O nome da empresa não pode exceder 50 caracteres.',
        ],
        'company_email' => [
            'required' => 'O e-mail da empresa é obrigatório.',
            'email' => 'O e-mail da empresa deve ser um endereço de e-mail válido.',
        ],
        'title' => [
            'max' => 'O título não pode exceder 50 caracteres.',
        ],
        'website' => [
            'required' => 'A URL do website é obrigatória.',
            'url' => 'O website deve ser uma URL válida.',
            'regex' => 'O formato do website é inválido.',
        ],
        'phone' => [
            'required' => 'O número de telefone é obrigatório.',
        ],
        'address' => [
            'required' => 'O endereço é obrigatório.',
        ],
        'state' => [
            'required' => 'O estado é obrigatório.',
        ],
        'country' => [
            'required' => 'O país é obrigatório.',
        ],
        'gstin' => [
            'max' => 'O GSTIN não pode exceder 15 caracteres.',
        ],
        'default_currency' => [
            'required' => 'A moeda padrão é obrigatória.',
        ],
        'admin_logo' => [
            'mimes' => 'O logo do administrador deve ser um arquivo dos tipos: jpeg, png, jpg.',
            'max' => 'O logo do administrador não pode ser maior que 2MB.',
        ],
        'fav_icon' => [
            'mimes' => 'O ícone favorito deve ser um arquivo dos tipos: jpeg, png, jpg.',
            'max' => 'O ícone favorito não pode ser maior que 2MB.',
        ],
        'logo' => [
            'mimes' => 'O logo deve ser um arquivo dos tipos: jpeg, png, jpg.',
            'max' => 'O logo não pode ser maior que 2MB.',
        ],
    ],

    'social_media_form' => [
        'name' => [
            'required' => 'O campo nome é obrigatório.',
            'unique' => 'Este nome já existe.',
            'max' => 'O nome não pode exceder 50 caracteres.',
        ],
        'link' => [
            'required' => 'O campo link é obrigatório.',
            'url' => 'O link deve ser uma URL válida.',
            'regex' => 'O formato do link é inválido.',
        ],
    ],

    'custom' => [
        'password' => [
            'required_if' => 'O campo senha é obrigatório para o driver de e-mail selecionado.',
        ],
        'port' => [
            'required_if' => 'O campo porta é obrigatório para SMTP.',
        ],
        'encryption' => [
            'required_if' => 'O campo criptografia é obrigatório para SMTP.',
        ],
        'host' => [
            'required_if' => 'O campo host é obrigatório para SMTP.',
        ],
        'secret' => [
            'required_if' => 'O campo segredo é obrigatório para o driver de e-mail selecionado.',
        ],
        'domain' => [
            'required_if' => 'O campo domínio é obrigatório para Mailgun.',
        ],
        'key' => [
            'required_if' => 'O campo chave é obrigatório para SES.',
        ],
        'region' => [
            'required_if' => 'O campo região é obrigatório para SES.',
        ],
        'email' => [
            'required_if' => 'O campo e-mail é obrigatório para o driver de e-mail selecionado.',
            'required' => 'O campo e-mail é obrigatório.',
            'email' => 'Por favor, insira um endereço de e-mail válido.',
            'not_matching' => 'O domínio do e-mail deve corresponder ao domínio do site atual.',
        ],
        'driver' => [
            'required' => 'O campo driver é obrigatório.',
        ],
    ],

    'customer_form' => [
        'first_name' => [
            'required' => 'O campo primeiro nome é obrigatório.',
        ],
        'last_name' => [
            'required' => 'O campo sobrenome é obrigatório.',
        ],
        'company' => [
            'required' => 'O campo empresa é obrigatório.',
        ],
        'mobile' => [
            'regex' => 'O formato do número de celular é inválido.',
        ],
        'address' => [
            'required' => 'O campo endereço é obrigatório.',
        ],
        'zip' => [
            'required' => 'O campo código postal é obrigatório.',
            'min' => 'O código postal deve ter pelo menos 5 dígitos.',
            'numeric' => 'O código postal deve ser numérico.',
        ],
        'email' => [
            'required' => 'O campo e-mail é obrigatório.',
            'email' => 'O e-mail deve ser um endereço de e-mail válido.',
            'unique' => 'Este e-mail já foi registrado.',
        ],
    ],
    'contact_request' => [
        'conName' => 'O campo nome é obrigatório.',
        'email' => 'O campo e-mail é obrigatório.',
        'conmessage' => 'O campo mensagem é obrigatório.',
        'Mobile' => 'O campo celular é obrigatório.',
        'country_code' => 'O campo código do país é obrigatório.',
        'demoname' => 'O campo nome é obrigatório.',
        'demomessage' => 'O campo mensagem é obrigatório.',
        'demoemail' => 'O campo e-mail é obrigatório.',
        'congg-recaptcha-response-1.required' => 'Falha na verificação do robô. Tente novamente.',
        'demo-recaptcha-response-1.required' => 'Falha na verificação do robô. Tente novamente.',
    ],

    'frontend_pages' => [
        'name' => [
            'required' => 'O campo nome é obrigatório.',
            'unique' => 'Este nome já existe.',
            'max' => 'O nome não pode exceder 20 caracteres.',
            'regex' => 'O nome pode conter apenas letras e espaços.',
        ],
        'publish' => [
            'required' => 'O campo publicação é obrigatório.',
        ],
        'slug' => [
            'required' => 'O campo slug é obrigatório.',
        ],
        'url' => [
            'required' => 'O campo URL é obrigatório.',
            'url' => 'A URL deve ser um link válido.',
            'regex' => 'O formato da URL é inválido.',
        ],
        'content' => [
            'required' => 'O campo conteúdo é obrigatório.',
        ],
        'created_at' => [
            'required' => 'O campo data de criação é obrigatório.',
        ],
    ],

    'order_form' => [
        'client' => [
            'required' => 'O campo cliente é obrigatório.',
        ],
        'payment_method' => [
            'required' => 'O campo método de pagamento é obrigatório.',
        ],
        'promotion_code' => [
            'required' => 'O campo código de promoção é obrigatório.',
        ],
        'order_status' => [
            'required' => 'O campo status do pedido é obrigatório.',
        ],
        'product' => [
            'required' => 'O campo produto é obrigatório.',
        ],
        'subscription' => [
            'required' => 'O campo assinatura é obrigatório.',
        ],
        'price_override' => [
            'numeric' => 'O preço substituído deve ser um número.',
        ],
        'qty' => [
            'integer' => 'A quantidade deve ser um número inteiro.',
        ],
    ],

    'coupon_form' => [
        'code' => [
            'required' => 'O campo código do cupom é obrigatório.',
            'string' => 'O código do cupom deve ser uma string.',
            'max' => 'O código do cupom não pode exceder 255 caracteres.',
        ],
        'type' => [
            'required' => 'O campo tipo é obrigatório.',
            'in' => 'Tipo inválido. Os valores permitidos são: percentage, other_type.',
        ],
        'applied' => [
            'required' => 'O campo aplicado para um produto é obrigatório.',
            'date' => 'O campo aplicado para um produto deve ser uma data válida.',
        ],
        'uses' => [
            'required' => 'O campo usos é obrigatório.',
            'numeric' => 'O campo usos deve ser um número.',
            'min' => 'O campo usos deve ser no mínimo :min.',
        ],
        'start' => [
            'required' => 'O campo início é obrigatório.',
            'date' => 'O campo início deve ser uma data válida.',
        ],
        'expiry' => [
            'required' => 'O campo expiração é obrigatório.',
            'date' => 'O campo expiração deve ser uma data válida.',
            'after' => 'A data de expiração deve ser após a data de início.',
        ],
        'value' => [
            'required' => 'O campo valor do desconto é obrigatório.',
            'numeric' => 'O campo valor do desconto deve ser um número.',
            'between' => 'O campo valor do desconto deve estar entre :min e :max se o tipo for percentage.',
        ],
    ],

    'tax_form' => [
        'name' => [
            'required' => 'O campo nome é obrigatório.',
        ],
        'rate' => [
            'required' => 'O campo taxa é obrigatório.',
            'numeric' => 'A taxa deve ser um número.',
        ],
        'level' => [
            'required' => 'O campo nível é obrigatório.',
            'integer' => 'O nível deve ser um número inteiro.',
        ],
        'country' => [
            'required' => 'O campo país é obrigatório.',
            // 'exists' => 'O país selecionado é inválido.',
        ],
        'state' => [
            'required' => 'O campo estado é obrigatório.',
            // 'exists' => 'O estado selecionado é inválido.',
        ],
    ],
//Product
    'subscription_form' => [
        'name' => [
            'required' => 'O campo nome é obrigatório.',
        ],
        'subscription' => [
            'required' => 'O campo assinatura é obrigatório.',
        ],
        'regular_price' => [
            'required' => 'O campo preço regular é obrigatório.',
            'numeric' => 'O preço regular deve ser um número.',
        ],
        'selling_price' => [
            'required' => 'O campo preço de venda é obrigatório.',
            'numeric' => 'O preço de venda deve ser um número.',
        ],
        'products' => [
            'required' => 'O campo produtos é obrigatório.',
        ],
    ],

    'bundle' => [
        'name' => [
            'required' => 'O campo nome é obrigatório.',
        ],
        'items' => [
            'required' => 'Cada item é obrigatório.',
        ],
    ],

    'group' => [
        'name' => [
            'required' => 'O nome é obrigatório',
        ],
        'features' => [
            'name' => [
                'required' => 'Todos os campos de características são obrigatórios',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => 'O preço é obrigatório',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => 'O valor é obrigatório',
            ],
        ],
        'type' => [
            'required_with' => 'O tipo é obrigatório',
        ],
        'title' => [
            'required_with' => 'O título é obrigatório',
        ],
    ],

    'product' => [
        'name' => [
            'required' => 'O campo nome é obrigatório.',
        ],
        'type' => [
            'required' => 'O campo tipo é obrigatório.',
        ],
        'group' => [
            'required' => 'O campo grupo é obrigatório.',
        ],
        'subscription' => [
            'required' => 'O campo assinatura é obrigatório.',
        ],
        'currency' => [
            'required' => 'O campo moeda é obrigatório.',
        ],
        'file' => [
            'required_without_all' => 'O campo arquivo é obrigatório se nenhum dos campos github_owner ou github_repository forem fornecidos.',
            'mimes' => 'O arquivo deve ser um arquivo zip.',
        ],
        'image' => [
            'required_without_all' => 'O campo imagem é obrigatório se nenhum dos campos github_owner ou github_repository forem fornecidos.',
            'mimes' => 'A imagem deve ser um arquivo PNG.',
        ],
        'github_owner' => [
            'required_without_all' => 'O campo GitHub owner é obrigatório se nenhum dos campos arquivo ou imagem forem fornecidos.',
        ],
        'github_repository' => [
            'required_without_all' => 'O campo GitHub repository é obrigatório se nenhum dos campos arquivo ou imagem forem fornecidos.',
            'required_if' => 'O campo GitHub repository é obrigatório se o tipo for 2.',
        ],
    ],

//User
    'users' => [
        'first_name' => [
            'required' => 'O campo nome é obrigatório.',
        ],
        'last_name' => [
            'required' => 'O campo sobrenome é obrigatório.',
        ],
        'company' => [
            'required' => 'O campo empresa é obrigatório.',
        ],
        'email' => [
            'required' => 'O campo e-mail é obrigatório.',
            'email' => 'O e-mail deve ser um endereço de e-mail válido.',
            'unique' => 'O e-mail já foi registrado.',
        ],
        'address' => [
            'required' => 'O campo endereço é obrigatório.',
        ],
        'mobile' => [
            'required' => 'O campo celular é obrigatório.',
        ],
        'country' => [
            'required' => 'O campo país é obrigatório.',
            'exists' => 'O país selecionado é inválido.',
        ],
        'state' => [
            'required_if' => 'O campo estado é obrigatório quando o país for a Índia.',
        ],
        'timezone_id' => [
            'required' => 'O campo fuso horário é obrigatório.',
        ],
        'user_name' => [
            'required' => 'O campo nome de usuário é obrigatório.',
            'unique' => 'O nome de usuário já foi registrado.',
        ],
        'zip' => [
            'regex' => 'O campo estado é obrigatório quando o país for a Índia.',
        ]
    ],

    'profile_form' => [
        'first_name' => [
            'required' => 'O nome é obrigatório.',
            'min' => 'O nome deve ter pelo menos :min caracteres.',
            'max' => 'O nome não pode ter mais de :max caracteres.',
        ],
        'last_name' => [
            'required' => 'O sobrenome é obrigatório.',
            'max' => 'O sobrenome não pode ter mais de :max caracteres.',
        ],
        'company' => [
            'required' => 'O nome da empresa é obrigatório.',
            'max' => 'O nome da empresa não pode ter mais de :max caracteres.',
        ],
        'email' => [
            'required' => 'O e-mail é obrigatório.',
            'email' => 'Digite um endereço de e-mail válido.',
            'unique' => 'Este endereço de e-mail já foi registrado. Escolha um e-mail diferente.',
        ],
        'mobile' => [
            'required' => 'O número de celular é obrigatório.',
            'regex' => 'Digite um número de celular válido.',
            'min' => 'O número de celular deve ter pelo menos :min caracteres.',
            'max' => 'O número de celular não pode ter mais de :max caracteres.',
        ],
        'address' => [
            'required' => 'O endereço é obrigatório.',
        ],
        'user_name' => [
            'required' => 'O nome de usuário é obrigatório.',
            'unique' => 'Este nome de usuário já foi registrado.',
        ],
        'timezone_id' => [
            'required' => 'O fuso horário é obrigatório.',
        ],
        'country' => [
            'required' => 'O país é obrigatório.',
            'exists' => 'O país selecionado é inválido.',
        ],
        'state' => [
            'required_if' => 'O campo estado é obrigatório quando o país for a Índia.',
        ],
    ],
//Password form
    'old_password' => [
        'required' => 'O campo senha antiga é obrigatório.',
        'min' => 'A senha antiga deve ter pelo menos :min caracteres.',
    ],
    'new_password' => [
        'required' => 'O campo nova senha é obrigatório.',
        'different' => 'A nova senha deve ser diferente da senha antiga.',
    ],
    'confirm_password' => [
        'required' => 'O campo confirmar senha é obrigatório.',
        'same' => 'A confirmação da senha deve coincidir com a nova senha.',
    ],
    'terms' => [
        'required' => 'Você deve aceitar os termos.',
    ],
    'password' => [
        'required' => 'O campo senha é obrigatório.',
    ],
    'password_confirmation' => [
        'required' => 'A confirmação da senha é obrigatória.',
        'same' => 'As senhas não coincidem.',
    ],
    'mobile_code' => [
        'required' => 'Digite o código do país (celular).',
    ],

//Invoice form
    'invoice' => [
        'user' => [
            'required' => 'O campo cliente é obrigatório.',
        ],
        'date' => [
            'required' => 'O campo data é obrigatório.',
            'date' => 'A data deve ser uma data válida.',
        ],
        'domain' => [
            'regex' => 'O formato do domínio é inválido.',
        ],
        'plan' => [
            'required_if' => 'O campo plano é obrigatório.',
        ],
        'price' => [
            'required' => 'O campo preço é obrigatório.',
        ],
        'product' => [
            'required' => 'O campo produto é obrigatório.',
        ],
    ],

//LocalizedLicense form
    'domain_form' => [
        'domain' => [
            'required' => 'O campo domínio é obrigatório.',
            'url' => 'O domínio deve ser uma URL válida.',
        ],
    ],

//Product Renewal form
    'product_renewal' => [
        'domain' => [
            'required' => 'O campo domínio é obrigatório.',
            'no_http' => 'O domínio não deve conter "http" ou "https".',
        ],
    ],

//Language form
    'language' => [
        'required' => 'O campo idioma é obrigatório.',
        'invalid' => 'O idioma selecionado é inválido.',
    ],

//UpdateSroragePathRequest form
    'storage_path' => [
        'disk' => [
            'required' => 'O campo disco de armazenamento é obrigatório.',
            'string' => 'O disco deve ser uma string.',
        ],
        'path' => [
            'string' => 'O caminho deve ser uma string.',
            'nullable' => 'O campo caminho é opcional.',
        ],
    ],

//ValidateSecretRequest form
    'validate_secret' => [
        'totp' => [
            'required' => 'Por favor, insira o código.',
            'digits' => 'Por favor, insira um código válido de 6 dígitos.',
        ],
    ],

//VerifyOtp form
    'verify_email' => [
        'required' => 'O campo e-mail é obrigatório.',
        'email' => 'O e-mail deve ser um endereço de e-mail válido.',
        'verify_email' => 'A verificação do e-mail falhou.', // Mensagem personalizada para verify_email
    ],

    'verify_country_code' => [
        'required' => 'O código do país é obrigatório.',
        'numeric' => 'O código do país deve ser um número válido.',
        'verify_country_code' => 'A verificação do código do país falhou.', // Mensagem personalizada para verify_country_code
    ],

    'verify_number' => [
        'required' => 'O número é obrigatório.',
        'numeric' => 'O número deve ser um número válido.',
        'verify_number' => 'A verificação do número falhou.', // Mensagem personalizada para verify_number
    ],

    'password_otp' => [
        'required' => 'O campo senha é obrigatório.',
        'password' => 'A senha está incorreta.',
        'invalid' => 'Senha inválida.',
    ],

//AuthController file
    'auth_controller' => [
        'name_required' => 'O nome é obrigatório.',
        'name_max' => 'O nome não pode ter mais de 255 caracteres.',

        'email_required' => 'O e-mail é obrigatório.',
        'email_email' => 'Digite um endereço de e-mail válido.',
        'email_max' => 'O e-mail não pode ter mais de 255 caracteres.',
        'email_unique' => 'Este e-mail já foi registrado.',

        'password_required' => 'A senha é obrigatória.',
        'password_confirmed' => 'A confirmação da senha não corresponde.',
        'password_min' => 'A senha deve ter pelo menos 6 caracteres.',
    ],

    'resend_otp' => [
        'eid_required' => 'O campo EID é obrigatório.',
        'eid_string' => 'O EID deve ser uma string.',
        'type_required' => 'O campo tipo é obrigatório.',
        'type_string' => 'O tipo deve ser uma string.',
        'type_in' => 'O tipo selecionado é inválido.',
    ],

    'verify_otp' => [
        'eid_required' => 'O ID do funcionário é obrigatório.',
        'eid_string' => 'O ID do funcionário deve ser uma string.',
        'otp_required' => 'O OTP é obrigatório.',
        'otp_size' => 'O OTP deve ter exatamente 6 caracteres.',
        'recaptcha_required' => 'Por favor, complete o CAPTCHA.',
        'recaptcha_size' => 'A resposta do CAPTCHA é inválida.',
    ],

    'company_validation' => [
        'company_required' => 'O nome da empresa é obrigatório.',
        'company_string' => 'A empresa deve ser texto.',
        'address_required' => 'O endereço é obrigatório.',
        'address_string' => 'O endereço deve ser texto.',
    ],

    'token_validation' => [
        'token_required' => 'O token é obrigatório.',
        'password_required' => 'O campo senha é obrigatório.',
        'password_confirmed' => 'A confirmação da senha não corresponde.',
    ],

    'custom_email' => [
        'required' => 'O campo e-mail é obrigatório.',
        'email' => 'Digite um endereço de e-mail válido.',
        'exists' => 'Este e-mail não está registrado conosco.',
    ],

    'newsletterEmail' => [
        'required' => 'O e-mail da newsletter é obrigatório.',
        'email' => 'Digite um e-mail válido para a newsletter.',
    ],

    'widget' => [
        'name_required' => 'O nome é obrigatório.',
        'name_max' => 'O nome não pode ter mais de 50 caracteres.',
        'publish_required' => 'O status de publicação é obrigatório.',
        'type_required' => 'O tipo é obrigatório.',
        'type_unique' => 'Este tipo já existe.',
    ],

    'payment' => [
        'payment_date_required' => 'A data de pagamento é obrigatória.',
        'payment_method_required' => 'O método de pagamento é obrigatório.',
        'amount_required' => 'O valor é obrigatório.',
    ],

    'custom_date' => [
        'date_required' => 'O campo data é obrigatório.',
        'total_required' => 'O campo total é obrigatório.',
        'status_required' => 'O campo status é obrigatório.',
    ],

    'plan_renewal' => [
        'plan_required' => 'O campo plano é obrigatório.',
        'payment_method_required' => 'O campo método de pagamento é obrigatório.',
        'cost_required' => 'O campo custo é obrigatório.',
        'code_not_valid' => 'O código promocional não é válido.',
    ],

    'rate' => [
        'required' => 'A taxa é obrigatória.',
        'numeric' => 'A taxa deve ser um número.',
    ],

    'product_validate' => [
        'producttitle_required' => 'O título do produto é obrigatório.',
        'version_required' => 'A versão é obrigatória.',
        'filename_required' => 'Por favor, envie um ficheiro.',
        'dependencies_required' => 'O campo de dependências é obrigatório.',
    ],
    'product_sku_unique' => 'O SKU do produto deve ser único.',
    'product_name_unique' => 'O nome deve ser único.',
    'product_show_agent_required' => 'Selecione sua preferência da página do carrinho.',
    'product_controller' => [
        'name_required' => 'O nome do produto é obrigatório.',
        'name_unique' => 'O nome deve ser único.',
        'type_required' => 'O tipo do produto é obrigatório.',
        'description_required' => 'A descrição do produto é obrigatória.',
        'product_description_required' => 'A descrição detalhada do produto é obrigatória.',
        'image_mimes' => 'A imagem deve ser um ficheiro do tipo: jpeg, png, jpg.',
        'image_max' => 'A imagem não pode ter mais de 2048 kilobytes.',
        'product_sku_required' => 'O SKU do produto é obrigatório.',
        'group_required' => 'O grupo do produto é obrigatório.',
        'show_agent_required' => 'Selecione sua preferência da página do carrinho.',
    ],
    'current_domain_required' => 'O domínio atual é obrigatório.',
    'new_domain_required' => 'O novo domínio é obrigatório.',
    'special_characters_not_allowed' => 'Caracteres especiais não são permitidos no nome de domínio.',
    'orderno_required' => 'O número do pedido é obrigatório.',
    'cloud_central_domain_required' => 'O domínio central da nuvem é obrigatório.',
    'cloud_cname_required' => 'O CNAME da nuvem é obrigatório.',
    'cloud_tenant' => [
        'cloud_top_message_required' => 'A mensagem superior da nuvem é obrigatória.',
        'cloud_label_field_required' => 'O campo de etiqueta da nuvem é obrigatório.',
        'cloud_label_radio_required' => 'A opção de etiqueta da nuvem é obrigatória.',
        'cloud_product_required' => 'O produto da nuvem é obrigatório.',
        'cloud_free_plan_required' => 'O plano gratuito da nuvem é obrigatório.',
        'cloud_product_key_required' => 'A chave do produto da nuvem é obrigatória.',
    ],
    'reg_till_after' => 'A data de fim do registo deve ser posterior à data de início do registo.',
    'extend_product' => [
        'title_required' => 'O campo título é obrigatório.',
        'version_required' => 'O campo versão é obrigatório.',
        'dependencies_required' => 'O campo dependências é obrigatório.',
    ],
    'please_enter_recovery_code' => 'Por favor, insira o código de recuperação.',
    'social_login' => [
        'client_id_required' => 'O ID do cliente é obrigatório para Google, Github ou Linkedin.',
        'client_secret_required' => 'O segredo do cliente é obrigatório para Google, Github ou Linkedin.',
        'api_key_required' => 'A chave da API é obrigatória para o Twitter.',
        'api_secret_required' => 'O segredo da API é obrigatório para o Twitter.',
        'redirect_url_required' => 'O URL de redirecionamento é obrigatório.',
    ],
    'thirdparty_api' => [
        'app_name_required' => 'O nome do aplicativo é obrigatório.',
        'app_key_required' => 'A chave do aplicativo é obrigatória.',
        'app_key_size' => 'A chave do aplicativo deve ter exatamente 32 caracteres.',
        'app_secret_required' => 'O segredo do aplicativo é obrigatório.',
    ],


];
