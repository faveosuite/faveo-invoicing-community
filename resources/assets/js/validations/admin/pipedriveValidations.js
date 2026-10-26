import * as yup from 'yup'

export const apiKeySchema = yup.object({
    // The saved key is never sent back to the page; blank keeps it.
    apiKey: yup.string().trim().when('hasKey', {
        is:        true,
        then:      s => s.notRequired(),
        otherwise: s => s.required(() => __('message.enter_pipedrive_api')),
    }),
})
