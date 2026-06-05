import * as yup from 'yup'
import { reqSelect } from '../shared/helpers'
import { __ } from '@/plugins/i18n'

const DOMAIN_RE = /^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/i

export const cloudTrialSchema = yup.object({
    domain:             yup.string().trim()
                           .required(() => __('message.domain_required'))
                           .matches(DOMAIN_RE, () => __('message.domain_invalid')),
    selectedProduct:    reqSelect(() => __('message.select_product_required')),
    selectedDataCenter: reqSelect(() => __('message.select_data_center_required')),
})
