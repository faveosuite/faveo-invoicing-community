import * as yup from 'yup'

export const connectionSchema = yup.object({
    apiKey: yup.string().trim().required(() => __('message.enter_mailchimp_key')),
})

export const listSchema = yup.object({
    listId:          yup.string().nullable().required(() => __('message.list_id_error')),
    subscribeStatus: yup.string().required(() => __('message.field_required')),
})
