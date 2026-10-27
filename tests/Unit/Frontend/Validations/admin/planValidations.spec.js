import { planSchema } from '@/validations/admin/planValidations'

describe('planSchema', () => {
    const valid = {
        name:    'Pro Plan',
        product: { id: 1, name: 'My Product' },
        no_of_agents: 5,
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

describe('planSchema — required field follows the product type', () => {
    const base = { name: 'Pro Plan', product: { id: 1 } }

    it('agent product: fails without agents even when a hidden quantity is set', async () => {
        await expect(planSchema.validate({ ...base, show_agent: true, no_of_agents: '', product_quantity: 1 })).rejects.toThrow()
    })

    it('agent product: 0 agents (unlimited) passes', async () => {
        await expect(planSchema.validate({ ...base, show_agent: true, no_of_agents: 0 })).resolves.toBeTruthy()
    })

    it('quantity product: fails without quantity even when hidden agents are set', async () => {
        await expect(planSchema.validate({ ...base, show_agent: false, no_of_agents: 5, product_quantity: '' })).rejects.toThrow()
    })

    it('product not known yet: either field is enough', async () => {
        await expect(planSchema.validate({ ...base, show_agent: null, product_quantity: 1 })).resolves.toBeTruthy()
    })
})
