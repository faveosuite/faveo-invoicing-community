import * as yup from 'yup'
import { reqSelect } from './helpers'

export const userCreateSchema = yup.object({
    first_name:  yup.string().required(() => __('validation.users.first_name.required')),
    last_name:   yup.string().required(() => __('validation.users.last_name.required')),
    email:       yup.string()
                    .required(() => __('validation.users.email.required'))
                    .email(() => __('validation.users.email.email')),
    company:     yup.string().required(() => __('validation.users.company.required')),
    address:     yup.string().required(() => __('validation.users.address.required')),
    mobile:      yup.string().required(() => __('validation.users.mobile.required')),
    country:     reqSelect(() => __('validation.users.country.required')),
    timezone_id: reqSelect(() => __('validation.users.timezone_id.required')),
})

export const userEditSchema = yup.object({
    first_name:  yup.string().required(() => __('validation.users.first_name.required')),
    last_name:   yup.string().required(() => __('validation.users.last_name.required')),
    email:       yup.string()
                    .required(() => __('validation.users.email.required'))
                    .email(() => __('validation.users.email.email')),
    company:     yup.string().required(() => __('validation.users.company.required')),
    address:     yup.string().required(() => __('validation.users.address.required')),
    mobile:      yup.string().required(() => __('validation.users.mobile.required')),
    timezone_id: reqSelect(() => __('validation.users.timezone_id.required')),
})
