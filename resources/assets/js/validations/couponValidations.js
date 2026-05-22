import * as yup from 'yup'
import { reqSelect } from './helpers'

export const couponSchema = yup.object({
    code:    yup.string().required(() => __('validation.coupon_form.code.required')),
    type:    reqSelect(() => __('validation.coupon_form.type.required')),
    value:   yup.string().required(() => __('validation.coupon_form.value.required')),
    applied: reqSelect(() => __('validation.coupon_form.applied.required')),
    uses:    yup.string().required(() => __('validation.coupon_form.uses.required')),
    start:   yup.string().required(() => __('validation.coupon_form.start.required')),
    expiry:  yup.string().required(() => __('validation.coupon_form.expiry.required')),
})
