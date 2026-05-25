import * as yup from 'yup'

export const socialLoginSchema = yup.object({
    client_id:     yup.string().required(() => __('message.field_required')),
    client_secret: yup.string().required(() => __('message.field_required')),
    redirect_url:  yup.string()
        .required(() => __('message.field_required'))
        .url(() => __('message.invalid_url')),
})
