<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'Paramètres reCAPTCHA',
    'captcha_configuration' => 'Configuration reCAPTCHA',
    'captcha_version' => 'Version reCAPTCHA',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Invisible',
    'recaptcha_v2_checkbox' => 'Case à cocher reCAPTCHA v2',
    'select_captcha_type' => 'Sélectionnez la version de reCAPTCHA à utiliser',
    'failover_action' => 'Action de basculement',
    'none' => 'Aucun',
    'fallback_v2_checkbox' => 'Revenir à la case à cocher reCAPTCHA v2',
    'action_if_captcha_fails' => 'Action à entreprendre en cas d'échec de reCAPTCHA',

    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'Paramètres reCAPTCHA v3',
    'v3_site_key' => 'Clé de site v3',
    'enter_v3_site_key' => 'Entrez votre clé de site reCAPTCHA v3',
    'v3_secret_key' => 'Clé secrète v3',
    'enter_v3_secret_key' => 'Entrez votre clé secrète reCAPTCHA v3',
    'v3_score_threshold' => 'Seuil de score v3',
    'v3_score_hint' => 'Valeur comprise entre 0,0 et 1,0 (plus la valeur est élevée, mieux c'est)',
    'v3_preview' => 'Aperçu v3',

    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'Paramètres reCAPTCHA v2',
    'v2_site_key' => 'Clé de site v2',
    'enter_v2_site_key' => 'Entrez votre clé de site reCAPTCHA v2',
    'v2_secret_key' => 'Clé secrète v2',
    'enter_v2_secret_key' => 'Entrez votre clé secrète reCAPTCHA v2',
    'v2_preview' => 'Aperçu v2',

    /*
    * Appearance
    */
    'appearance_messages' => 'Apparence',
    'theme' => 'Thème',
    'theme_light' => 'Clair',
    'theme_dark' => 'Foncé',
    'size' => 'Taille',
    'size_normal' => 'Normal',
    'size_compact' => 'Compact',
    'badge_position' => 'Position du badge',
    'badge_bottomright' => 'En bas à droite',
    'badge_bottomleft' => 'En bas à gauche',
    'badge_inline' => 'En ligne',

    /*
    * Common
    */
    'save' => 'Enregistrer',
    'saving' => 'Enregistrement en cours',
    'home' => 'Accueil',
    'settings' => 'Paramètres',

    /*
    * Success messages
    */
    'captcha_settings_updated' => 'Paramètres reCAPTCHA mis à jour avec succès !',

    /*
    * Error messages
    */
    'captcha_message' => 'La vérification reCAPTCHA a échoué. Veuillez réessayer.',

    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Ce champ est obligatoire lorsque la condition est remplie',
    'select_captcha_version' => 'Veuillez sélectionner une version de reCAPTCHA',
    'v3_site_key_required' => 'La clé de site reCAPTCHA v3 est requise',
    'v3_secret_key_required' => 'La clé secrète reCAPTCHA v3 est requise',
    'v2_site_key_required' => 'La clé de site reCAPTCHA v2 est requise',
    'v2_secret_key_required' => 'La clé secrète reCAPTCHA v2 est requise',
    'valid_recaptcha_site_key' => 'Veuillez saisir une clé de site reCAPTCHA valide',
    'valid_recaptcha_secret_key' => 'Veuillez saisir une clé secrète reCAPTCHA valide',
    'score_threshold_required' => 'Le seuil de score est requis pour reCAPTCHA v3',
    'valid_number' => 'Veuillez saisir un numéro valide',
    'complete_recaptcha_v3' => 'Échec de la génération du jeton reCAPTCHA. Veuillez vérifier que la clé du site est correctement configurée et valide.',
    'failed_generate_v3_token' => 'Échec de la génération du jeton reCAPTCHA. Veuillez vérifier que la clé du site est correctement configurée et valide.',
    'complete_recaptcha_v2' => 'Veuillez compléter le reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Échec de la génération du jeton reCAPTCHA v2.',
    'settings_saved' => 'Paramètres enregistrés.',
    'failed_save_settings' => 'Échec de l'enregistrement des paramètres. Veuillez réessayer.',

    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'La clé secrète ou le jeton de réponse n'est pas valide',
    'captcha_verification_failed' => 'La vérification reCAPTCHA a échoué (non-concordance du score/de l'action/du nom d'hôte)',

    /*
    * Validation messages
    */
    'captcha_version_required' => 'La version de reCAPTCHA est requise',
    'captcha_version_in' => 'La version de reCAPTCHA sélectionnée n'est pas valide',
    'failover_action_required' => 'Une action de basculement est requise',
    'failover_action_in' => 'L'action de basculement sélectionnée n'est pas valide',
    'score_threshold_numeric' => 'Le seuil de score doit être un nombre',
    'score_threshold_min' => 'Le seuil de score doit être d'au moins 0',
    'score_threshold_max' => 'Le seuil de score ne doit pas être supérieur à 1',
    'theme_required' => 'Le thème est requis',
    'theme_in' => 'Le thème sélectionné n'est pas valide',
    'size_required' => 'La taille est requise',
    'size_in' => 'La taille sélectionnée n'est pas valide',
    'badge_position_required' => 'La position du badge est requise',
    'badge_position_in' => 'La position du badge sélectionnée n'est pas valide',
];
