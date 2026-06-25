import * as yup from 'yup'

export const deploymentSchema = yup.object({
    install_script_url:       yup.string().trim()
        .required(() => __('message.enter_install_script_url'))
        .url(() => __('message.valid_install_script_url')),
    manual_install_guide_url: yup.string().trim()
        .required(() => __('message.enter_manual_install_guide_url'))
        .url(() => __('message.valid_manual_install_guide_url')),
})
