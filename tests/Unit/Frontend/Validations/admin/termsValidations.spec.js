import { termsSchema } from '@/validations/admin/termsValidations'

describe('termsSchema', () => {
    it('passes with a valid URL', async () => {
        await expect(termsSchema.validate({ terms_url: 'https://example.com/terms' })).resolves.toBeTruthy()
    })

    it('fails when terms_url is empty', async () => {
        await expect(termsSchema.validate({ terms_url: '' })).rejects.toThrow()
    })

    it('fails when terms_url is missing', async () => {
        await expect(termsSchema.validate({})).rejects.toThrow()
    })

    it('fails when terms_url is only whitespace', async () => {
        await expect(termsSchema.validate({ terms_url: '   ' })).rejects.toThrow()
    })

    it('fails when terms_url is not a valid URL', async () => {
        await expect(termsSchema.validate({ terms_url: 'not-a-url' })).rejects.toThrow()
    })

    it('fails when terms_url is a partial URL without protocol', async () => {
        await expect(termsSchema.validate({ terms_url: 'example.com/terms' })).rejects.toThrow()
    })
})
