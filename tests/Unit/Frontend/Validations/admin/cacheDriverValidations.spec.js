import { cacheDriverSchemas } from '@/validations/admin/cacheDriverValidations'

describe('cacheDriverSchemas.redis', () => {
    const schema = cacheDriverSchemas.redis

    it('passes with valid data', async () => {
        await expect(schema.validate({ REDIS_HOST: 'localhost', REDIS_PORT: 6379 })).resolves.toBeTruthy()
    })

    it('fails when REDIS_HOST is empty', async () => {
        await expect(schema.validate({ REDIS_HOST: '', REDIS_PORT: 6379 })).rejects.toThrow()
    })

    it('fails when REDIS_HOST is missing', async () => {
        await expect(schema.validate({ REDIS_PORT: 6379 })).rejects.toThrow()
    })

    it('fails when REDIS_PORT is missing', async () => {
        await expect(schema.validate({ REDIS_HOST: 'localhost' })).rejects.toThrow()
    })

    it('fails when REDIS_PORT is 0', async () => {
        await expect(schema.validate({ REDIS_HOST: 'localhost', REDIS_PORT: 0 })).rejects.toThrow()
    })

    it('fails when REDIS_PORT is negative', async () => {
        await expect(schema.validate({ REDIS_HOST: 'localhost', REDIS_PORT: -1 })).rejects.toThrow()
    })

    it('fails when REDIS_PORT exceeds 65535', async () => {
        await expect(schema.validate({ REDIS_HOST: 'localhost', REDIS_PORT: 65536 })).rejects.toThrow()
    })

    it('passes with REDIS_PORT at max boundary (65535)', async () => {
        await expect(schema.validate({ REDIS_HOST: 'localhost', REDIS_PORT: 65535 })).resolves.toBeTruthy()
    })

    it('passes with REDIS_PORT at min boundary (1)', async () => {
        await expect(schema.validate({ REDIS_HOST: 'localhost', REDIS_PORT: 1 })).resolves.toBeTruthy()
    })
})

describe('cacheDriverSchemas.memcached', () => {
    const schema = cacheDriverSchemas.memcached

    it('passes with valid data', async () => {
        await expect(schema.validate({ MEMCACHED_HOST: '127.0.0.1', MEMCACHED_PORT: 11211 })).resolves.toBeTruthy()
    })

    it('fails when MEMCACHED_HOST is empty', async () => {
        await expect(schema.validate({ MEMCACHED_HOST: '', MEMCACHED_PORT: 11211 })).rejects.toThrow()
    })

    it('fails when MEMCACHED_HOST is missing', async () => {
        await expect(schema.validate({ MEMCACHED_PORT: 11211 })).rejects.toThrow()
    })

    it('fails when MEMCACHED_PORT is missing', async () => {
        await expect(schema.validate({ MEMCACHED_HOST: '127.0.0.1' })).rejects.toThrow()
    })

    it('fails when MEMCACHED_PORT is 0', async () => {
        await expect(schema.validate({ MEMCACHED_HOST: '127.0.0.1', MEMCACHED_PORT: 0 })).rejects.toThrow()
    })
})

describe('cacheDriverSchemas.dynamodb', () => {
    const schema = cacheDriverSchemas.dynamodb

    const valid = {
        AWS_ACCESS_KEY_ID:     'AKIAIOSFODNN7',
        AWS_SECRET_ACCESS_KEY: 'wJalrXUtnFEMI',
        AWS_DEFAULT_REGION:    'us-east-1',
        DYNAMODB_CACHE_TABLE:  'cache',
    }

    it('passes with valid data', async () => {
        await expect(schema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when AWS_ACCESS_KEY_ID is empty', async () => {
        await expect(schema.validate({ ...valid, AWS_ACCESS_KEY_ID: '' })).rejects.toThrow()
    })

    it('fails when AWS_ACCESS_KEY_ID is missing', async () => {
        const { AWS_ACCESS_KEY_ID: _omit, ...rest } = valid
        await expect(schema.validate(rest)).rejects.toThrow()
    })

    it('fails when AWS_SECRET_ACCESS_KEY is empty', async () => {
        await expect(schema.validate({ ...valid, AWS_SECRET_ACCESS_KEY: '' })).rejects.toThrow()
    })

    it('fails when AWS_DEFAULT_REGION is empty', async () => {
        await expect(schema.validate({ ...valid, AWS_DEFAULT_REGION: '' })).rejects.toThrow()
    })

    it('fails when DYNAMODB_CACHE_TABLE is empty', async () => {
        await expect(schema.validate({ ...valid, DYNAMODB_CACHE_TABLE: '' })).rejects.toThrow()
    })
})
