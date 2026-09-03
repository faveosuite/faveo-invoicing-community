import * as yup from 'yup'
import { reqSelect } from '../shared/helpers'

export const productSchema = yup.object({
    name:                yup.string().required(() => __('validation.product.name.required')),
    type:                reqSelect(() => __('validation.product.type.required')),
    product_type:        yup.string().required(() => __('validation.product.product_type.required')),
    group:               reqSelect(() => __('validation.product.group.required')),
    description:         yup.string().required(() => __('validation.product_controller.description_required')),
    short_description:   yup.string().required(() => __('validation.product_controller.short_description_required')),
    product_sku:         yup.string().required(() => __('validation.product_controller.product_sku_required')),
    product_description: yup.string().required(() => __('validation.product_controller.product_description_required')),
    // Only ProductEdit.vue has this field (auto-defaulted there); neither backend
    // endpoint (productCreate/updateProduct) requires it — required() here just
    // silently blocked every Create submission since that form has no such field.
    shoping_cart_link:   yup.string().optional(),
    github_owner:        yup.string().when('file_source', {
        is: 'github',
        then: (s) => s.required(() => __('validation.product.github_owner.required')),
        otherwise: (s) => s.optional(),
    }),
    github_repository:   yup.string().when('file_source', {
        is: 'github',
        then: (s) => s.required(() => __('validation.product.github_repository.required')),
        otherwise: (s) => s.optional(),
    }),
})
