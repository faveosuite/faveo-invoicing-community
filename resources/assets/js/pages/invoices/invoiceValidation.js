export function invoiceCreateRules(form, t) {
    return {
        user:    [form.user,    { isRequired: t('validation.invoice.user.required') }],
        date:    [form.date,    { isRequired: t('validation.invoice.date.required') }],
        product: [form.product, { isRequired: t('validation.invoice.product.required') }],
        price:   [form.price,   { isRequired: t('validation.invoice.price.required') }],
    }
}

export function invoiceEditRules(form, t) {
    return {
        date: [form.date, { isRequired: t('validation.invoice.date.required') }],
    }
}
