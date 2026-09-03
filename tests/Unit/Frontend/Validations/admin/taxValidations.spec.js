import { buildTaxCreateSchema, buildTaxEditSchema } from '@/validations/admin/taxValidations'

describe('buildTaxCreateSchema', () => {
    const schema = buildTaxCreateSchema()

    it('passes with valid data', async () => {
        await expect(schema.validate({ name: 'GST', rate: 18 })).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(schema.validate({ name: '', rate: 18 })).rejects.toThrow()
    })

    it('fails when name is missing', async () => {
        await expect(schema.validate({ rate: 18 })).rejects.toThrow()
    })

    it('fails when rate is missing', async () => {
        await expect(schema.validate({ name: 'GST' })).rejects.toThrow()
    })

    it('passes when rate is 0 (min boundary)', async () => {
        await expect(schema.validate({ name: 'Zero Rate', rate: 0 })).resolves.toBeTruthy()
    })

    it('fails when rate is negative', async () => {
        await expect(schema.validate({ name: 'GST', rate: -1 })).rejects.toThrow()
    })

    it('fails when rate is not a number', async () => {
        await expect(schema.validate({ name: 'GST', rate: 'abc' })).rejects.toThrow()
    })
})

describe('buildTaxEditSchema', () => {
    const schema = buildTaxEditSchema()

    it('passes with valid data', async () => {
        await expect(schema.validate({ name: 'VAT', rate: 20 })).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(schema.validate({ name: '', rate: 20 })).rejects.toThrow()
    })

    it('fails when rate is negative', async () => {
        await expect(schema.validate({ name: 'VAT', rate: -5 })).rejects.toThrow()
    })

    it('passes when rate is 0', async () => {
        await expect(schema.validate({ name: 'Zero', rate: 0 })).resolves.toBeTruthy()
    })
})
