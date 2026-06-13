import * as yup from 'yup'

const required = (label) => yup.string().trim().required(() => `${label} ${__('message.is_required')}`)

export const queueDriverSchemas = {
    redis: yup.object({
        driver: required(__('message.driver')),
        queue:  required(__('message.queue')),
    }),

    beanstalkd: yup.object({
        driver: required(__('message.driver')),
        host:   required(__('message.host')),
        queue:  required(__('message.queue')),
    }),

    sqs: yup.object({
        driver: required(__('message.driver')),
        key:    required(__('message.db_key')),
        secret: required(__('message.secret')),
        region: required(__('message.region')),
    }),

    iron: yup.object({
        driver:  required(__('message.driver')),
        host:    required(__('message.host')),
        token:   required(__('message.db_token')),
        project: required(__('message.db_project')),
        queue:   required(__('message.queue')),
    }),
}
