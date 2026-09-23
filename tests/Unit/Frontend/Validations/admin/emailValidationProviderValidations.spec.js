import { emailValidationProviderSchema } from '@/validations/admin/emailValidationProviderValidations'

describe('emailValidationProviderSchema', () => {
    it('passes with a valid apikey', async () => {
        await expect(emailValidationProviderSchema.validate({ apikey: 'abc123' })).resolves.toBeTruthy()
    })

    it('fails when apikey is empty', async () => {
        await expect(emailValidationProviderSchema.validate({ apikey: '' })).rejects.toThrow()
    })

    it('fails when apikey is missing', async () => {
        await expect(emailValidationProviderSchema.validate({})).rejects.toThrow()
    })

    it('trims whitespace and fails when apikey is only spaces', async () => {
        await expect(emailValidationProviderSchema.validate({ apikey: '   ' })).rejects.toThrow()
    })
})
