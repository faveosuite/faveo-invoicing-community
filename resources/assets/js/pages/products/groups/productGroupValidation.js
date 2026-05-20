export function productGroupRules(form, t) {
    return {
        name:                 [form.name,                 { isRequired: t('validation.group.name.required') }],
        pricing_templates_id: [form.pricing_templates_id, 'isRequired'],
    }
}
