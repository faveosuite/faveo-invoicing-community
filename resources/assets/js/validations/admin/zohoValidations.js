import * as yup from 'yup'

export const zohoCredentialsSchema = yup.object({
    client_id:     yup.string().required(() => __('message.field_required')),
    client_secret: yup.string().required(() => __('message.field_required')),
    region:        yup.string().required(() => __('message.field_required')),
})
