import * as yup from 'yup'

export const apiKeySchema = yup.object({
    apiKey: yup.string().trim().required(() => __('message.enter_pipedrive_api')),
})
