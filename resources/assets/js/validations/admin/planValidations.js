import * as yup from 'yup'
import { reqSelect } from '../shared/helpers'

// Mirrors PlanRequest. Agents included in a plan are set in the plan's Features table.
export const planSchema = yup.object({
    name:    yup.string().required(() => __('validation.plan_request.name_required')),
    product: reqSelect(() => __('validation.plan_request.pro_req')),
})
