export function userCreateRules(form, t) {
    return {
        first_name:  [form.first_name,  { isRequired: t('validation.users.first_name.required') }],
        last_name:   [form.last_name,   { isRequired: t('validation.users.last_name.required') }],
        email:       [form.email,       { isRequired: t('validation.users.email.required') }, { isEmail: t('validation.users.email.email') }],
        company:     [form.company,     { isRequired: t('validation.users.company.required') }],
        address:     [form.address,     { isRequired: t('validation.users.address.required') }],
        mobile:      [form.mobile,      { isRequired: t('validation.users.mobile.required') }],
        country:     [form.country,     { isRequired: t('validation.users.country.required') }],
        timezone_id: [form.timezone_id, { isRequired: t('validation.users.timezone_id.required') }],
    }
}

export function userEditRules(form, t) {
    return {
        first_name:  [form.first_name,  { isRequired: t('validation.users.first_name.required') }],
        last_name:   [form.last_name,   { isRequired: t('validation.users.last_name.required') }],
        email:       [form.email,       { isRequired: t('validation.users.email.required') }, { isEmail: t('validation.users.email.email') }],
        company:     [form.company,     { isRequired: t('validation.users.company.required') }],
        address:     [form.address,     { isRequired: t('validation.users.address.required') }],
        mobile:      [form.mobile,      { isRequired: t('validation.users.mobile.required') }],
        timezone_id: [form.timezone_id, { isRequired: t('validation.users.timezone_id.required') }],
    }
}
