import * as yup from 'yup'

export const termsSchema = yup.object({
    terms_url: yup.string()
        .trim()
        .required(() => __('message.enter_terms_url'))
        .url(() => __('message.terms_url_s')),
})
