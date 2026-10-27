import * as yup from 'yup'

export const msg91Schema = yup.object({
    msg91_auth_key:    yup.string().trim().required(() => __('message.mobile_authkey')),
    msg91_sender:      yup.string().trim().required(() => __('message.sender')),
    msg91_template_id: yup.string().trim().required(() => __('message.templateId')),
})
