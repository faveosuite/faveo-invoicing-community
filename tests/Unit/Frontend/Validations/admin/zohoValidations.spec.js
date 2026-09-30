import { zohoCredentialsSchema } from '@/validations/admin/zohoValidations'

describe('zohoCredentialsSchema', () => {
    const valid = {
        client_id:     'zoho-client-id',
        client_secret: 'zoho-client-secret',
        region:        'US',
    }

    it('passes with valid data', async () => {
        await expect(zohoCredentialsSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when client_id is empty', async () => {
        await expect(zohoCredentialsSchema.validate({ ...valid, client_id: '' })).rejects.toThrow()
    })

    it('fails when client_id is missing', async () => {
        const { client_id: _o, ...rest } = valid // NOSONAR
        await expect(zohoCredentialsSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when client_id is only whitespace', async () => {
        await expect(zohoCredentialsSchema.validate({ ...valid, client_id: '   ' })).rejects.toThrow()
    })

    it('fails when client_secret is empty', async () => {
        await expect(zohoCredentialsSchema.validate({ ...valid, client_secret: '' })).rejects.toThrow()
    })

    it('fails when client_secret is only whitespace', async () => {
        await expect(zohoCredentialsSchema.validate({ ...valid, client_secret: '   ' })).rejects.toThrow()
    })

    it('fails when region is missing', async () => {
        const { region: _o, ...rest } = valid // NOSONAR
        await expect(zohoCredentialsSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when region is null', async () => {
        await expect(zohoCredentialsSchema.validate({ ...valid, region: null })).rejects.toThrow()
    })
})
