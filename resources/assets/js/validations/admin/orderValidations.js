import * as yup from 'yup'

export const licenseDetailsSchema = yup.object({
    limit: yup
        .number()
        .typeError(() => __('message.limit_number'))
        .required(() => __('message.limit_number'))
        .min(1, () => __('message.limit_number')),
    agents: yup
        .number()
        .typeError(() => __('message.agents_number'))
        .min(0, () => __('message.agents_number'))
        .max(9999, () => __('message.agents_number'))
        .integer(() => __('message.agents_number'))
        .nullable(),
    update_end:       yup.string().nullable(),
    subscription_end: yup.string().nullable(),
    support_end:      yup.string().nullable(),
})
