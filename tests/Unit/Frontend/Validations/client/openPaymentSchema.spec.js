import { openPaymentSchema } from '@/validations/client/openPaymentSchema'

describe('openPaymentSchema', () => {
    const valid = {
        name:    'Alice Smith',
        email:   'alice@example.com',
        mobile:  '+12345678',
        company: 'Acme Corp',
        address: '123 Main St',
        city:    'Springfield',
        state:   'IL',
        zip:     '62701',
        country: 'US',
        amount:  99,
    }

    it('passes with valid data', async () => {
        await expect(openPaymentSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(openPaymentSchema.validate({ ...valid, name: '' })).rejects.toThrow()
    })

    it('fails when name is missing', async () => {
        const { name: _o, ...rest } = valid // NOSONAR
        await expect(openPaymentSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when name exceeds 100 characters', async () => {
        await expect(openPaymentSchema.validate({ ...valid, name: 'A'.repeat(101) })).rejects.toThrow()
    })

    it('passes when name is exactly 100 characters', async () => {
        await expect(openPaymentSchema.validate({ ...valid, name: 'A'.repeat(100) })).resolves.toBeTruthy()
    })

    it('fails when email is empty', async () => {
        await expect(openPaymentSchema.validate({ ...valid, email: '' })).rejects.toThrow()
    })

    it('fails when email is not a valid email', async () => {
        await expect(openPaymentSchema.validate({ ...valid, email: 'notanemail' })).rejects.toThrow()
    })

    it('fails when mobile is empty', async () => {
        await expect(openPaymentSchema.validate({ ...valid, mobile: '' })).rejects.toThrow()
    })

    it('fails when mobile is fewer than 8 characters', async () => {
        await expect(openPaymentSchema.validate({ ...valid, mobile: '1234567' })).rejects.toThrow()
    })

    it('passes when mobile is exactly 8 characters', async () => {
        await expect(openPaymentSchema.validate({ ...valid, mobile: '12345678' })).resolves.toBeTruthy()
    })

    it('fails when company is empty', async () => {
        await expect(openPaymentSchema.validate({ ...valid, company: '' })).rejects.toThrow()
    })

    it('fails when address is empty', async () => {
        await expect(openPaymentSchema.validate({ ...valid, address: '' })).rejects.toThrow()
    })

    it('fails when city is empty', async () => {
        await expect(openPaymentSchema.validate({ ...valid, city: '' })).rejects.toThrow()
    })

    it('fails when state is empty', async () => {
        await expect(openPaymentSchema.validate({ ...valid, state: '' })).rejects.toThrow()
    })

    it('fails when zip is empty', async () => {
        await expect(openPaymentSchema.validate({ ...valid, zip: '' })).rejects.toThrow()
    })

    it('fails when zip exceeds 15 characters', async () => {
        await expect(openPaymentSchema.validate({ ...valid, zip: '1'.repeat(16) })).rejects.toThrow()
    })

    it('fails when country is empty', async () => {
        await expect(openPaymentSchema.validate({ ...valid, country: '' })).rejects.toThrow()
    })

    it('fails when amount is missing', async () => {
        const { amount: _o, ...rest } = valid // NOSONAR
        await expect(openPaymentSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when amount is 0', async () => {
        await expect(openPaymentSchema.validate({ ...valid, amount: 0 })).rejects.toThrow()
    })

    it('fails when amount is negative', async () => {
        await expect(openPaymentSchema.validate({ ...valid, amount: -1 })).rejects.toThrow()
    })

    it('passes when amount is 1 (min boundary)', async () => {
        await expect(openPaymentSchema.validate({ ...valid, amount: 1 })).resolves.toBeTruthy()
    })

    it('fails when amount is not a number', async () => {
        await expect(openPaymentSchema.validate({ ...valid, amount: 'abc' })).rejects.toThrow()
    })
})
