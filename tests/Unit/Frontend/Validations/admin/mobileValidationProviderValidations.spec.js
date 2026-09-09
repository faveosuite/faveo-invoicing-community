import { buildMobileValidationSchema } from '@/validations/admin/mobileValidationProviderValidations'

describe('buildMobileValidationSchema - non-vonage provider', () => {
    const schema = buildMobileValidationSchema({ provider: 'twilio' })

    it('passes with only apikey', async () => {
        await expect(schema.validate({ apikey: 'twilio-key' })).resolves.toBeTruthy()
    })

    it('fails when apikey is empty', async () => {
        await expect(schema.validate({ apikey: '' })).rejects.toThrow()
    })

    it('fails when apikey is missing', async () => {
        await expect(schema.validate({})).rejects.toThrow()
    })

    it('fails when apikey is only whitespace', async () => {
        await expect(schema.validate({ apikey: '   ' })).rejects.toThrow()
    })

    it('passes without apisecret (not required for non-vonage)', async () => {
        await expect(schema.validate({ apikey: 'key123' })).resolves.toBeTruthy()
    })
})

describe('buildMobileValidationSchema - vonage provider', () => {
    const schema = buildMobileValidationSchema({ provider: 'vonage' })

    it('passes with apikey and apisecret', async () => {
        await expect(schema.validate({ apikey: 'key123', apisecret: 'secret456' })).resolves.toBeTruthy()
    })

    it('fails when apikey is empty', async () => {
        await expect(schema.validate({ apikey: '', apisecret: 'secret456' })).rejects.toThrow()
    })

    it('fails when apisecret is empty', async () => {
        await expect(schema.validate({ apikey: 'key123', apisecret: '' })).rejects.toThrow()
    })

    it('fails when apisecret is missing', async () => {
        await expect(schema.validate({ apikey: 'key123' })).rejects.toThrow()
    })

    it('fails when apisecret is only whitespace', async () => {
        await expect(schema.validate({ apikey: 'key123', apisecret: '   ' })).rejects.toThrow()
    })
})
