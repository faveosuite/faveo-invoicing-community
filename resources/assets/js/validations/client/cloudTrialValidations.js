import * as yup from 'yup'
import { reqSelect } from '../shared/helpers'
import { __ } from '@/plugins/i18n'

const DOMAIN_RE = /^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/i

// selectedProduct/selectedDataCenter are only required when the API actually
// returned options for them — their <select> doesn't render on an empty list,
// so requiring a value there would make the form permanently unsubmittable.
export function buildCloudTrialSchema(dynamic) {
    return yup.object({
        domain: yup.string().trim()
                   .required(() => __('message.domain_required'))
                   .matches(DOMAIN_RE, () => __('message.domain_invalid')),
        ...(dynamic.hasProducts    && { selectedProduct:    reqSelect(() => __('message.select_product_required')) }),
        ...(dynamic.hasDataCenters && { selectedDataCenter: reqSelect(() => __('message.select_data_center_required')) }),
    })
}
