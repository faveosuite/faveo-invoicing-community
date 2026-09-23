import { socialLoginSchema } from '@/validations/admin/socialLoginValidations'

describe('socialLoginSchema', () => {
    const valid = {
        client_id:     'google-client-id',
        client_secret: 'google-client-secret',
        redirect_url:  'https://example.com/auth/callback',
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

    it('fails when redirect_url is empty', async () => {
        await expect(socialLoginSchema.validate({ ...valid, redirect_url: '' })).rejects.toThrow()
    })

    it('fails when redirect_url is not a valid URL', async () => {
        await expect(socialLoginSchema.validate({ ...valid, redirect_url: 'not-a-url' })).rejects.toThrow()
    })

    it('fails when redirect_url is missing', async () => {
        const { redirect_url: _o, ...rest } = valid // NOSONAR
        await expect(socialLoginSchema.validate(rest)).rejects.toThrow()
    })
})
