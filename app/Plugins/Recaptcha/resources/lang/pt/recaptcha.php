<?php

return [
    /*
    * Recaptcha Settings Page
    */
    'captcha_settings' => 'Configurações do Captcha',
    'captcha_configuration' => 'Configuração do Captcha',
    'captcha_version' => 'Versão do Captcha',
    'recaptcha_v3' => 'reCAPTCHA v3',
    'recaptcha_v2_invisible' => 'reCAPTCHA v2 Invisível',
    'recaptcha_v2_checkbox' => 'reCAPTCHA v2 Caixa de seleção',
    'select_captcha_type' => 'Selecione qual versão do reCAPTCHA usar',
    'failover_action' => 'Ação de Failover',
    'none' => 'Nenhum',
    'fallback_v2_checkbox' => 'Voltar para reCAPTCHA v2 Caixa de seleção',
    'action_if_captcha_fails' => 'Ação a ser tomada se o reCAPTCHA falhar',
    
    /*
    * Recaptcha v3 Settings
    */
    'recaptcha_v3_settings' => 'Configurações do reCAPTCHA v3',
    'v3_site_key' => 'Chave do site v3',
    'enter_v3_site_key' => 'Digite sua chave de site reCAPTCHA v3',
    'v3_secret_key' => 'Chave secreta v3',
    'enter_v3_secret_key' => 'Digite sua chave secreta reCAPTCHA v3',
    'v3_score_threshold' => 'Limite de pontuação v3',
    'v3_score_hint' => 'Valor entre 0,0 e 1,0 (maior é melhor)',
    'v3_preview' => 'Pré-visualização v3',
    
    /*
    * Recaptcha v2 Settings
    */
    'recaptcha_v2_settings' => 'Configurações do reCAPTCHA v2',
    'v2_site_key' => 'Chave do site v2',
    'enter_v2_site_key' => 'Digite sua chave de site reCAPTCHA v2',
    'v2_secret_key' => 'Chave secreta v2',
    'enter_v2_secret_key' => 'Digite sua chave secreta reCAPTCHA v2',
    'v2_preview' => 'Pré-visualização v2',
    
    /*
    * Appearance & Messages
    */
    'appearance_messages' => 'Aparência e Mensagens',
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
    'captcha_message' => 'Falha na verificação do captcha. Por favor, tente novamente.',
    
    /*
    * JavaScript validation messages
    */
    'field_required_condition' => 'Este campo é obrigatório quando a condição é atendida',
    'select_captcha_version' => 'Por favor, selecione uma versão do captcha',
    'v3_site_key_required' => 'Chave do site reCAPTCHA v3 é obrigatória',
    'v3_secret_key_required' => 'Chave secreta reCAPTCHA v3 é obrigatória',
    'v2_site_key_required' => 'Chave do site reCAPTCHA v2 é obrigatória',
    'v2_secret_key_required' => 'Chave secreta reCAPTCHA v2 é obrigatória',
    'valid_recaptcha_site_key' => 'Por favor, insira uma chave de site reCAPTCHA válida',
    'valid_recaptcha_secret_key' => 'Por favor, insira uma chave secreta reCAPTCHA válida',
    'score_threshold_required' => 'Limite de pontuação é obrigatório para reCAPTCHA v3',
    'valid_number' => 'Por favor, insira um número válido',
    'complete_recaptcha_v3' => 'Por favor, complete o reCAPTCHA v3.',
    'failed_generate_v3_token' => 'Falha ao gerar token reCAPTCHA v3.',
    'complete_recaptcha_v2' => 'Por favor, complete o reCAPTCHA v2.',
    'failed_generate_v2_token' => 'Falha ao gerar token reCAPTCHA v2.',
    'settings_saved' => 'Configurações salvas.',
    'failed_save_settings' => 'Falha ao salvar configurações. Por favor, tente novamente.',
    
    /*
    * Backend validation messages
    */
    'invalid_secret_or_token' => 'Chave secreta ou token de resposta inválido',
    'captcha_verification_failed' => 'Falha na verificação do Captcha (incompatibilidade de pontuação/ação/nome do host)',
    
    /*
    * Validation messages
    */
    'captcha_version_required' => 'Versão do Captcha é obrigatória',
    'captcha_version_in' => 'Versão do Captcha selecionada é inválida',
    'failover_action_required' => 'Ação de failover é obrigatória',
    'failover_action_in' => 'Ação de failover selecionada é inválida',
    'score_threshold_numeric' => 'Limite de pontuação deve ser um número',
    'score_threshold_min' => 'Limite de pontuação deve ser pelo menos 0',
    'score_threshold_max' => 'Limite de pontuação não deve ser maior que 1',
    'theme_required' => 'Tema é obrigatório',
    'theme_in' => 'Tema selecionado é inválido',
    'size_required' => 'Tamanho é obrigatório',
    'size_in' => 'Tamanho selecionado é inválido',
    'badge_position_required' => 'Posição do selo é obrigatória',
    'badge_position_in' => 'Posição do selo selecionada é inválida',
];