import * as yup from 'yup'

export const emailValidationProviderSchema = yup.object({
    apikey: yup.string().trim().required(() => __('message.emailApikey_error')),
})
