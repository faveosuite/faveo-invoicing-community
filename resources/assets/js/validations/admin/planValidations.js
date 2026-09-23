import * as yup from 'yup'
import { reqSelect } from '../shared/helpers'

// Mirrors PlanRequest: no_of_agents/product_quantity are each required_without
// the other — at least one must be filled. Both also mirror its 'min:0'.
const filled = (val) => String(val ?? '').trim() !== ''
const nonNegative = (val) => !filled(val) || Number(val) >= 0

export const planSchema = yup.object({
    name:    yup.string().required(() => __('validation.plan_request.name_required')),
    product: reqSelect(() => __('validation.plan_request.pro_req')),
    no_of_agents: yup.mixed()
        .test(
            'no_of_agents-or-product_quantity',
            () => __('validation.plan_request.no_agent_req'),
            function (val) { return filled(val) || filled(this.parent.product_quantity) },
        )
        .test('non-negative', () => __('validation.plan_request.non_negative'), nonNegative),
    product_quantity: yup.mixed()
        .test(
            'product_quantity-or-no_of_agents',
            () => __('validation.plan_request.product_quant_req'),
            function (val) { return filled(val) || filled(this.parent.no_of_agents) },
        )
        .test('non-negative', () => __('validation.plan_request.non_negative'), nonNegative),
})
