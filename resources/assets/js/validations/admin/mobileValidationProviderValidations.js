import * as yup from 'yup'

export function buildMobileValidationSchema(form) {
    const shape = {
        apikey: yup.string().trim().required(() => __('message.mobileApikey_error')),
    }
    if (form.provider === 'vonage') {
        shape.apisecret = yup.string().trim().required(() => __('message.mobileApisecret_error'))
    }
    return yup.object(shape)
}
