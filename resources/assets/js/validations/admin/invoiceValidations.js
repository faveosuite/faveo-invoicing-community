import * as yup from 'yup'
import { reqSelect } from '../shared/helpers'

export function buildInvoiceCreateSchema(dynamic) {
    return yup.object({
        user:    reqSelect(() => __('validation.invoice.user.required')),
        date:    yup.string().required(() => __('validation.invoice.date.required')),
        product: reqSelect(() => __('validation.invoice.product.required')),
        price:   yup.string().required(() => __('validation.invoice.price.required')),
        ...(dynamic.required_domain  && { domain:       yup.string().required(() => __('validation.invoice.domain.required')) }),
        ...(dynamic.is_cloud_product && {
            // Same subdomain-label format the client panel validates
            // (PlanCard.vue's cloud domain field) — must start/end with a
            // letter or digit, hyphens only allowed in between.
            cloud_domain: yup.string()
                .required(() => __('validation.invoice.cloud_domain.required'))
                .matches(/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/i, () => __('validation.invoice.cloud_domain.regex')),
        }),
    })
}

export const invoiceEditSchema = yup.object({
    date: yup.string().required(() => __('validation.invoice.date.required')),
})
