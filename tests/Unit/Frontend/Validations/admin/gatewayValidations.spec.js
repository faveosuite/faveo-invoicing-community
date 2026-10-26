import { buildGatewaySchema } from '@/validations/admin/gatewayValidations'

describe('buildGatewaySchema', () => {
    it('passes when all required fields are present', async () => {
        const schema = buildGatewaySchema([
            { name: 'api_key' },
            { name: 'secret' },
        ])
        await expect(schema.validate({ api_key: 'abc', secret: 'xyz', webhook_secret: 'whsec_test' })).resolves.toBeTruthy()
    })

    it('fails when a required field is empty', async () => {
        const schema = buildGatewaySchema([{ name: 'api_key' }])
        await expect(schema.validate({ api_key: '', webhook_secret: 'whsec_test' })).rejects.toThrow()
    })

    it('fails when a required field is missing', async () => {
        const schema = buildGatewaySchema([{ name: 'api_key' }])
        await expect(schema.validate({ webhook_secret: 'whsec_test' })).rejects.toThrow()
    })

    it('skips fields with required: false', async () => {
        const schema = buildGatewaySchema([
            { name: 'api_key' },
            { name: 'optional_field', required: false },
        ])
        await expect(schema.validate({ api_key: 'abc', webhook_secret: 'whsec_test' })).resolves.toBeTruthy()
    })

    it('handles an empty fields array', async () => {
        const schema = buildGatewaySchema([])
        await expect(schema.validate({ webhook_secret: 'whsec_test' })).resolves.toBeTruthy()
    })

    it('treats field without explicit required as required', async () => {
        const schema = buildGatewaySchema([{ name: 'token' }])
        await expect(schema.validate({ token: 'tok123', webhook_secret: 'whsec_test' })).resolves.toBeTruthy()
        await expect(schema.validate({ token: '', webhook_secret: 'whsec_test' })).rejects.toThrow()
    })

    it('fails when webhook_secret is missing', async () => {
        const schema = buildGatewaySchema([])
        await expect(schema.validate({})).rejects.toThrow()
    })
})
