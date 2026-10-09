import * as yup from 'yup'

export const licenseTypeCreateSchema = yup.object({
    license_type_name: yup.string().required(() => __('message.field_required')),
})

export const licenseTypeEditSchema = yup.object({
    license_type_edit_name: yup.string().required(() => __('message.field_required')),
})
