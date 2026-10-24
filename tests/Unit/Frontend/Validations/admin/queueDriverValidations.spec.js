// queueDriverValidations.js calls __() eagerly at module load time (not lazily).
// Jest hoists imports above normal statements, so we use jest.mock to inject
// the global before the module is first evaluated.
jest.mock('@/validations/admin/queueDriverValidations', () => {
    globalThis.__ = (key) => key
    return jest.requireActual('@/validations/admin/queueDriverValidations')
})

import { queueDriverSchemas } from '@/validations/admin/queueDriverValidations'

describe('queueDriverSchemas.redis', () => {
    const schema = queueDriverSchemas.redis

    it('passes with valid data', async () => {
        await expect(schema.validate({ driver: 'redis', queue: 'default' })).resolves.toBeTruthy()
    })

    it('fails when driver is empty', async () => {
        await expect(schema.validate({ driver: '', queue: 'default' })).rejects.toThrow()
    })

    it('fails when queue is empty', async () => {
        await expect(schema.validate({ driver: 'redis', queue: '' })).rejects.toThrow()
    })

    it('fails when driver is missing', async () => {
        await expect(schema.validate({ queue: 'default' })).rejects.toThrow()
    })
})

describe('queueDriverSchemas.beanstalkd', () => {
    const schema = queueDriverSchemas.beanstalkd
    const valid = { driver: 'beanstalkd', host: 'localhost', queue: 'default' }

    it('passes with valid data', async () => {
        await expect(schema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when host is empty', async () => {
        await expect(schema.validate({ ...valid, host: '' })).rejects.toThrow()
    })

    it('fails when host is missing', async () => {
        const { host: _o, ...rest } = valid // NOSONAR
        await expect(schema.validate(rest)).rejects.toThrow()
    })

    it('fails when queue is empty', async () => {
        await expect(schema.validate({ ...valid, queue: '' })).rejects.toThrow()
    })
})

describe('queueDriverSchemas.sqs', () => {
    const schema = queueDriverSchemas.sqs
    const valid = { driver: 'sqs', key: 'AKID', secret: 'secret', region: 'us-east-1' }

    it('passes with valid data', async () => {
        await expect(schema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when key is empty', async () => {
        await expect(schema.validate({ ...valid, key: '' })).rejects.toThrow()
    })

    it('fails when secret is empty', async () => {
        await expect(schema.validate({ ...valid, secret: '' })).rejects.toThrow()
    })

    it('fails when region is empty', async () => {
        await expect(schema.validate({ ...valid, region: '' })).rejects.toThrow()
    })
})

describe('queueDriverSchemas.iron', () => {
    const schema = queueDriverSchemas.iron
    const valid = { driver: 'iron', host: 'mq-aws-us-east-1.iron.io', token: 'tok', project: 'proj', queue: 'default' }

    it('passes with valid data', async () => {
        await expect(schema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when token is empty', async () => {
        await expect(schema.validate({ ...valid, token: '' })).rejects.toThrow()
    })

    it('fails when project is empty', async () => {
        await expect(schema.validate({ ...valid, project: '' })).rejects.toThrow()
    })

    it('fails when queue is empty', async () => {
        await expect(schema.validate({ ...valid, queue: '' })).rejects.toThrow()
    })
})
