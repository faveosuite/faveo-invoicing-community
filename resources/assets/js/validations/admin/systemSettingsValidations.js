import * as yup from 'yup'
import { reqSelect } from '../shared/helpers'

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

export const webhookUrlSchema = yup.object({
    editWebhookUrl: yup.string()
        .required(() => __('message.field_required'))
        .url(() => __('message.invalid_url')),
})
