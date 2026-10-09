import * as yup from 'yup'

export const productGroupSchema = yup.object({
    name:                 yup.string().required(() => __('validation.group.name.required')),
    meta_title:           yup.string().nullable(),
    meta_description:     yup.string().nullable(),
    og_title:             yup.string().nullable(),
    og_description:       yup.string().nullable(),
})
