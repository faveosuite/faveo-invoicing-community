import * as yup from 'yup'

export const licenseSchema = yup.object({
    product:              yup.string().required(() => __('validation.license.product.required')),
    client:               yup.string().nullable(),
    license_code:         yup.string().required(() => __('validation.license.license_code.required')),
    license_expire_date:  yup.mixed().nullable().test('required', () => __('validation.license.license_expire_date.required'), val => val !== null && val !== undefined && val !== ''),
    license_updates_date: yup.mixed().nullable().test('required', () => __('validation.license.license_updates_date.required'), val => val !== null && val !== undefined && val !== ''),
    license_support_date: yup.mixed().nullable().test('required', () => __('validation.license.license_support_date.required'), val => val !== null && val !== undefined && val !== ''),
})

// IPv4 (octet-bounded) or IPv6 (loose, backend does the strict check via Laravel's `ip` rule)
const IP_ADDRESS_REGEX = /^(?:(?:25[0-5]|2[0-4]\d|1\d{2}|[1-9]?\d)\.){3}(?:25[0-5]|2[0-4]\d|1\d{2}|[1-9]?\d)$|^(?:[0-9a-fA-F]{0,4}:){2,7}[0-9a-fA-F]{0,4}$/

export const bannedHostSchema = yup.object({
    banned_host_ip: yup.string()
        .required(() => __('validation.license.banned_host_ip.required'))
        .matches(IP_ADDRESS_REGEX, { message: () => __('validation.license.banned_host_ip.invalid'), excludeEmptyString: true }),
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
