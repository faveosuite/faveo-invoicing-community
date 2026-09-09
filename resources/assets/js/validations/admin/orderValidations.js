import * as yup from 'yup'

export const licenseDetailsSchema = yup.object({
    limit: yup
        .number()
        .typeError(() => __('message.limit_number'))
        .required(() => __('message.limit_number'))
        .min(1, () => __('message.limit_number')),
    update_end:       yup.string().nullable(),
    subscription_end: yup.string().nullable(),
    support_end:      yup.string().nullable(),
})
