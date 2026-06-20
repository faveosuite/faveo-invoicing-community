import { buildInvoiceCreateSchema, invoiceEditSchema } from '@/validations/admin/invoiceValidations'

describe('buildInvoiceCreateSchema - base (no dynamic fields)', () => {
    const schema = buildInvoiceCreateSchema({})
    const valid = {
        user:    '1',
        date:    '2024-01-15',
        product: '2',
        price:   '99.00',
    }

    it('passes with valid base data', async () => {
        await expect(schema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when user is empty string', async () => {
        await expect(schema.validate({ ...valid, user: '' })).rejects.toThrow()
    })

    it('fails when user is null', async () => {
        await expect(schema.validate({ ...valid, user: null })).rejects.toThrow()
    })

    it('fails when date is empty', async () => {
        await expect(schema.validate({ ...valid, date: '' })).rejects.toThrow()
    })

    it('fails when date is missing', async () => {
        const { date: _o, ...rest } = valid
        await expect(schema.validate(rest)).rejects.toThrow()
    })

    it('fails when product is null', async () => {
        await expect(schema.validate({ ...valid, product: null })).rejects.toThrow()
    })

    it('fails when price is empty', async () => {
        await expect(schema.validate({ ...valid, price: '' })).rejects.toThrow()
    })
})

describe('buildInvoiceCreateSchema - with required_domain', () => {
    const schema = buildInvoiceCreateSchema({ required_domain: true })
    const valid = {
        user:    '1',
        date:    '2024-01-15',
        product: '2',
        price:   '99.00',
        domain:  'example.com',
    }

    it('passes when domain is provided', async () => {
        await expect(schema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when domain is empty', async () => {
        await expect(schema.validate({ ...valid, domain: '' })).rejects.toThrow()
    })

    it('fails when domain is missing', async () => {
        const { domain: _o, ...rest } = valid
        await expect(schema.validate(rest)).rejects.toThrow()
    })
})

describe('buildInvoiceCreateSchema - with is_cloud_product', () => {
    const schema = buildInvoiceCreateSchema({ is_cloud_product: true })
    const valid = {
        user:         '1',
        date:         '2024-01-15',
        product:      '2',
        price:        '99.00',
        cloud_domain: 'tenant.cloud.example.com',
    }

    it('passes when cloud_domain is provided', async () => {
        await expect(schema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when cloud_domain is empty', async () => {
        await expect(schema.validate({ ...valid, cloud_domain: '' })).rejects.toThrow()
    })

    it('fails when cloud_domain is missing', async () => {
        const { cloud_domain: _o, ...rest } = valid
        await expect(schema.validate(rest)).rejects.toThrow()
    })
})

describe('invoiceEditSchema', () => {
    it('passes with a valid date', async () => {
        await expect(invoiceEditSchema.validate({ date: '2024-06-01' })).resolves.toBeTruthy()
    })

    it('fails when date is empty', async () => {
        await expect(invoiceEditSchema.validate({ date: '' })).rejects.toThrow()
    })

    it('fails when date is missing', async () => {
        await expect(invoiceEditSchema.validate({})).rejects.toThrow()
    })
})
