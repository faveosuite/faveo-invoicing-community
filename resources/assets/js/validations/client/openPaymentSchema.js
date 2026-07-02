import * as yup from 'yup'
import { __ } from '@/plugins/i18n'

export const openPaymentSchema = yup.object({
    name:    yup.string().required(() => __('message.open_payment.name_required')).max(100, () => __('message.open_payment.name_max')),
    email:   yup.string().required(() => __('message.open_payment.email_required')).email(() => __('message.open_payment.email_invalid')),
    mobile:  yup.string().required(() => __('message.open_payment.mobile_required')).min(8, () => __('message.open_payment.mobile_min')),
    company: yup.string().required(() => __('message.open_payment.company_required')),
    address: yup.string().required(() => __('message.open_payment.address_required')),
    city:    yup.string().required(() => __('message.open_payment.city_required')),
    state:   yup.string().required(() => __('message.open_payment.state_required')),
    zip:     yup.string().required(() => __('message.open_payment.zip_required')).max(15, () => __('message.open_payment.zip_max')),
    country: yup.string().required(() => __('message.open_payment.country_required')),
    amount:  yup.number()
        .typeError(() => __('message.open_payment.amount_invalid'))
        .required(() => __('message.open_payment.amount_required'))
        .min(1, () => __('message.open_payment.amount_min')),
})
