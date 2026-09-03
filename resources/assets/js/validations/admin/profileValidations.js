import * as yup from 'yup'
import { reqSelect } from '../shared/helpers'
import { STRONG_PASS } from '../client/authSchemas'

const GSTIN_REGEX = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/

export const profileSchema = yup.object({
    first_name:  yup.string().required(() => __('validation.users.first_name.required')),
    last_name:   yup.string().required(() => __('validation.users.last_name.required')),
    user_name:   yup.string().required(() => __('validation.users.user_name.required')),
    company:     yup.string().required(() => __('validation.users.company.required')),
    mobile:      yup.string().required(() => __('validation.users.mobile.required')),
    address:     yup.string().required(() => __('validation.users.address.required')),
    timezone_id: reqSelect(() => __('validation.users.timezone_id.required')),
    country:     reqSelect(() => __('validation.users.country.required')),
    state:       yup.string().when('has_states', {
        is:        true,
        then:      (s) => s.required(() => __('validation.users.state.required_if')),
        otherwise: (s) => s.optional(),
    }),
    gstin:       yup.string().matches(GSTIN_REGEX, {
        message:           () => __('validation.users.gstin.regex'),
        excludeEmptyString: true,
    }),
})

export const passwordChangeSchema = yup.object({
    old_password:     yup.string().required(() => __('message.field_required'))
                           .min(6, () => __('validation.profile_form.old_password.min')),
    new_password:      yup.string().required(() => __('message.field_required'))
                           .matches(STRONG_PASS, () => __('message.strong_password'))
                           .notOneOf([yup.ref('old_password')], () => __('validation.profile_form.new_password.different')),
    confirm_password: yup.string().required(() => __('message.field_required'))
                           .oneOf([yup.ref('new_password')], () => __('message.login_validation.confirm_password_equalto')),
})
