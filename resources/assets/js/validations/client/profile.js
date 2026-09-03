import * as yup from 'yup'
import { reqSelect } from '../shared/helpers'
import { __ } from '@/plugins/i18n'

export const profileSchema = yup.object({
    first_name: yup.string().required(() => __('validation.users.first_name.required')),
    last_name:  yup.string().required(() => __('validation.users.last_name.required')),
    user_name:  yup.string().required(() => __('validation.users.user_name.required')),
    company:    yup.string().required(() => __('validation.users.company.required')),
    mobile:     yup.string().required(() => __('validation.users.mobile.required')),
    address:    yup.string().required(() => __('validation.users.address.required')),
    country:    reqSelect(() => __('validation.users.country.required')),
})

export const passwordChangeSchema = yup.object({
    current_password:      yup.string().required(() => __('message.old_pass_required')),
    password:              yup.string().required(() => __('message.new_pass_required')),
    password_confirmation: yup.string()
        .required(() => __('message.confirm_pass_required'))
        .oneOf([yup.ref('password')], () => __('message.password_mismatch')),
})
