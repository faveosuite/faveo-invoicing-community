export function taxCreateRules(form, t) {
    const rules = {
        name:       [form.name,        { isRequired: t('validation.tax_form.name.required') }],
        'tax-name': [form['tax-name'], { isRequired: t('validation.tax_form.name.required') }],
    }
    if (form.name === 'Others') {
        rules.rate = [form.rate, { isRequired: t('validation.tax_form.rate.required') }]
    }
    return rules
}

export function taxEditRules(form, t) {
    const rules = {
        name:           [form.name,           { isRequired: t('validation.tax_form.name.required') }],
        tax_classes_id: [form.tax_classes_id, { isRequired: t('validation.tax_form.name.required') }],
    }
    if (form.tax_classes_id === 'Others') {
        rules.rate = [form.rate, { isRequired: t('validation.tax_form.rate.required') }]
    }
    return rules
}
