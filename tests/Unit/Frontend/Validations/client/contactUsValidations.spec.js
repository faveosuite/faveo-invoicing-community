import { contactUsSchema } from '@/validations/client/contactUsValidations'

describe('contactUsSchema', () => {
    const valid = {
        name:    'Jane Doe',
        email:   'jane@example.com',
        mobile:  '+1234567890',
        message: 'Hello, I need help.',
    }

    it('passes with valid data', async () => {
        await expect(contactUsSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(contactUsSchema.validate({ ...valid, name: '' })).rejects.toThrow()
    })

    it('fails when name is missing', async () => {
        const { name: _o, ...rest } = valid
        await expect(contactUsSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when name is only whitespace', async () => {
        await expect(contactUsSchema.validate({ ...valid, name: '   ' })).rejects.toThrow()
    })

    it('fails when email is empty', async () => {
        await expect(contactUsSchema.validate({ ...valid, email: '' })).rejects.toThrow()
    })

    it('fails when email has invalid format', async () => {
        await expect(contactUsSchema.validate({ ...valid, email: 'notanemail' })).rejects.toThrow()
    })

    it('fails when email has no TLD', async () => {
        await expect(contactUsSchema.validate({ ...valid, email: 'user@nodomain' })).rejects.toThrow()
    })

    it('fails when mobile is empty', async () => {
        await expect(contactUsSchema.validate({ ...valid, mobile: '' })).rejects.toThrow()
    })

    it('fails when mobile is only whitespace', async () => {
        await expect(contactUsSchema.validate({ ...valid, mobile: '   ' })).rejects.toThrow()
    })

    it('fails when message is empty', async () => {
        await expect(contactUsSchema.validate({ ...valid, message: '' })).rejects.toThrow()
    })

    it('fails when message is missing', async () => {
        const { message: _o, ...rest } = valid
        await expect(contactUsSchema.validate(rest)).rejects.toThrow()
    })
})
