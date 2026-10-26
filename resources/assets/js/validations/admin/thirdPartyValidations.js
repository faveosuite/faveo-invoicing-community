import * as yup from 'yup'

export const thirdPartyAppSchema = yup.object({
    app_name:   yup.string().required(() => __('message.field_required')),
    app_key:    yup.string().required(() => __('message.field_required')),
    app_secret: yup.string().required(() => __('message.field_required')),
})
