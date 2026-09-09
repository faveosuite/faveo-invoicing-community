import * as yup from 'yup'

export const cloudSettingsSchema = yup.object({
    cloud_central_domain: yup.string()
        .required(() => __('message.field_required'))
        .url(() => __('message.invalid_url')),
    cloud_cname:       yup.string().required(() => __('message.field_required')),
    cloud_top_message: yup.string().required(() => __('message.field_required')),
    cloud_label_field: yup.string().required(() => __('message.field_required')),
    cloud_label_radio: yup.string().required(() => __('message.field_required')),
})

export const cloudProductSchema = yup.object({
    cloud_product:     yup.mixed().nullable().test('required', () => __('message.field_required'), (v) => v != null && v !== ''),
    cloud_free_plan:   yup.mixed().nullable().test('required', () => __('message.field_required'), (v) => v != null && v !== ''),
    cloud_product_key: yup.string().required(() => __('message.field_required')),
})
