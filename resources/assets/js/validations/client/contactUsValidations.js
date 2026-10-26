import * as yup from 'yup'
import { __ } from '@/plugins/i18n'
import { EMAIL_RE } from './authSchemas'

export const contactUsSchema = yup.object({
    name:    yup.string().trim().required(() => __('message.contact_error_name')),
    email:   yup.string().trim()
                .required(() => __('message.error_email_address'))
                .matches(EMAIL_RE, () => __('message.contact_error_email')),
    mobile:  yup.string().trim().required(() => __('message.error_mobile')),
    message: yup.string().trim().required(() => __('message.contact_error_message')),
})
