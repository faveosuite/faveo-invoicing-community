import { apiKeySchema } from '@/validations/admin/pipedriveValidations'

describe('apiKeySchema', () => {
    it('passes with a valid apiKey', async () => {
        await expect(apiKeySchema.validate({ apiKey: 'pd-api-key-123' })).resolves.toBeTruthy()
    })

    it('fails when apiKey is empty', async () => {
        await expect(apiKeySchema.validate({ apiKey: '' })).rejects.toThrow()
    })

    it('fails when apiKey is missing', async () => {
        await expect(apiKeySchema.validate({})).rejects.toThrow()
    })

    it('fails when apiKey is only whitespace', async () => {
        await expect(apiKeySchema.validate({ apiKey: '   ' })).rejects.toThrow()
    })
})
