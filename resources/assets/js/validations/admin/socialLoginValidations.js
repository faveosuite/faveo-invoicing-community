import * as yup from 'yup'

export const socialLoginSchema = yup.object({
    client_id:     yup.string().required(() => __('message.social_login_client_id_required')),
    client_secret: yup.string().required(() => __('message.social_login_client_secret_required')),
})
