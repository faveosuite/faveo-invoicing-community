import * as yup from 'yup'

export function buildGatewaySchema(fields) {
    const shape = {}
    fields.forEach(f => {
        shape[f.name] = f.type === 'number'
            // yup's built-in cast turns '' (and any non-numeric text) into
            // NaN before this runs; fold that into undefined so an empty/bad
            // value fails .required() with a plain "required" message.
            ? yup.number()
                .transform(v => (Number.isNaN(v) ? undefined : v))
                .min(0, () => __('message.processing_fee_invalid'))
                .max(100, () => __('message.processing_fee_invalid'))
                .required(() => __('message.field_required'))
            : yup.string().required(() => __('message.field_required'))
    })

    // webhook_secret is always rendered (outside the gateway-specific `fields`
    // list) and is required for every gateway, same as the rest.
    shape.webhook_secret = yup.string().required(() => __('message.field_required'))

    return yup.object(shape)
}
