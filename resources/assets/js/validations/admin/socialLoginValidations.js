import * as yup from 'yup'

// Real credentials are only required to actually turn the login ON — saving
// blank is always allowed while it stays Inactive, so admins can clear a
// mistaken value back out (see QA bug #33).
export const socialLoginSchema = yup.object({
    client_id:     yup.string().when('status', {
        is: true,
        then:      (s) => s.required(() => __('message.field_required')),
        otherwise: (s) => s.optional(),
    }),
    client_secret: yup.string().when('status', {
        is: true,
        then:      (s) => s.required(() => __('message.field_required')),
        otherwise: (s) => s.optional(),
    }),
    redirect_url:  yup.string().when('status', {
        is: true,
        then:      (s) => s.required(() => __('message.field_required')).url(() => __('message.invalid_url')),
        otherwise: (s) => s.optional(),
    }),
})
