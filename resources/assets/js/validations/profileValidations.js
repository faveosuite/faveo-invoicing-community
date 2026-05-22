import * as yup from 'yup'
import { reqSelect } from './helpers'

export const profileSchema = yup.object({
    first_name:  yup.string().required(() => __('validation.users.first_name.required')),
    last_name:   yup.string().required(() => __('validation.users.last_name.required')),
    user_name:   yup.string().required(() => __('validation.users.user_name.required')),
    company:     yup.string().required(() => __('validation.users.company.required')),
    mobile:      yup.string().required(() => __('validation.users.mobile.required')),
    address:     yup.string().required(() => __('validation.users.address.required')),
    timezone_id: reqSelect(() => __('validation.users.timezone_id.required')),
    country:     reqSelect(() => __('validation.users.country.required')),
})

export const passwordChangeSchema = yup.object({
    old_password:     yup.string().required(() => __('message.field_required')),
    new_password:     yup.string().required(() => __('message.field_required')),
    confirm_password: yup.string().required(() => __('message.field_required')),
})
