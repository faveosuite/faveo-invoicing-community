import * as yup from 'yup'

export const analyticsSchema = yup.object({
    name:   yup.string().required(() => __('message.field_required')),
    script: yup.string().required(() => __('message.field_required')),
})

export const socialMediaSchema = yup.object({
    name: yup.string().required(() => __('validation.social_media_form.name.required')),
    link: yup.string()
        .required(() => __('validation.social_media_form.link.required'))
        .url(() => __('validation.social_media_form.link.url')),
    class: yup.string().required(() => __('validation.social_media_form.class.required')),
    fa_class: yup.string().required(() => __('validation.social_media_form.fa_class.required')),
})

export const footerWidgetSchema = yup.object({
    name: yup.string().required(() => __('message.field_required')),
})
