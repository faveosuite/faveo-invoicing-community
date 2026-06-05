import * as yup from 'yup'
import { __ } from '@/plugins/i18n'

const EMAIL_RE = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/

export const contactUsSchema = yup.object({
    name:    yup.string().trim().required(() => __('message.contact_error_name')),
    email:   yup.string().trim()
                .required(() => __('message.error_email_address'))
                .matches(EMAIL_RE, () => __('message.contact_error_email')),
    mobile:  yup.string().trim().required(() => __('message.error_mobile')),
    message: yup.string().trim().required(() => __('message.contact_error_message')),
})
