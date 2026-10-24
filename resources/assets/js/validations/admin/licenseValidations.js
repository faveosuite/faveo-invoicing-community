import * as yup from 'yup'

export const licenseSchema = yup.object({
    product:              yup.string().required(() => __('validation.license.product.required')),
    client:               yup.string().nullable(),
    license_code:         yup.string().required(() => __('validation.license.license_code.required')),
    // The three expiry dates are optional: empty means it never expires (lifetime licenses).
})

// IPv4 (octet-bounded) or IPv6 (loose, backend does the strict check via Laravel's `ip` rule)
const OCTET = /(?:25[0-5]|2[0-4]\d|1\d{2}|[1-9]?\d)/.source
const IPV4_REGEX = new RegExp(`^(?:${OCTET}\\.){3}${OCTET}$`)
const IPV6_REGEX = /^(?:[0-9a-fA-F]{0,4}:){2,7}[0-9a-fA-F]{0,4}$/
const isIpAddress = value => !value || IPV4_REGEX.test(value) || IPV6_REGEX.test(value)

export const bannedHostSchema = yup.object({
    banned_host_ip: yup.string()
        .required(() => __('validation.license.banned_host_ip.required'))
        .test('ip', () => __('validation.license.banned_host_ip.invalid'), isIpAddress),
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
