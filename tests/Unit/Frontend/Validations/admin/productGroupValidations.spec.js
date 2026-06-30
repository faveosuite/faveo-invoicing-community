import { productGroupSchema } from '@/validations/admin/productGroupValidations'

describe('productGroupSchema', () => {
    const valid = {
        name:                 'Cloud Products',
        pricing_templates_id: { id: 3, name: 'Standard Pricing' },
    }

    it('passes with valid data', async () => {
        await expect(productGroupSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(productGroupSchema.validate({ ...valid, name: '' })).rejects.toThrow()
    })

    it('fails when name is missing', async () => {
        const { name: _o, ...rest } = valid // NOSONAR
        await expect(productGroupSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when pricing_templates_id is null', async () => {
        await expect(productGroupSchema.validate({ ...valid, pricing_templates_id: null })).rejects.toThrow()
    })

    it('fails when pricing_templates_id is empty string', async () => {
        await expect(productGroupSchema.validate({ ...valid, pricing_templates_id: '' })).rejects.toThrow()
    })

    it('fails when pricing_templates_id has null id', async () => {
        await expect(productGroupSchema.validate({ ...valid, pricing_templates_id: { id: null } })).rejects.toThrow()
    })

    it('passes when pricing_templates_id is a plain string id', async () => {
        await expect(productGroupSchema.validate({ ...valid, pricing_templates_id: '3' })).resolves.toBeTruthy()
    })
})
