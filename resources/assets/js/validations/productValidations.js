import * as yup from 'yup'
import { reqSelect } from './helpers'

export const productSchema = yup.object({
    name:                yup.string().required(() => __('validation.product.name.required')),
    type:                reqSelect(() => __('validation.product.type.required')),
    group:               reqSelect(() => __('validation.product.group.required')),
    description:         yup.string().required(() => __('validation.product_controller.description_required')),
    short_description:   yup.string().required(() => __('validation.product_controller.short_description_required')),
    product_sku:         yup.string().required(() => __('validation.product_controller.product_sku_required')),
    product_description: yup.string().required(() => __('validation.product_controller.product_description_required')),
})
