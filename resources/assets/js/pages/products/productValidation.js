export function productRules(form, t) {
    return {
        name:                [form.name,                { isRequired: t('validation.product.name.required') }],
        type:                [form.type,                { isRequired: t('validation.product.type.required') }],
        group:               [form.group,               { isRequired: t('validation.product.group.required') }],
        description:         [form.description,         { isRequired: t('validation.product_controller.description_required') }],
        short_description:   [form.short_description,   'isRequired'],
        product_sku:         [form.product_sku,         { isRequired: t('validation.product_controller.product_sku_required') }],
        product_description: [form.product_description, { isRequired: t('validation.product_controller.product_description_required') }],
    }
}
