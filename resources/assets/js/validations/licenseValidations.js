import * as yup from 'yup'

export const licenseSchema = yup.object({
    product:              yup.string().required(() => __('validation.licenses.product.required')),
    client:               yup.string().required(() => __('validation.licenses.client.required')),
    license_code:         yup.string().required(() => __('validation.licenses.license_code.required')),
    license_expire_date:  yup.mixed().required(() => __('validation.licenses.license_expire_date.required')),
    license_updates_date: yup.mixed().required(() => __('validation.licenses.license_updates_date.required')),
    license_support_date: yup.mixed().required(() => __('validation.licenses.license_support_date.required')),
})

export const bannedHostSchema = yup.object({
    banned_host_ip: yup.string().required(() => __('message.field_required')),
})

export const whitelistSchema = yup.object({
    whitelist_host_ip: yup.string().required(() => __('message.field_required')),
})

export const installationSchema = yup.object({
    installation_ip: yup.string().required(() => __('message.field_required')),
})

export function buildNotificationsSchema(fieldNames) {
    const shape = {}
    fieldNames.forEach(name => {
        shape[name] = yup.string()
            .required(() => __('message.field_required'))
            .max(250, 'The word limit should be less than 250 characters.')
    })
    return yup.object(shape)
}
