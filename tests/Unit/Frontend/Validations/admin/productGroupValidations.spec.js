import { productGroupSchema } from '@/validations/admin/productGroupValidations'

describe('productGroupSchema', () => {
    const valid = {
        name: 'Cloud Products',
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

    // Design-template picker (pricing_templates_id) was removed from the UI (2026-09) —
    // the backend keeps the column nullable, so the frontend schema has no rule for it.
})
