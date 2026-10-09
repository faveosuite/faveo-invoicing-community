import { socialLoginSchema } from '@/validations/admin/socialLoginValidations'

describe('socialLoginSchema', () => {
    const valid = {
        client_id:     'google-client-id',
        client_secret: 'google-client-secret',
    }

    it('passes with valid data', async () => {
        await expect(socialLoginSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when client_id is empty', async () => {
        await expect(socialLoginSchema.validate({ ...valid, client_id: '' })).rejects.toThrow()
    })

    it('fails when client_id is missing', async () => {
        const { client_id: _o, ...rest } = valid // NOSONAR
        await expect(socialLoginSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when client_secret is empty', async () => {
        await expect(socialLoginSchema.validate({ ...valid, client_secret: '' })).rejects.toThrow()
    })

    it('fails when client_secret is missing', async () => {
        const { client_secret: _o, ...rest } = valid // NOSONAR
        await expect(socialLoginSchema.validate(rest)).rejects.toThrow()
    })
})
