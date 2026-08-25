import * as yup from 'yup'
import { reqSelect } from '../shared/helpers'

const GSTIN_REGEX = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/

export const systemSettingsSchema = yup.object({
    company: yup.string()
        .required(() => __('validation.users.company.required'))
        .max(50, () => __('message.field_required')),
    company_email: yup.string()
        .required(() => __('validation.users.email.required'))
        .email(() => __('message.invalid_email')),
    website: yup.string()
        .required(() => __('message.field_required'))
        .url(() => __('message.invalid_url')),
    phone:            yup.string().required(() => __('message.field_required')),
    address:          yup.string().required(() => __('validation.users.address.required')),
    country:          reqSelect(() => __('validation.users.country.required')),
    state:            reqSelect(() => __('message.field_required')),
    default_currency: reqSelect(() => __('message.field_required')),
    gstin:            yup.string().matches(GSTIN_REGEX, {
        message:            () => __('validation.settings_forms.gstin.regex'),
        excludeEmptyString: true,
    }),
})

export function buildFileStorageSchema(disk) {
    if (disk === 'system') {
        return yup.object({
            path: yup.string().required(() => __('validation.storage_path.path.required')),
        })
    }
    return yup.object({
        s3_bucket:       yup.string().required(() => __('message.field_required')),
        s3_region:       yup.string().required(() => __('message.field_required')),
        s3_access_key:   yup.string().required(() => __('message.field_required')),
        s3_secret_key:   yup.string().required(() => __('message.field_required')),
        s3_endpoint_url: yup.string().required(() => __('message.field_required')),
    })
}

export const pdfSettingsSchema = yup.object({
    node_path:   yup.string().required(() => __('validation.pdf_settings.node_path.required')).typeError(() => __('validation.pdf_settings.node_path.string')),
    npm_path:    yup.string().required(() => __('validation.pdf_settings.npm_path.required')).typeError(() => __('validation.pdf_settings.npm_path.string')),
    chrome_path: yup.string().required(() => __('validation.pdf_settings.chrome_path.required')).typeError(() => __('validation.pdf_settings.chrome_path.string')),
})

export const webhookUrlSchema = yup.object({
    editWebhookUrl: yup.string()
        .required(() => __('message.field_required'))
        .url(() => __('message.invalid_url')),
})
