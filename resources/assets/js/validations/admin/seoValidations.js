import * as yup from 'yup'

export const seoDefaultPageSchema = yup.object({
    meta_title:       yup.string().nullable(),
    meta_description: yup.string().nullable(),
    og_title:         yup.string().nullable(),
    og_description:   yup.string().nullable(),
})
