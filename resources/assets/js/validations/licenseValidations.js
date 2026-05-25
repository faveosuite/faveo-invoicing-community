import * as yup from 'yup'

export const licenseSchema = yup.object({
    product:              yup.string().required(() => __('validation.license.product.required')),
    client:               yup.string().required(() => __('validation.license.client.required')),
    license_code:         yup.string().required(() => __('validation.license.license_code.required')),
    license_expire_date:  yup.mixed().nullable().test('required', () => __('validation.license.license_expire_date.required'), val => val !== null && val !== undefined && val !== ''),
    license_updates_date: yup.mixed().nullable().test('required', () => __('validation.license.license_updates_date.required'), val => val !== null && val !== undefined && val !== ''),
    license_support_date: yup.mixed().nullable().test('required', () => __('validation.license.license_support_date.required'), val => val !== null && val !== undefined && val !== ''),
})

export const bannedHostSchema = yup.object({
    banned_host_ip: yup.string().required(() => __('validation.license.banned_host_ip.required')),
})

export const whitelistSchema = yup.object({
    whitelist_host_ip: yup.string().required(() => __('validation.license.whitelist_host_ip.required')),
})

export const installationSchema = yup.object({
    installation_ip: yup.string().required(() => __('validation.license.installation_ip.required')),
})

export function buildNotificationsSchema(fields) {
    const shape = {}
    fields.forEach(field => {
        shape[field] = yup.string().required(() => __('validation.license.notification_field.required'))
    })
    return yup.object(shape)
}
