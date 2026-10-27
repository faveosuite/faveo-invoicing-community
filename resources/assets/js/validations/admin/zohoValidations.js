import * as yup from 'yup'

export const zohoCredentialsSchema = yup.object({
    client_id:     yup.string().trim().required(() => __('message.zoho_client_id_required')),
    // The saved secret is never sent back to the page; blank keeps it.
    client_secret: yup.string().trim().when('has_secret', {
        is:        true,
        then:      s => s.notRequired(),
        otherwise: s => s.required(() => __('message.zoho_client_secret_required')),
    }),
    region:        yup.string().nullable().required(() => __('message.zoho_region_required')),
})
