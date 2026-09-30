import * as yup from 'yup'

const seoFields = {
    meta_title:       yup.string().nullable(),
    meta_description: yup.string().nullable(),
    og_title:         yup.string().nullable(),
    og_description:   yup.string().nullable(),
}

// URL is optional: custom pages render by slug via the SPA page view, and
// contact-us pages have their URL auto-filled. (type kept for signature parity.)
export function buildFrontendPageCreateSchema(_type) {
    return yup.object({
        name:    yup.string().required(() => __('validation.frontend_pages.name.required')),
        slug:    yup.string().required(() => __('validation.frontend_pages.slug.required')),
        content: yup.string().required(() => __('validation.frontend_pages.content.required')),
        ...seoFields,
    })
}

export function buildFrontendPageEditSchema(_type) {
    return yup.object({
        name:            yup.string().required(() => __('validation.frontend_pages.name.required')),
        slug:            yup.string().required(() => __('validation.frontend_pages.slug.required')),
        created_at_date: yup.string().required(() => __('validation.publish_date_required')),
        content:         yup.string().required(() => __('validation.frontend_pages.content.required')),
        ...seoFields,
    })
}
