export function thirdPartyAppRules(form, t) {
    return {
        app_name:   [form.app_name,   { isRequired: t('message.field_required') }],
        app_key:    [form.app_key,    { isRequired: t('message.field_required') }],
        app_secret: [form.app_secret, { isRequired: t('message.field_required') }],
    }
}
