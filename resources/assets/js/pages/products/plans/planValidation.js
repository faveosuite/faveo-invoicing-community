export function planRules(form, t) {
    return {
        name:    [form.name,    { isRequired: t('validation.plan_request.name_required') }],
        product: [form.product, { isRequired: t('validation.plan_request.pro_req') }],
    }
}
