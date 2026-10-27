import * as yup from 'yup'
import { reqSelect } from '../shared/helpers'

// Mirrors PlanRequest: the product's show_agent (passed in as form.show_agent) picks
// which field the form shows, and that one is required — a hidden leftover value
// of the other doesn't count. Product not known yet: at least one must be filled.
// Both also mirror its 'min:0'.
const filled = (val) => String(val ?? '').trim() !== ''
const nonNegative = (val) => !filled(val) || Number(val) >= 0

function requiredFor(whenShowAgent, other) {
    return function (val) {
        const showAgent = this.parent.show_agent
        if (showAgent === true || showAgent === false) return showAgent !== whenShowAgent || filled(val)
        return filled(val) || filled(this.parent[other])
    }
}

export const planSchema = yup.object({
    name:    yup.string().required(() => __('validation.plan_request.name_required')),
    product: reqSelect(() => __('validation.plan_request.pro_req')),
    no_of_agents: yup.mixed()
        .test('no_of_agents-required', () => __('validation.plan_request.no_agent_req'), requiredFor(true, 'product_quantity'))
        .test('non-negative', () => __('validation.plan_request.non_negative'), nonNegative),
    product_quantity: yup.mixed()
        .test('product_quantity-required', () => __('validation.plan_request.product_quant_req'), requiredFor(false, 'no_of_agents'))
        .test('non-negative', () => __('validation.plan_request.non_negative'), nonNegative),
})
