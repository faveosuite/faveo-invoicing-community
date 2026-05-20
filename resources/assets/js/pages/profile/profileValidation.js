export function profileRules(form, t) {
    return {
        first_name:  [form.first_name,  { isRequired: t('validation.users.first_name.required') }],
        last_name:   [form.last_name,   { isRequired: t('validation.users.last_name.required') }],
        user_name:   [form.user_name,   { isRequired: t('validation.users.user_name.required') }],
        company:     [form.company,     { isRequired: t('validation.users.company.required') }],
        mobile:      [form.mobile,      { isRequired: t('validation.users.mobile.required') }],
        address:     [form.address,     { isRequired: t('validation.users.address.required') }],
        timezone_id: [form.timezone_id, { isRequired: t('validation.users.timezone_id.required') }],
        country:     [form.country,     { isRequired: t('validation.users.country.required') }],
    }
}

export function passwordChangeRules(pwForm, t) {
    return {
        old_password:     [pwForm.old_password,     { isRequired: t('message.field_required') }],
        new_password:     [pwForm.new_password,     { isRequired: t('message.field_required') }],
        confirm_password: [pwForm.confirm_password, { isRequired: t('message.field_required') }],
    }
}
