import * as yup from 'yup'
import { __ } from '@/plugins/i18n'

export const deploySchema = yup.object({
    host: yup.string().trim()
        .required(() => __('message.deploy_host_required')),

    port: yup.number()
        .typeError(() => __('message.deploy_port_invalid'))
        .min(1,     () => __('message.deploy_port_invalid'))
        .max(65535, () => __('message.deploy_port_invalid'))
        .required(  () => __('message.deploy_port_invalid')),

    username: yup.string().trim()
        .required(() => __('message.deploy_username_required')),

    password: yup.string().when('auth_method', {
        is:        'password',
        then:      (s) => s.required(() => __('message.deploy_password_required')),
        otherwise: (s) => s.nullable(),
    }),

    private_key: yup.string().when('auth_method', {
        is:        'private_key',
        then:      (s) => s.trim().required(() => __('message.deploy_private_key_required')),
        otherwise: (s) => s.nullable(),
    }),

    deploy_path: yup.string().when('deploy_mode', {
        is:        'extract_only',
        then:      (s) => s.trim().required(() => __('message.deploy_path_required')),
        otherwise: (s) => s.nullable(),
    }),

    install_domain: yup.string().when('deploy_mode', {
        is:        'fresh_install',
        then:      (s) => s.trim().required(() => __('message.deploy_domain_required')),
        otherwise: (s) => s.nullable(),
    }),

    install_email: yup.string().when('deploy_mode', {
        is:        'fresh_install',
        then:      (s) => s.trim()
            .required(() => __('message.deploy_email_required'))
            .email(   () => __('message.deploy_email_invalid')),
        otherwise: (s) => s.nullable(),
    }),

    ssl_cert_path: yup.string().when(['deploy_mode', 'ssl_type'], {
        is:        (mode, ssl) => mode === 'fresh_install' && ssl === 'C',
        then:      (s) => s.trim().required(() => __('message.deploy_cert_path_required')),
        otherwise: (s) => s.nullable(),
    }),

    ssl_key_path: yup.string().when(['deploy_mode', 'ssl_type'], {
        is:        (mode, ssl) => mode === 'fresh_install' && ssl === 'C',
        then:      (s) => s.trim().required(() => __('message.deploy_key_path_required')),
        otherwise: (s) => s.nullable(),
    }),
})
