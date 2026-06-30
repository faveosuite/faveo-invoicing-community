import { thirdPartyAppSchema } from '@/validations/admin/thirdPartyValidations'

describe('thirdPartyAppSchema', () => {
    const valid = {
        app_name:   'My App',
        app_key:    'key-abc',
        app_secret: 'secret-xyz',
    }

    it('passes with valid data', async () => {
        await expect(thirdPartyAppSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when app_name is empty', async () => {
        await expect(thirdPartyAppSchema.validate({ ...valid, app_name: '' })).rejects.toThrow()
    })

    it('fails when app_name is missing', async () => {
        const { app_name: _o, ...rest } = valid // NOSONAR
        await expect(thirdPartyAppSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when app_key is empty', async () => {
        await expect(thirdPartyAppSchema.validate({ ...valid, app_key: '' })).rejects.toThrow()
    })

    it('fails when app_key is missing', async () => {
        const { app_key: _o, ...rest } = valid // NOSONAR
        await expect(thirdPartyAppSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when app_secret is empty', async () => {
        await expect(thirdPartyAppSchema.validate({ ...valid, app_secret: '' })).rejects.toThrow()
    })

    it('fails when app_secret is missing', async () => {
        const { app_secret: _o, ...rest } = valid // NOSONAR
        await expect(thirdPartyAppSchema.validate(rest)).rejects.toThrow()
    })
})
