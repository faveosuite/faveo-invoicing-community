import * as yup from 'yup'

export function buildGatewaySchema(fields) {
    const shape = {}
    fields
        .filter(f => f.required !== false)
        .forEach(f => {
            shape[f.name] = yup.string().required(() => __('message.field_required'))
        })
    return yup.object(shape)
}
