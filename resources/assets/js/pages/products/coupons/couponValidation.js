export function couponRules(form, t) {
    return {
        code:    [form.code,    { isRequired: t('validation.coupon_form.code.required') }],
        type:    [form.type,    { isRequired: t('validation.coupon_form.type.required') }],
        value:   [form.value,   { isRequired: t('validation.coupon_form.value.required') }],
        applied: [form.applied, { isRequired: t('validation.coupon_form.applied.required') }],
        uses:    [form.uses,    { isRequired: t('validation.coupon_form.uses.required') }],
        start:   [form.start,   { isRequired: t('validation.coupon_form.start.required') }],
        expiry:  [form.expiry,  { isRequired: t('validation.coupon_form.expiry.required') }],
    }
}
