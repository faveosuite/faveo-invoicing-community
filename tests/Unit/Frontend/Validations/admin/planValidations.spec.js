import { planSchema } from '@/validations/admin/planValidations'

describe('planSchema', () => {
    const valid = {
        name:    'Pro Plan',
        product: { id: 1, name: 'My Product' },
    }

    it('passes with valid data', async () => {
        await expect(planSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(planSchema.validate({ ...valid, name: '' })).rejects.toThrow()
    })

    it('fails when name is missing', async () => {
        const { name: _o, ...rest } = valid // NOSONAR
        await expect(planSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when product is null', async () => {
        await expect(planSchema.validate({ ...valid, product: null })).rejects.toThrow()
    })

    it('fails when product is empty string', async () => {
        await expect(planSchema.validate({ ...valid, product: '' })).rejects.toThrow()
    })

    it('fails when product is an object with null id', async () => {
        await expect(planSchema.validate({ ...valid, product: { id: null } })).rejects.toThrow()
    })

    it('passes when product is a plain id string', async () => {
        await expect(planSchema.validate({ ...valid, product: '5' })).resolves.toBeTruthy()
    })
})
