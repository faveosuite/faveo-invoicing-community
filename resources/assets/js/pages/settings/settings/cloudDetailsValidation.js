export function cloudSettingsRules(form, popup, t) {
    return {
        cloud_central_domain: [form.cloud_central_domain, { isRequired: t('message.field_required') }, { isUrl: t('message.invalid_url') }],
        cloud_cname:          [form.cloud_cname,          { isRequired: t('message.field_required') }],
        cloud_top_message:    [popup.cloud_top_message,   { isRequired: t('message.field_required') }],
        cloud_label_field:    [popup.cloud_label_field,   { isRequired: t('message.field_required') }],
        cloud_label_radio:    [popup.cloud_label_radio,   { isRequired: t('message.field_required') }],
    }
}

export function cloudProductRules(productForm, t) {
    return {
        cloud_product:     [productForm.cloud_product,     { isRequired: t('message.field_required') }],
        cloud_free_plan:   [productForm.cloud_free_plan,   { isRequired: t('message.field_required') }],
        cloud_product_key: [productForm.cloud_product_key, { isRequired: t('message.field_required') }],
    }
}
