import * as yup from 'yup'

export const deploymentSchema = yup.object({
    deployment_enabled:       yup.boolean(),
    install_script_url:       yup.string().trim()
        .when('deployment_enabled', {
            is:   true,
            then: schema => schema
                .required(() => __('message.enter_install_script_url'))
                .url(() => __('message.valid_install_script_url')),
            otherwise: schema => schema.nullable().notRequired(),
        }),
    manual_install_guide_url: yup.string().trim()
        .when('deployment_enabled', {
            is:   true,
            then: schema => schema
                .required(() => __('message.enter_manual_install_guide_url'))
                .url(() => __('message.valid_manual_install_guide_url')),
            otherwise: schema => schema.nullable().notRequired(),
        }),
})
