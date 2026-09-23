import * as yup from 'yup'
import { reqSelect } from '../shared/helpers'
import { __ } from '@/plugins/i18n'

// Mirrors the client-side rules used by the legacy blade auth forms.
export const NAME_RE     = /^[a-zA-Z][a-zA-Z' -]{0,98}$/
export const EMAIL_RE    = /^(?!.*\.\.)[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
export const STRONG_PASS = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[~*!@$#%_+.?:,{ }])[A-Za-z\d~*!@$#%_+.?:,{ }]{8,16}$/
const OTP_RE      = /^\d{6}$/

export const loginSchema = yup.object({
    email_username: yup.string().trim().required(() => __('message.error_email_address')),
    password1:      yup.string().required(() => __('message.login_validation.password_required')),
})

export const registerSchema = yup.object({
    first_name: yup.string().trim()
        .required(() => __('message.login_validation.firstname_required'))
        .matches(NAME_RE, () => __('message.login_validation.firstname_regex')),
    last_name: yup.string().trim()
        .required(() => __('message.login_validation.lastname_required'))
        .matches(NAME_RE, () => __('message.login_validation.lastname_regex')),
    email: yup.string().trim()
        .required(() => __('message.error_email_address'))
        .matches(EMAIL_RE, () => __('message.contact_error_email')),
    company: yup.string().trim().required(() => __('message.login_validation.company_required')),
    address: yup.string().trim().required(() => __('message.login_validation.address_required')),
    country: reqSelect(() => __('message.login_validation.country_required')),
    mobile:  yup.string().trim().required(() => __('message.login_validation.mobile_required')),
    password: yup.string()
        .required(() => __('message.login_validation.password_required'))
        .matches(STRONG_PASS, () => __('message.strong_password')),
    password_confirmation: yup.string()
        .required(() => __('message.login_validation.confirm_password_required'))
        .oneOf([yup.ref('password')], () => __('message.login_validation.confirm_password_equalto')),
})

export const forgotSchema = yup.object({
    email: yup.string().trim()
        .required(() => __('message.error_email_address'))
        .matches(EMAIL_RE, () => __('message.contact_error_email')),
})

export const resetSchema = yup.object({
    password: yup.string()
        .required(() => __('message.login_validation.password_required'))
        .matches(STRONG_PASS, () => __('message.strong_password')),
    password_confirmation: yup.string()
        .required(() => __('message.login_validation.confirm_password_required'))
        .oneOf([yup.ref('password')], () => __('message.login_validation.confirm_password_equalto')),
})

export const otpSchema = yup.object({
    otp: yup.string()
        .required(() => __('message.otp_required'))
        .matches(OTP_RE, () => __('message.otp_must_be_6_digits')),
})

export const twoFaSchema = yup.object({
    totp: yup.string()
        .required(() => __('message.otp_required'))
        .matches(OTP_RE, () => __('message.otp_must_be_6_digits')),
})

export const recoverySchema = yup.object({
    rec_code: yup.string().trim().required(() => __('validation.please_enter_recovery_code')),
})

// Live password-requirement checklist (used by Register/Reset pages).
export const passwordChecks = (pwd = '') => ({
    length:  pwd.length >= 8 && pwd.length <= 16,
    lower:   /[a-z]/.test(pwd),
    upper:   /[A-Z]/.test(pwd),
    number:  /\d/.test(pwd),
    special: /[~*!@$#%_+.?:,{ }]/.test(pwd),
})
