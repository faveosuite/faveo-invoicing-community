export function frontendPageCreateRules(form, t) {
    const rules = {
        name:    [form.name,    { isRequired: t('validation.frontend_pages.name.required') }],
        slug:    [form.slug,    { isRequired: t('validation.frontend_pages.slug.required') }],
        content: [form.content, { isRequired: t('validation.frontend_pages.content.required') }],
    }
    if (form.type !== 'contactus') {
        rules.url = [form.url, { isRequired: t('validation.frontend_pages.url.required') }]
    }
    return rules
}

export function frontendPageEditRules(form, t) {
    const rules = {
        name:            [form.name,            { isRequired: t('validation.frontend_pages.name.required') }],
        slug:            [form.slug,            { isRequired: t('validation.frontend_pages.slug.required') }],
        created_at_date: [form.created_at_date, { isRequired: t('validation.publish_date_required') }],
        content:         [form.content,         { isRequired: t('validation.frontend_pages.content.required') }],
    }
    if (form.type !== 'contactus') {
        rules.url = [form.url, { isRequired: t('validation.frontend_pages.url.required') }]
    }
    return rules
}
