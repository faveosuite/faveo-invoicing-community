import * as yup from 'yup'
import { reqSelect } from '../shared/helpers'

export const productGroupSchema = yup.object({
    name:                 yup.string().required(() => __('validation.group.name.required')),
    pricing_templates_id: reqSelect(() => __('validation.group.pricing_templates_id.required')),
})
