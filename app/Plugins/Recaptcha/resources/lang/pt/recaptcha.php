<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'Configurações do reCAPTCHA',
    'captcha_configuration' => 'Configuração do reCAPTCHA',
    'captcha_version' => 'Versão do reCAPTCHA',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Invisível',
    'recaptcha_v2_checkbox' => 'Caixa de seleção do reCAPTCHA v2',
    'select_captcha_type' => 'Selecione qual versão do reCAPTCHA usar',
    'failover_action' => 'Ação de failover',
    'none' => 'Nenhum',
    'fallback_v2_checkbox' => 'Voltar para a caixa de seleção do reCAPTCHA v2',
    'action_if_captcha_fails' => 'Ação a ser tomada se o reCAPTCHA falhar',

    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'Configurações do reCAPTCHA v3',
    'v3_site_key' => 'Chave do site v3',
    'enter_v3_site_key' => 'Digite a chave do seu site reCAPTCHA v3',
    'v3_secret_key' => 'Chave secreta v3',
    'enter_v3_secret_key' => 'Digite sua chave secreta do reCAPTCHA v3',
    'v3_score_threshold' => 'Limite de pontuação v3',
    'v3_score_hint' => 'Valor entre 0,0 e 1,0 (quanto maior, melhor)',
    'v3_preview' => 'Visualização v3',

    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'Configurações do reCAPTCHA v2',
    'v2_site_key' => 'Chave do site v2',
    'enter_v2_site_key' => 'Digite a chave do seu site reCAPTCHA v2',
    'v2_secret_key' => 'Chave secreta v2',
    'enter_v2_secret_key' => 'Digite sua chave secreta do reCAPTCHA v2',
    'v2_preview' => 'Visualização v2',

    /*
    * Appearance
    */
    'appearance_messages' => 'Aparência',
    'theme' => 'Tema',
    'theme_light' => 'Claro',
    'theme_dark' => 'Escuro',
    'size' => 'Tamanho',
    'size_normal' => 'Normal',
    'size_compact' => 'Compacto',
    'badge_position' => 'Posição do selo',
    'badge_bottomright' => 'Inferior direito',
    'badge_bottomleft' => 'Inferior esquerdo',
    'badge_inline' => 'Em linha',

    /*
    * Common
    */
    'save' => 'Salvar',
    'saving' => 'Salvando',
    'home' => 'Início',
    'settings' => 'Configurações',

    /*
    * Success messages
    */
    'captcha_settings_updated' => 'Configurações do reCAPTCHA atualizadas com sucesso!',

    /*
    * Error messages
    */
    'captcha_message' => 'A verificação do reCAPTCHA falhou. Tente novamente.',

    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Este campo é obrigatório quando a condição é atendida',
    'select_captcha_version' => 'Selecione uma versão do reCAPTCHA',
    'v3_site_key_required' => 'A chave do site reCAPTCHA v3 é obrigatória',
    'v3_secret_key_required' => 'A chave secreta do reCAPTCHA v3 é obrigatória',
    'v2_site_key_required' => 'A chave do site reCAPTCHA v2 é obrigatória',
    'v2_secret_key_required' => 'A chave secreta do reCAPTCHA v2 é obrigatória',
    'valid_recaptcha_site_key' => 'Digite uma chave de site reCAPTCHA válida',
    'valid_recaptcha_secret_key' => 'Digite uma chave secreta reCAPTCHA válida',
    'score_threshold_required' => 'O limite de pontuação é obrigatório para o reCAPTCHA v3',
    'valid_number' => 'Digite um número válido',
    'complete_recaptcha_v3' => 'Falha ao gerar o token do reCAPTCHA. Verifique se a chave do site está configurada corretamente e é válida.',
    'failed_generate_v3_token' => 'Falha ao gerar o token do reCAPTCHA. Verifique se a chave do site está configurada corretamente e é válida.',
    'complete_recaptcha_v2' => 'Preencha o reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Falha ao gerar o token do reCAPTCHA v2.',
    'settings_saved' => 'Configurações salvas.',
    'failed_save_settings' => 'Falha ao salvar as configurações. Tente novamente.',

    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Chave secreta ou token de resposta inválido',
    'captcha_verification_failed' => 'A verificação do reCAPTCHA falhou (incompatibilidade de pontuação/ação/nome do host)',

    /*
    * Validation messages
    */
    'captcha_version_required' => 'A versão do reCAPTCHA é obrigatória',
    'captcha_version_in' => 'A versão do reCAPTCHA selecionada é inválida',
    'failover_action_required' => 'A ação de failover é obrigatória',
    'failover_action_in' => 'A ação de failover selecionada é inválida',
    'score_threshold_numeric' => 'O limite de pontuação deve ser um número',
    'score_threshold_min' => 'O limite de pontuação deve ser de pelo menos 0',
    'score_threshold_max' => 'O limite de pontuação não deve ser maior que 1',
    'theme_required' => 'O tema é obrigatório',
    'theme_in' => 'O tema selecionado é inválido',
    'size_required' => 'O tamanho é obrigatório',
    'size_in' => 'O tamanho selecionado é inválido',
    'badge_position_required' => 'A posição do selo é obrigatória',
    'badge_position_in' => 'A posição do selo selecionada é inválida',
];
