import { demoSchema } from '@/validations/client/demoValidations'

describe('demoSchema', () => {
    const valid = {
        name:    'Bob Smith',
        email:   'bob@example.com',
        mobile:  '+9876543210',
        message: 'I would like a demo.',
    }

    it('passes with valid data', async () => {
        await expect(demoSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(demoSchema.validate({ ...valid, name: '' })).rejects.toThrow()
    })

    it('fails when name is missing', async () => {
        const { name: _o, ...rest } = valid
        await expect(demoSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when name is only whitespace', async () => {
        await expect(demoSchema.validate({ ...valid, name: '   ' })).rejects.toThrow()
    })

    it('fails when email is empty', async () => {
        await expect(demoSchema.validate({ ...valid, email: '' })).rejects.toThrow()
    })

    it('fails when email has invalid format', async () => {
        await expect(demoSchema.validate({ ...valid, email: 'notanemail' })).rejects.toThrow()
    })

    it('fails when email has no TLD', async () => {
        await expect(demoSchema.validate({ ...valid, email: 'user@nodomain' })).rejects.toThrow()
    })

    it('fails when mobile is empty', async () => {
        await expect(demoSchema.validate({ ...valid, mobile: '' })).rejects.toThrow()
    })

    it('fails when message is empty', async () => {
        await expect(demoSchema.validate({ ...valid, message: '' })).rejects.toThrow()
    })

    it('fails when message is missing', async () => {
        const { message: _o, ...rest } = valid
        await expect(demoSchema.validate(rest)).rejects.toThrow()
    })
})
