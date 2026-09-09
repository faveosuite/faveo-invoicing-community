import { buildTaxCreateSchema, buildTaxEditSchema } from '@/validations/admin/taxValidations'

describe('buildTaxCreateSchema', () => {
    const schema = buildTaxCreateSchema()

    it('passes with valid data', async () => {
        await expect(schema.validate({ name: 'GST', rate: 18, priority: 1 })).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(schema.validate({ name: '', rate: 18, priority: 1 })).rejects.toThrow()
    })

    it('fails when name is missing', async () => {
        await expect(schema.validate({ rate: 18, priority: 1 })).rejects.toThrow()
    })

    it('fails when rate is missing', async () => {
        await expect(schema.validate({ name: 'GST', priority: 1 })).rejects.toThrow()
    })

    it('passes when rate is 0 (min boundary)', async () => {
        await expect(schema.validate({ name: 'Zero Rate', rate: 0, priority: 1 })).resolves.toBeTruthy()
    })

    it('fails when rate is negative', async () => {
        await expect(schema.validate({ name: 'GST', rate: -1, priority: 1 })).rejects.toThrow()
    })

    it('fails when rate is not a number', async () => {
        await expect(schema.validate({ name: 'GST', rate: 'abc', priority: 1 })).rejects.toThrow()
    })

    it('fails when priority is missing', async () => {
        await expect(schema.validate({ name: 'GST', rate: 18 })).rejects.toThrow()
    })

    it('fails when priority is below 1', async () => {
        await expect(schema.validate({ name: 'GST', rate: 18, priority: 0 })).rejects.toThrow()
    })
})

describe('buildTaxEditSchema', () => {
    const schema = buildTaxEditSchema()

    it('passes with valid data', async () => {
        await expect(schema.validate({ name: 'VAT', rate: 20, priority: 1 })).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(schema.validate({ name: '', rate: 20, priority: 1 })).rejects.toThrow()
    })

    it('fails when rate is negative', async () => {
        await expect(schema.validate({ name: 'VAT', rate: -5, priority: 1 })).rejects.toThrow()
    })

    it('passes when rate is 0', async () => {
        await expect(schema.validate({ name: 'Zero', rate: 0, priority: 1 })).resolves.toBeTruthy()
    })
})
