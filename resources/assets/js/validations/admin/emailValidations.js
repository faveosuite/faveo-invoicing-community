import * as yup from 'yup'

export function buildEmailSettingsSchema(driverId) {
    const shape = {}
    if (driverId === 'smtp') {
        shape.host = yup.string().required(() => __('message.field_required'))
        shape.port = yup.string().required(() => __('message.field_required'))
    }
    if (['mailgun', 'mandrill', 'ses', 'sparkpost'].includes(driverId)) {
        shape.secret = yup.string().required(() => __('message.field_required'))
    }
    if (driverId === 'mailgun') {
        shape.domain = yup.string().required(() => __('message.field_required'))
    }
    if (driverId === 'ses') {
        shape.key    = yup.string().required(() => __('message.field_required'))
        shape.region = yup.string().required(() => __('message.field_required'))
    }
    return yup.object(shape)
}

export const templateEditSchema = yup.object({
    name: yup.string().required(() => __('message.field_required')),
    type: yup.string().required(() => __('message.field_required')),
    data: yup.string().required(() => __('message.field_required')),
})
