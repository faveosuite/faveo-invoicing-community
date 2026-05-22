import * as yup from 'yup'

export function buildFrontendPageCreateSchema(type) {
    const shape = {
        name:    yup.string().required(() => __('validation.frontend_pages.name.required')),
        slug:    yup.string().required(() => __('validation.frontend_pages.slug.required')),
        content: yup.string().required(() => __('validation.frontend_pages.content.required')),
    }
    if (type !== 'contactus') {
        shape.url = yup.string().required(() => __('validation.frontend_pages.url.required'))
    }
    return yup.object(shape)
}

export function buildFrontendPageEditSchema(type) {
    const shape = {
        name:            yup.string().required(() => __('validation.frontend_pages.name.required')),
        slug:            yup.string().required(() => __('validation.frontend_pages.slug.required')),
        created_at_date: yup.string().required(() => __('validation.publish_date_required')),
        content:         yup.string().required(() => __('validation.frontend_pages.content.required')),
    }
    if (type !== 'contactus') {
        shape.url = yup.string().required(() => __('validation.frontend_pages.url.required'))
    }
    return yup.object(shape)
}
