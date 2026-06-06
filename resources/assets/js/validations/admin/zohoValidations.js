import * as yup from 'yup'

export const zohoCredentialsSchema = yup.object({
    client_id:     yup.string().trim().required(() => __('message.zoho_client_id_required')),
    client_secret: yup.string().trim().required(() => __('message.zoho_client_secret_required')),
    region:        yup.string().nullable().required(() => __('message.zoho_region_required')),
})
