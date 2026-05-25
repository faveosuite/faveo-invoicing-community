import * as yup from 'yup'
import { reqSelect } from '../shared/helpers'

export const planSchema = yup.object({
    name:    yup.string().required(() => __('validation.plan_request.name_required')),
    product: reqSelect(() => __('validation.plan_request.pro_req')),
})
