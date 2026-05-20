export function socialLoginRules(form, t) {
    return {
        client_id:     [form.client_id,     { isRequired: t('message.field_required') }],
        client_secret: [form.client_secret, { isRequired: t('message.field_required') }],
        redirect_url:  [form.redirect_url,  { isRequired: t('message.field_required') }, { isUrl: t('message.invalid_url') }],
    }
}
