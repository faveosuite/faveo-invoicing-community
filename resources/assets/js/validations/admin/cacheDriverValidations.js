import * as yup from 'yup'

const required = (key) => yup.string().trim().required(() => __(`message.${key}_required`))
const port      = (key) => yup.number().typeError(() => __('message.port_must_be_number'))
                               .required(() => __(`message.${key}_required`))
                               .min(1).max(65535)

export const cacheDriverSchemas = {
    redis: yup.object({
        REDIS_HOST: required('redis_host'),
        REDIS_PORT: port('redis_port'),
    }),

    memcached: yup.object({
        MEMCACHED_HOST: required('memcached_host'),
        MEMCACHED_PORT: port('memcached_port'),
    }),

    dynamodb: yup.object({
        AWS_ACCESS_KEY_ID:     required('aws_key_id'),
        AWS_SECRET_ACCESS_KEY: required('aws_secret'),
        AWS_DEFAULT_REGION:    required('aws_region'),
        DYNAMODB_CACHE_TABLE:  required('dynamodb_table'),
    }),
}
