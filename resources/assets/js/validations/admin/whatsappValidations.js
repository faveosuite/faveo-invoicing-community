import * as yup from 'yup'

export const whatsappSchema = yup.object({
    app_id:       yup.string().trim().required(() => __('message.app_id_error')),
    app_secret:   yup.string().trim().required(() => __('message.app_secret_error')),
    config_id:    yup.string().trim().required(() => __('message.config_id_error')),
    verify_token: yup.string().trim().required(() => __('message.verify_token_error')),
})
