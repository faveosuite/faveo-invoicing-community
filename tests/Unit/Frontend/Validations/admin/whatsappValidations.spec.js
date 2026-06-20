import { whatsappSchema } from '@/validations/admin/whatsappValidations'

describe('whatsappSchema', () => {
    const valid = {
        app_id:       'app-id-123',
        app_secret:   'app-secret-xyz',
        config_id:    'config-abc',
        verify_token: 'verify-tok',
    }

    it('passes with valid data', async () => {
        await expect(whatsappSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when app_id is empty', async () => {
        await expect(whatsappSchema.validate({ ...valid, app_id: '' })).rejects.toThrow()
    })

    it('fails when app_id is missing', async () => {
        const { app_id: _o, ...rest } = valid
        await expect(whatsappSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when app_id is only whitespace', async () => {
        await expect(whatsappSchema.validate({ ...valid, app_id: '   ' })).rejects.toThrow()
    })

    it('fails when app_secret is empty', async () => {
        await expect(whatsappSchema.validate({ ...valid, app_secret: '' })).rejects.toThrow()
    })

    it('fails when config_id is empty', async () => {
        await expect(whatsappSchema.validate({ ...valid, config_id: '' })).rejects.toThrow()
    })

    it('fails when verify_token is empty', async () => {
        await expect(whatsappSchema.validate({ ...valid, verify_token: '' })).rejects.toThrow()
    })

    it('fails when verify_token is missing', async () => {
        const { verify_token: _o, ...rest } = valid
        await expect(whatsappSchema.validate(rest)).rejects.toThrow()
    })
})
