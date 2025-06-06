<?php

return [

    'accepted' => 'Le :attribute doit être accepté.',
    'accepted_if' => 'Le :attribute doit être accepté lorsque :other est :value.',
    'active_url' => 'Le :attribute n\'est pas une URL valide.',
    'after' => 'Le :attribute doit être une date après :date.',
    'after_or_equal' => 'Le :attribute doit être une date après ou égale à :date.',
    'alpha' => 'Le :attribute ne peut contenir que des lettres.',
    'alpha_dash' => 'Le :attribute ne peut contenir que des lettres, des chiffres, des tirets et des underscores.',
    'alpha_num' => 'Le :attribute ne peut contenir que des lettres et des chiffres.',
    'array' => 'Le :attribute doit être un tableau.',
    'before' => 'Le :attribute doit être une date avant :date.',
    'before_or_equal' => 'Le :attribute doit être une date avant ou égale à :date.',
    'between' => [
        'array' => 'Le :attribute doit avoir entre :min et :max éléments.',
        'file' => 'Le :attribute doit être entre :min et :max kilo-octets.',
        'numeric' => 'Le :attribute doit être entre :min et :max.',
        'string' => 'Le :attribute doit contenir entre :min et :max caractères.',
    ],
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation du :attribute ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'date' => 'Le :attribute n\'est pas une date valide.',
    'date_equals' => 'Le :attribute doit être une date égale à :date.',
    'date_format' => 'Le :attribute ne correspond pas au format :format.',
    'declined' => 'Le :attribute doit être refusé.',
    'declined_if' => 'Le :attribute doit être refusé lorsque :other est :value.',
    'different' => 'Le :attribute et :other doivent être différents.',
    'digits' => 'Le :attribute doit être composé de :digits chiffres.',
    'digits_between' => 'Le :attribute doit être composé entre :min et :max chiffres.',
    'dimensions' => 'Le :attribute a des dimensions d\'image invalides.',
    'distinct' => 'Le champ :attribute contient une valeur en double.',
    'doesnt_start_with' => 'Le :attribute ne peut pas commencer par l\'un des suivants : :values.',
    'email' => 'Le :attribute doit être une adresse e-mail valide.',
    'ends_with' => 'Le :attribute doit se terminer par l\'un des suivants : :values.',
    'enum' => 'Le :attribute sélectionné est invalide.',
    'exists' => 'Le :attribute sélectionné est invalide.',
    'file' => 'Le :attribute doit être un fichier.',
    'filled' => 'Le champ :attribute doit avoir une valeur.',
    'gt' => [
        'array' => 'Le :attribute doit avoir plus de :value éléments.',
        'file' => 'Le :attribute doit être supérieur à :value kilo-octets.',
        'numeric' => 'Le :attribute doit être supérieur à :value.',
        'string' => 'Le :attribute doit contenir plus de :value caractères.',
    ],
    'gte' => [
        'array' => 'Le :attribute doit avoir :value éléments ou plus.',
        'file' => 'Le :attribute doit être supérieur ou égal à :value kilo-octets.',
        'numeric' => 'Le :attribute doit être supérieur ou égal à :value.',
        'string' => 'Le :attribute doit contenir au moins :value caractères.',
    ],
    'image' => 'Le :attribute doit être une image.',
    'in' => 'Le :attribute sélectionné est invalide.',
    'in_array' => 'Le champ :attribute n\'existe pas dans :other.',
    'integer' => 'Le :attribute doit être un entier.',
    'ip' => 'Le :attribute doit être une adresse IP valide.',
    'ipv4' => 'Le :attribute doit être une adresse IPv4 valide.',
    'ipv6' => 'Le :attribute doit être une adresse IPv6 valide.',
    'json' => 'Le :attribute doit être une chaîne JSON valide.',
    'lt' => [
        'array' => 'Le :attribute doit avoir moins de :value éléments.',
        'file' => 'Le :attribute doit être inférieur à :value kilo-octets.',
        'numeric' => 'Le :attribute doit être inférieur à :value.',
        'string' => 'Le :attribute doit contenir moins de :value caractères.',
    ],
    'lte' => [
        'array' => 'Le :attribute ne doit pas avoir plus de :value éléments.',
        'file' => 'Le :attribute doit être inférieur ou égal à :value kilo-octets.',
        'numeric' => 'Le :attribute doit être inférieur ou égal à :value.',
        'string' => 'Le :attribute doit contenir au maximum :value caractères.',
    ],
    'mac_address' => 'Le :attribute doit être une adresse MAC valide.',
    'max' => [
        'array' => 'Le :attribute ne doit pas avoir plus de :max éléments.',
        'file' => 'Le :attribute ne doit pas être supérieur à :max kilo-octets.',
        'numeric' => 'Le :attribute ne doit pas être supérieur à :max.',
        'string' => 'Le :attribute ne doit pas contenir plus de :max caractères.',
    ],
    'mimes' => 'Le :attribute doit être un fichier de type :values.',
    'mimetypes' => 'Le :attribute doit être un fichier de type :values.',
    'min' => [
        'array' => 'Le :attribute doit avoir au moins :min éléments.',
        'file' => 'Le :attribute doit être d\'au moins :min kilo-octets.',
        'numeric' => 'Le :attribute doit être d\'au moins :min.',
        'string' => 'Le :attribute doit contenir au moins :min caractères.',
    ],
    'multiple_of' => 'Le :attribute doit être un multiple de :value.',
    'not_in' => 'Le :attribute sélectionné est invalide.',
    'not_regex' => 'Le format du :attribute est invalide.',
    'numeric' => 'Le :attribute doit être un nombre.',
    'password' => [
        'letters' => 'Le :attribute doit contenir au moins une lettre.',
        'mixed' => 'Le :attribute doit contenir au moins une majuscule et une minuscule.',
        'numbers' => 'Le :attribute doit contenir au moins un chiffre.',
        'symbols' => 'Le :attribute doit contenir au moins un symbole.',
        'uncompromised' => 'Le :attribute donné est apparu dans une fuite de données. Veuillez choisir un autre :attribute.',
    ],
    'present' => 'Le champ :attribute doit être présent.',
    'prohibited' => 'Le champ :attribute est interdit.',
    'prohibited_if' => 'Le champ :attribute est interdit lorsque :other est :value.',
    'prohibited_unless' => 'Le champ :attribute est interdit à moins que :other ne soit dans :values.',
    'prohibits' => 'Le champ :attribute interdit que :other soit présent.',
    'regex' => 'Le format du :attribute est invalide.',
    'required' => 'Le champ :attribute est requis.',
    'required_array_keys' => 'Le champ :attribute doit contenir des éléments pour :values.',
    'required_if' => 'Le champ :attribute est requis lorsque :other est :value.',
    'required_unless' => 'Le champ :attribute est requis à moins que :other ne soit dans :values.',
    'required_with' => 'Le champ :attribute est requis lorsque :values est présent.',
    'required_with_all' => 'Le champ :attribute est requis lorsque :values sont présents.',
    'required_without' => 'Le champ :attribute est requis lorsque :values n\'est pas présent.',
    'required_without_all' => 'Le champ :attribute est requis lorsque aucun des :values n\'est présent.',
    'same' => 'Le :attribute et :other doivent correspondre.',
    'size' => [
        'array' => 'Le :attribute doit contenir :size éléments.',
        'file' => 'Le :attribute doit être de :size kilo-octets.',
        'numeric' => 'Le :attribute doit être :size.',
        'string' => 'Le :attribute doit contenir :size caractères.',
    ],
    'starts_with' => 'Le :attribute doit commencer par l\'un des suivants : :values.',
    'string' => 'Le :attribute doit être une chaîne.',
    'timezone' => 'Le :attribute doit être un fuseau horaire valide.',
    'unique' => 'Le :attribute a déjà été pris.',
    'uploaded' => 'Le :attribute n\'a pas pu être téléchargé.',
    'url' => 'Le :attribute doit être une URL valide.',
    'uuid' => 'Le :attribute doit être un UUID valide.',

    // Custom Validation Language Lines
    'custom_dup' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    // Custom Validation Attributes
    'attributes' => [],
    'publish_date_required' => 'La date de publication est requise',
    'price_numeric_value' => 'Le prix doit être une valeur numérique',
    'quantity_integer_value' => 'La quantité doit être une valeur entière',
    'order_has_Expired' => 'La commande a expiré',
    'expired' => 'Expiré',
    'eid_required' => 'Le champ EID est requis.',
    'eid_string' => "L'EID doit être une chaîne de caractères.",
    'otp_required' => 'Le champ OTP est requis.',
    'amt_required' => 'Le champ montant est requis',
    'amt_numeric' => 'Le montant doit être un nombre',
    'payment_date_required' => 'La date de paiement est requise.',
    'payment_method_required' => 'Le mode de paiement est requis.',
    'total_amount_required' => 'Le montant total est requis.',
    'total_amount_numeric' => 'Le montant total doit être une valeur numérique.',
    'invoice_link_required' => 'Veuillez lier le montant à au moins une facture.',
    //Common
    'settings_form' => [
        'company' => [
            'required' => 'Le champ société est requis.',
        ],
        'website' => [
            'url' => 'Le site Web doit être une URL valide.',
        ],
        'phone' => [
            'regex' => 'Le format du numéro de téléphone est invalide.',
        ],
        'address' => [
            'required' => 'Le champ adresse est requis.',
            'max' => "L'adresse ne peut pas dépasser 300 caractères.",
        ],
        'logo' => [
            'mimes' => 'Le logo doit être un fichier PNG.',
        ],
        'driver' => [
            'required' => 'Le champ pilote est requis.',
        ],
        'port' => [
            'integer' => 'Le port doit être un entier.',
        ],
        'email' => [
            'required' => 'Le champ email est requis.',
            'email' => "L'adresse email doit être valide.",
        ],
        'password' => [
            'required' => 'Le champ mot de passe est requis.',
        ],
        'error_email' => [
            'email' => "L'adresse email d'erreur doit être valide.",
        ],
    ],

    'settings_forms' => [
        'company' => [
            'required' => 'Le nom de la société est requis.',
            'max' => 'Le nom de la société ne doit pas dépasser 50 caractères.',
        ],
        'company_email' => [
            'required' => "L'email de la société est requis.",
            'email' => "L'email de la société doit être une adresse valide.",
        ],
        'title' => [
            'max' => 'Le titre ne doit pas dépasser 50 caractères.',
        ],
        'website' => [
            'required' => "L'URL du site Web est requise.",
            'url' => 'Le site Web doit être une URL valide.',
            'regex' => 'Le format du site Web est invalide.',
        ],
        'phone' => [
            'required' => 'Le numéro de téléphone est requis.',
        ],
        'address' => [
            'required' => "L'adresse est requise.",
        ],
        'state' => [
            'required' => 'L’état est requis.',
        ],
        'country' => [
            'required' => 'Le pays est requis.',
        ],
        'gstin' => [
            'max' => 'Le GSTIN ne doit pas dépasser 15 caractères.',
        ],
        'default_currency' => [
            'required' => 'La devise par défaut est requise.',
        ],
        'admin_logo' => [
            'mimes' => "Le logo d'administration doit être un fichier de type : jpeg, png, jpg.",
            'max' => "Le logo d'administration ne doit pas dépasser 2 Mo.",
        ],
        'fav_icon' => [
            'mimes' => 'Le fav icon doit être un fichier de type : jpeg, png, jpg.',
            'max' => 'Le fav icon ne doit pas dépasser 2 Mo.',
        ],
        'logo' => [
            'mimes' => 'Le logo doit être un fichier de type : jpeg, png, jpg.',
            'max' => 'Le logo ne doit pas dépasser 2 Mo.',
        ],
    ],

    'social_media_form' => [
        'name' => [
            'required' => 'Le champ nom est requis.',
            'unique' => 'Ce nom existe déjà.',
            'max' => 'Le nom ne doit pas dépasser 50 caractères.',
        ],
        'link' => [
            'required' => 'Le champ lien est requis.',
            'url' => 'Le lien doit être une URL valide.',
            'regex' => 'Le format du lien est invalide.',
        ],
    ],

    //Email
    'custom' => [
        'password' => [
            'required_if' => 'Le champ mot de passe est requis pour le pilote de messagerie sélectionné.',
        ],
        'port' => [
            'required_if' => 'Le champ port est requis pour SMTP.',
        ],
        'encryption' => [
            'required_if' => "Le champ d'encodage est requis pour SMTP.",
        ],
        'host' => [
            'required_if' => 'Le champ hôte est requis pour SMTP.',
        ],
        'secret' => [
            'required_if' => 'Le champ secret est requis pour le pilote de messagerie sélectionné.',
        ],
        'domain' => [
            'required_if' => 'Le champ domaine est requis pour Mailgun.',
        ],
        'key' => [
            'required_if' => 'Le champ clé est requis pour SES.',
        ],
        'region' => [
            'required_if' => 'Le champ région est requis pour SES.',
        ],
        'email' => [
            'required_if' => 'Le champ email est requis pour le pilote de messagerie sélectionné.',
            'required' => 'Le champ email est requis.',
            'email' => 'Veuillez entrer une adresse email valide.',
            'not_matching' => "Le domaine de l'email doit correspondre au domaine du site actuel.",
        ],
        'driver' => [
            'required' => 'Le champ pilote est requis.',
        ],
    ],

    'customer_form' => [
        'first_name' => [
            'required' => 'Le champ prénom est requis.',
        ],
        'last_name' => [
            'required' => 'Le champ nom est requis.',
        ],
        'company' => [
            'required' => 'Le champ société est requis.',
        ],
        'mobile' => [
            'regex' => 'Le format du numéro de mobile est invalide.',
        ],
        'address' => [
            'required' => 'Le champ adresse est requis.',
        ],
        'zip' => [
            'required' => 'Le champ code postal est requis.',
            'min' => 'Le code postal doit contenir au moins 5 chiffres.',
            'numeric' => 'Le code postal doit être numérique.',
        ],
        'email' => [
            'required' => 'Le champ email est requis.',
            'email' => "L'email doit être une adresse valide.",
            'unique' => 'Cet email est déjà utilisé.',
        ],
    ],
    'contact_request' => [
        'conName' => 'Le champ nom est requis.',
        'email' => 'Le champ email est requis.',
        'conmessage' => 'Le champ message est requis.',
        'Mobile' => 'Le champ téléphone est requis.',
        'country_code' => 'Le champ téléphone est requis.',
        'demoname' => 'Le champ nom est requis.',
        'demomessage' => 'Le champ message est requis.',
        'demoemail' => 'Le champ email est requis.',
        'congg-recaptcha-response-1.required' => 'Échec de la vérification du robot. Veuillez réessayer.',
        'demo-recaptcha-response-1.required' => 'Échec de la vérification du robot. Veuillez réessayer.',
    ],

    'frontend_pages' => [
        'name' => [
            'required' => 'Le champ nom est requis.',
            'unique' => 'Ce nom existe déjà.',
            'max' => 'Le nom ne doit pas dépasser 20 caractères.',
            'regex' => 'Le nom ne peut contenir que des lettres et des espaces.',
        ],
        'publish' => [
            'required' => 'Le champ publication est requis.',
        ],
        'slug' => [
            'required' => 'Le champ slug est requis.',
        ],
        'url' => [
            'required' => 'Le champ URL est requis.',
            'url' => "L'URL doit être un lien valide.",
            'regex' => "Le format de l'URL est invalide.",
        ],
        'content' => [
            'required' => 'Le champ contenu est requis.',
        ],
        'created_at' => [
            'required' => 'Le champ date de création est requis.',
        ],
    ],

    //Order form
    'order_form' => [
        'client' => [
            'required' => 'Le champ client est requis.',
        ],
        'payment_method' => [
            'required' => 'Le champ mode de paiement est requis.',
        ],
        'promotion_code' => [
            'required' => 'Le champ code promotionnel est requis.',
        ],
        'order_status' => [
            'required' => 'Le champ statut de la commande est requis.',
        ],
        'product' => [
            'required' => 'Le champ produit est requis.',
        ],
        'subscription' => [
            'required' => 'Le champ abonnement est requis.',
        ],
        'price_override' => [
            'numeric' => 'Le prix substitutif doit être un nombre.',
        ],
        'qty' => [
            'integer' => 'La quantité doit être un entier.',
        ],
    ],

    //Payment form
    'coupon_form' => [
        'code' => [
            'required' => 'Le champ code promo est requis.',
            'string' => 'Le code promo doit être une chaîne de caractères.',
            'max' => 'Le code promo ne doit pas dépasser 255 caractères.',
        ],
        'type' => [
            'required' => 'Le champ type est requis.',
            'in' => 'Type invalide. Les valeurs autorisées sont : percentage, other_type.',
        ],
        'applied' => [
            'required' => 'Le champ d’application au produit est requis.',
            'date' => 'Le champ d’application au produit doit être une date valide.',
        ],
        'uses' => [
            'required' => 'Le champ utilisations est requis.',
            'numeric' => 'Le champ utilisations doit être un nombre.',
            'min' => 'Le champ utilisations doit être au moins :min.',
        ],
        'start' => [
            'required' => 'Le champ début est requis.',
            'date' => 'Le champ début doit être une date valide.',
        ],
        'expiry' => [
            'required' => 'Le champ expiration est requis.',
            'date' => 'Le champ expiration doit être une date valide.',
            'after' => "La date d'expiration doit être postérieure à la date de début.",
        ],
        'value' => [
            'required' => 'Le champ valeur de la remise est requis.',
            'numeric' => 'Le champ valeur de la remise doit être un nombre.',
            'between' => 'La valeur de la remise doit être entre :min et :max si le type est pourcentage.',
        ],
    ],

    'tax_form' => [
        'name' => [
            'required' => 'Le champ nom est requis.',
        ],
        'rate' => [
            'required' => 'Le champ taux est requis.',
            'numeric' => 'Le taux doit être un nombre.',
        ],
        'level' => [
            'required' => 'Le champ niveau est requis.',
            'integer' => 'Le niveau doit être un entier.',
        ],
        'country' => [
            'required' => 'Le champ pays est requis.',
            // 'exists' => 'Le pays sélectionné est invalide.',
        ],
        'state' => [
            'required' => 'Le champ état est requis.',
            // 'exists' => 'L’état sélectionné est invalide.',
        ],
    ],

    //Product
    'subscription_form' => [
        'name' => [
            'required' => 'Le champ nom est requis.',
        ],
        'subscription' => [
            'required' => 'Le champ abonnement est requis.',
        ],
        'regular_price' => [
            'required' => 'Le champ prix normal est requis.',
            'numeric' => 'Le prix normal doit être un nombre.',
        ],
        'selling_price' => [
            'required' => 'Le champ prix de vente est requis.',
            'numeric' => 'Le prix de vente doit être un nombre.',
        ],
        'products' => [
            'required' => 'Le champ produits est requis.',
        ],
    ],
    'bundle' => [
        'name' => [
            'required' => 'Le champ nom est requis.',
        ],
        'items' => [
            'required' => 'Chaque élément est requis.',
        ],
    ],

    'group' => [
        'name' => [
            'required' => 'Le nom est requis.',
        ],
        'features' => [
            'name' => [
                'required' => 'Tous les champs de fonctionnalités sont requis.',
            ],
        ],
        'price' => [
            'name' => [
                'required_unless' => 'Le prix est requis.',
            ],
        ],
        'value' => [
            'name' => [
                'required_unless' => 'La valeur est requise.',
            ],
        ],
        'type' => [
            'required_with' => 'Le type est requis.',
        ],
        'title' => [
            'required_with' => 'Le titre est requis.',
        ],
    ],

    'product' => [
        'name' => [
            'required' => 'Le champ nom est requis.',
        ],
        'type' => [
            'required' => 'Le champ type est requis.',
        ],
        'group' => [
            'required' => 'Le champ groupe est requis.',
        ],
        'subscription' => [
            'required' => 'Le champ abonnement est requis.',
        ],
        'currency' => [
            'required' => 'Le champ devise est requis.',
        ],
        // 'price' => [
        //     'required' => 'Le champ prix est requis.',
        // ],
        'file' => [
            'required_without_all' => 'Le champ fichier est requis si github_owner ou github_repository ne sont pas fournis.',
            'mimes' => 'Le fichier doit être une archive zip.',
        ],
        'image' => [
            'required_without_all' => 'Le champ image est requis si github_owner ou github_repository ne sont pas fournis.',
            'mimes' => "L'image doit être un fichier PNG.",
        ],
        'github_owner' => [
            'required_without_all' => "Le champ propriétaire GitHub est requis si ni le fichier ni l'image ne sont fournis.",
        ],
        'github_repository' => [
            'required_without_all' => "Le champ dépôt GitHub est requis si ni le fichier ni l'image ne sont fournis.",
            'required_if' => 'Le champ dépôt GitHub est requis si le type est 2.',
        ],
    ],

    'users' => [
        'first_name' => [
            'required' => 'Le champ prénom est requis.',
        ],
        'last_name' => [
            'required' => 'Le champ nom est requis.',
        ],
        'company' => [
            'required' => 'Le champ société est requis.',
        ],
        'email' => [
            'required' => 'Le champ email est requis.',
            'email' => "L'adresse email doit être valide.",
            'unique' => 'Cette adresse email est déjà utilisée.',
        ],
        'address' => [
            'required' => 'Le champ adresse est requis.',
        ],
        'mobile' => [
            'required' => 'Le champ mobile est requis.',
        ],
        'country' => [
            'required' => 'Le champ pays est requis.',
            'exists' => 'Le pays sélectionné est invalide.',
        ],
        'state' => [
            'required_if' => "Le champ état est requis lorsque le pays est l'Inde.",
        ],
        'timezone_id' => [
            'required' => 'Le champ fuseau horaire est requis.',
        ],
        'user_name' => [
            'required' => "Le champ nom d'utilisateur est requis.",
            'unique' => "Le nom d'utilisateur est déjà utilisé.",
        ],
        'zip' => [
            'regex' => "Le champ état est requis lorsque le pays est l'Inde.",
        ],
    ],

    'profile_form' => [
        'first_name' => [
            'required' => 'Le prénom est requis.',
            'min' => 'Le prénom doit comporter au moins :min caractères.',
            'max' => 'Le prénom ne peut pas dépasser :max caractères.',
        ],
        'last_name' => [
            'required' => 'Le nom est requis.',
            'max' => 'Le nom ne peut pas dépasser :max caractères.',
        ],
        'company' => [
            'required' => 'Le nom de l’entreprise est requis.',
            'max' => 'Le nom de l’entreprise ne peut pas dépasser :max caractères.',
        ],
        'email' => [
            'required' => 'L’adresse email est requise.',
            'email' => 'Entrez une adresse email valide.',
            'unique' => "L'adresse email est déjà utilisée. Veuillez en choisir une autre.",
        ],
        'mobile' => [
            'required' => 'Le numéro de mobile est requis.',
            'regex' => 'Entrez un numéro de mobile valide.',
            'min' => 'Le numéro de mobile doit comporter au moins :min caractères.',
            'max' => 'Le numéro de mobile ne peut pas dépasser :max caractères.',
        ],
        'address' => [
            'required' => 'L’adresse est requise.',
        ],
        'user_name' => [
            'required' => "Le nom d'utilisateur est requis.",
            'unique' => 'Ce nom d’utilisateur est déjà utilisé.',
        ],
    ],
    'timezone_id' => [
        'required' => 'Le fuseau horaire est requis.',
    ],
    'country' => [
        'required' => 'Le pays est requis.',
        'exists' => 'Le pays sélectionné est invalide.',
    ],
    'state' => [
        'required_if' => 'Le champ état est requis lorsque le pays est l\'Inde.',
    ],
    'old_password' => [
        'required' => 'L\'ancien mot de passe est requis.',
        'min' => 'L\'ancien mot de passe doit comporter au moins :min caractères.',
    ],
    'new_password' => [
        'required' => 'Le nouveau mot de passe est requis.',
        'different' => 'Le nouveau mot de passe doit être différent de l\'ancien mot de passe.',
    ],
    'confirm_password' => [
        'required' => 'La confirmation du mot de passe est requise.',
        'same' => 'La confirmation du mot de passe doit correspondre au nouveau mot de passe.',
    ],
    'terms' => [
        'required' => 'Vous devez accepter les conditions.',
    ],
    'password' => [
        'required' => 'Le mot de passe est requis.',
    ],
    'password_confirmation' => [
        'required' => 'La confirmation du mot de passe est requise.',
        'same' => 'Les mots de passe ne correspondent pas.',
    ],
    'mobile_code' => [
        'required' => 'Entrez le code pays (mobile)',
    ],

    //Invoice form
    'invoice' => [
        'user' => [
            'required' => 'Le champ client est requis.',
        ],
        'date' => [
            'required' => 'Le champ date est requis.',
            'date' => 'La date doit être une date valide.',
        ],
        'domain' => [
            'regex' => 'Le format du domaine est invalide.',
        ],
        'plan' => [
            'required_if' => 'Le champ abonnement est requis.',
        ],
        'price' => [
            'required' => 'Le champ prix est requis.',
        ],
        'product' => [
            'required' => 'Le champ produit est requis.',
        ],
    ],

    //LocalizedLicense form
    'domain_form' => [
        'domain' => [
            'required' => 'Le champ domaine est requis.',
            'url' => 'Le domaine doit être une URL valide.',
        ],
    ],

    //Product Renewal form
    'product_renewal' => [
        'domain' => [
            'required' => 'Le champ domaine est requis.',
            'no_http' => 'Le domaine ne doit pas contenir "http" ou "https".',
        ],
    ],

    //Language form
    'language' => [
        'required' => 'Le champ langue est requis.',
        'invalid' => 'La langue sélectionnée est invalide.',
    ],

    //UpdateSroragePathRequest form
    'storage_path' => [
        'disk' => [
            'required' => 'Le champ disque de stockage est requis.',
            'string' => 'Le disque doit être une chaîne de caractères.',
        ],
        'path' => [
            'string' => 'Le chemin doit être une chaîne de caractères.',
            'nullable' => 'Le champ chemin est optionnel.',
        ],
    ],

    //ValidateSecretRequest form
    'validate_secret' => [
        'totp' => [
            'required' => 'Veuillez entrer le code',
            'digits' => 'Veuillez entrer un code valide de 6 chiffres',
        ],
    ],

    //VerifyOtp form
    'verify_email' => [
        'required' => 'Le champ email est requis.',
        'email' => 'L\'email doit être une adresse email valide.',
        'verify_email' => 'La vérification de l\'email a échoué.',
    ],

    'verify_country_code' => [
        'required' => 'Le code pays est requis.',
        'numeric' => 'Le code pays doit être un nombre valide.',
        'verify_country_code' => 'La vérification du code pays a échoué.',
    ],

    'verify_number' => [
        'required' => 'Le numéro est requis.',
        'numeric' => 'Le numéro doit être un nombre valide.',
        'verify_number' => 'La vérification du numéro a échoué.',
    ],

    'password_otp' => [
        'required' => 'Le champ mot de passe est requis.',
        'password' => 'Le mot de passe est incorrect.',
        'invalid' => 'Mot de passe invalide.',
    ],

    //AuthController file
    'auth_controller' => [
        'name_required' => 'Le nom est requis.',
        'name_max' => 'Le nom ne peut pas dépasser 255 caractères.',

        'email_required' => 'L\'email est requis.',
        'email_email' => 'Entrez une adresse email valide.',
        'email_max' => 'L\'email ne peut pas dépasser 255 caractères.',
        'email_unique' => 'Cet email est déjà enregistré.',

        'password_required' => 'Le mot de passe est requis.',
        'password_confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        'password_min' => 'Le mot de passe doit comporter au moins 6 caractères.',
    ],

    'resend_otp' => [
        'eid_required' => 'Le champ EID est requis.',
        'eid_string' => 'L\'EID doit être une chaîne de caractères.',
        'type_required' => 'Le champ type est requis.',
        'type_string' => 'Le type doit être une chaîne de caractères.',
        'type_in' => 'Le type sélectionné est invalide.',
    ],

    'verify_otp' => [
        'eid_required' => 'L\'ID de l\'employé est requis.',
        'eid_string' => 'L\'ID de l\'employé doit être une chaîne de caractères.',
        'otp_required' => 'Le OTP est requis.',
        'otp_size' => 'Le OTP doit comporter exactement 6 caractères.',
        'recaptcha_required' => 'Veuillez compléter le CAPTCHA.',
        'recaptcha_size' => 'La réponse CAPTCHA est invalide.',
    ],

    'company_validation' => [
        'company_required' => 'Le nom de l\'entreprise est requis.',
        'company_string' => 'L\'entreprise doit être un texte.',
        'address_required' => 'L\'adresse est requise.',
        'address_string' => 'L\'adresse doit être un texte.',
    ],

    'token_validation' => [
        'token_required' => 'Le jeton est requis.',
        'password_required' => 'Le champ mot de passe est requis.',
        'password_confirmed' => 'La confirmation du mot de passe ne correspond pas.',
    ],

    'custom_email' => [
        'required' => 'Le champ email est requis.',
        'email' => 'Veuillez entrer une adresse email valide.',
        'exists' => 'Cet email n\'est pas enregistré chez nous.',
    ],

    'newsletterEmail' => [
        'required' => 'L\'email de la newsletter est requis.',
        'email' => 'Veuillez entrer une adresse email valide pour la newsletter.',
    ],

    'widget' => [
        'name_required' => 'Le nom est requis.',
        'name_max' => 'Le nom ne peut pas dépasser 50 caractères.',
        'publish_required' => 'Le statut de publication est requis.',
        'type_required' => 'Le type est requis.',
        'type_unique' => 'Ce type existe déjà.',
    ],

    'payment' => [
        'payment_date_required' => 'La date de paiement est requise.',
        'payment_method_required' => 'Le mode de paiement est requis.',
        'amount_required' => 'Le montant est requis.',
    ],

    'custom_date' => [
        'date_required' => 'Le champ date est requis.',
        'total_required' => 'Le champ total est requis.',
        'status_required' => 'Le champ statut est requis.',
    ],

    'plan_renewal' => [
        'plan_required' => 'Le champ plan est requis.',
        'payment_method_required' => 'Le champ mode de paiement est requis.',
        'cost_required' => 'Le champ coût est requis.',
        'code_not_valid' => 'Le code de promotion n\'est pas valide.',
    ],

    'rate' => [
        'required' => 'Le taux est requis.',
        'numeric' => 'Le taux doit être un nombre.',
    ],

    'product_validate' => [
        'producttitle_required' => 'Le titre du produit est requis.',
        'version_required' => 'La version est requise.',
        'filename_required' => 'Veuillez télécharger un fichier.',
        'dependencies_required' => 'Le champ des dépendances est requis.',
    ],
    'product_sku_unique' => 'Le SKU du produit doit être unique',
    'product_name_unique' => 'Le nom doit être unique',
    'product_show_agent_required' => 'Sélectionnez votre préférence de page de panier',
    'product_controller' => [
        'name_required' => 'Le nom du produit est requis.',
        'name_unique' => 'Le nom doit être unique.',
        'type_required' => 'Le type de produit est requis.',
        'description_required' => 'La description du produit est requise.',
        'product_description_required' => 'La description détaillée du produit est requise.',
        'image_mimes' => 'L\'image doit être un fichier de type : jpeg, png, jpg.',
        'image_max' => 'L\'image ne doit pas dépasser 2048 kilobytes.',
        'product_sku_required' => 'Le SKU du produit est requis.',
        'group_required' => 'Le groupe de produits est requis.',
        'show_agent_required' => 'Sélectionnez votre préférence de page de panier.',
    ],
    'current_domain_required' => 'Le domaine actuel est requis.',
    'new_domain_required' => 'Le nouveau domaine est requis.',
    'special_characters_not_allowed' => 'Les caractères spéciaux ne sont pas autorisés dans le nom de domaine',
    'orderno_required' => 'Le numéro de commande est requis',
    'cloud_central_domain_required' => 'Le domaine central du cloud est requis.',
    'cloud_cname_required' => 'Le CNAME du cloud est requis.',
    'cloud_tenant' => [
        'cloud_top_message_required' => 'Le message principal du cloud est requis.',
        'cloud_label_field_required' => 'Le champ de l\'étiquette du cloud est requis.',
        'cloud_label_radio_required' => 'Le bouton radio de l\'étiquette du cloud est requis.',
        'cloud_product_required' => 'Le produit cloud est requis.',
        'cloud_free_plan_required' => 'Le plan gratuit du cloud est requis.',
        'cloud_product_key_required' => 'La clé du produit cloud est requise.',
    ],
    'reg_till_after' => 'La date d\'inscription jusqu\'à doit être après la date d\'inscription de.',
    'extend_product' => [
        'title_required' => 'Le champ du titre est requis.',
        'version_required' => 'Le champ de la version est requis.',
        'dependencies_required' => 'Le champ des dépendances est requis.',
    ],
    'please_enter_recovery_code' => 'Veuillez entrer le code de récupération',
    'social_login' => [
        'client_id_required' => 'L\'ID client est requis pour Google, Github, ou Linkedin.',
        'client_secret_required' => 'Le secret client est requis pour Google, Github, ou Linkedin.',
        'api_key_required' => 'La clé API est requise pour Twitter.',
        'api_secret_required' => 'Le secret API est requis pour Twitter.',
        'redirect_url_required' => 'L\'URL de redirection est requise.',
    ],
    'thirdparty_api' => [
        'app_name_required' => 'Le nom de l\'application est requis.',
        'app_key_required' => 'La clé de l\'application est requise.',
        'app_key_size' => 'La clé de l\'application doit être exactement de 32 caractères.',
        'app_secret_required' => 'Le secret de l\'application est requis.',
    ],
    'plan_request' => [
        'name_required' => 'Le champ nom est obligatoire',
        'product_quant_req' => "Le champ quantité du produit est obligatoire si le nombre d'agents n'est pas renseigné.",
        'no_agent_req' => "Le champ nombre d'agents est obligatoire si la quantité du produit n'est pas renseignée.",
        'pro_req' => 'Le champ produit est obligatoire',
        'offer_price' => "Le prix de l'offre ne doit pas être supérieur à 100",
    ],
    'razorpay_val' => [
        'business_required' => 'Le champ entreprise est obligatoire.',
        'cmd_required' => 'Le champ commande est obligatoire.',
        'paypal_url_required' => 'L\'URL PayPal est obligatoire.',
        'paypal_url_invalid' => 'L\'URL PayPal doit être une URL valide.',
        'success_url_invalid' => 'L\'URL de succès doit être une URL valide.',
        'cancel_url_invalid' => 'L\'URL d\'annulation doit être une URL valide.',
        'notify_url_invalid' => 'L\'URL de notification doit être une URL valide.',
        'currencies_required' => 'Le champ devises est obligatoire.',
    ],

];
