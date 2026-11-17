import * as yup from 'yup'
import { reqSelect } from '../shared/helpers'

export const couponSchema = yup.object({
    code:    yup.string().required(() => __('validation.coupon_form.code.required')),
    type:    reqSelect(() => __('validation.coupon_form.type.required')),
    value:   yup.string().required(() => __('validation.coupon_form.value.required')),
    // Fixed Amount (type 2) is money in one currency.
    currency: yup.string().nullable().when('type', {
        is: (type) => String(type) === '2',
        then: (s) => s.required(() => __('validation.coupon_form.currency.required')),
    }),
    applied: reqSelect(() => __('validation.coupon_form.applied.required')),
    uses:    yup.string().required(() => __('validation.coupon_form.uses.required')),
    start:   yup.string().required(() => __('validation.coupon_form.start.required')),
    expiry:  yup.string().required(() => __('validation.coupon_form.expiry.required')),
})
