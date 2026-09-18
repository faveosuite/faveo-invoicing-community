import * as yup from 'yup'

function needsV3(form) {
    return form.captcha_version === 'v3_invisible'
}

function needsV2(form) {
    return form.captcha_version === 'v2_checkbox'
        || form.captcha_version === 'v2_invisible'
        || (form.captcha_version === 'v3_invisible' && form.failover_action === 'v2_checkbox')
}

export function buildRecaptchaSchema(form) {
    const schema = {
        captcha_version: yup.string()
            .required(() => __('recaptcha.captcha_version_required'))
            .oneOf(['v3_invisible', 'v2_invisible', 'v2_checkbox']),
        failover_action: yup.string()
            .required(() => __('recaptcha.failover_action_required'))
            .oneOf(['none', 'v2_checkbox']),
        theme: yup.string()
            .required(() => __('recaptcha.theme_required'))
            .oneOf(['light', 'dark']),
        size: yup.string()
            .required(() => __('recaptcha.size_required'))
            .oneOf(['normal', 'compact']),
        badge_position: yup.string()
            .required(() => __('recaptcha.badge_position_required'))
            .oneOf(['bottomright', 'bottomleft', 'inline']),
    }

    if (needsV3(form)) {
        schema.v3_site_key = yup.string()
            .trim()
            .required(() => __('recaptcha.v3_site_key_required'))
        schema.v3_secret_key = yup.string()
            .trim()
            .required(() => __('recaptcha.v3_secret_key_required'))
        schema.score_threshold = yup.number()
            .typeError(() => __('recaptcha.valid_number'))
            .required(() => __('recaptcha.score_threshold_required'))
            .min(0, () => __('recaptcha.score_threshold_min'))
            .max(1, () => __('recaptcha.score_threshold_max'))
    }

    if (needsV2(form)) {
        schema.v2_site_key = yup.string()
            .trim()
            .required(() => __('recaptcha.v2_site_key_required'))
        schema.v2_secret_key = yup.string()
            .trim()
            .required(() => __('recaptcha.v2_secret_key_required'))
    }

    return yup.object(schema)
}
