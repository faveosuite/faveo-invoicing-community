export function systemSettingsRules(form, t) {
    return {
        company:          [form.company,          { isRequired: t('validation.users.company.required') }, 'max(50)'],
        company_email:    [form.company_email,    { isRequired: t('validation.users.email.required') }, { isEmail: t('message.invalid_email') }],
        website:          [form.website,          { isRequired: t('message.field_required') }, { isUrl: t('message.invalid_url') }],
        phone:            [form.phone,            { isRequired: t('message.field_required') }],
        address:          [form.address,          { isRequired: t('validation.users.address.required') }],
        country:          [form.country,          { isRequired: t('validation.users.country.required') }],
        state:            [form.state,            { isRequired: t('message.field_required') }],
        default_currency: [form.default_currency, { isRequired: t('message.field_required') }],
    }
}
