import * as yup from 'yup'

const required = (labelKey) => yup.string().trim().required(() => `${__(labelKey)} ${__('message.is_required')}`)

export const queueDriverSchemas = {
    redis: yup.object({
        driver: required('message.driver'),
        queue:  required('message.queue'),
    }),

    beanstalkd: yup.object({
        driver: required('message.driver'),
        host:   required('message.host'),
        queue:  required('message.queue'),
    }),

    sqs: yup.object({
        driver: required('message.driver'),
        key:    required('message.db_key'),
        secret: required('message.secret'),
        region: required('message.region'),
    }),

    iron: yup.object({
        driver:  required('message.driver'),
        host:    required('message.host'),
        token:   required('message.db_token'),
        project: required('message.db_project'),
        queue:   required('message.queue'),
    }),
}
