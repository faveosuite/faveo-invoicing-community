export function gatewayFieldRules(fields, form, t) {
    const rules = {}
    fields
        .filter(f => f.required !== false)
        .forEach(f => {
            rules[f.name] = [form[f.name], { isRequired: t('message.field_required') }]
        })
    return rules
}
