import { licenseDetailsSchema } from '@/validations/admin/orderValidations'

describe('licenseDetailsSchema', () => {
    const valid = {
        limit:            5,
        update_end:       '2025-12-31',
        subscription_end: '2025-12-31',
        support_end:      '2025-12-31',
    }

    it('passes with valid data', async () => {
        await expect(licenseDetailsSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('passes without optional date fields', async () => {
        await expect(licenseDetailsSchema.validate({ limit: 1 })).resolves.toBeTruthy()
    })

    it('fails when limit is missing', async () => {
        const { limit: _o, ...rest } = valid
        await expect(licenseDetailsSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when limit is 0 (below min 1)', async () => {
        await expect(licenseDetailsSchema.validate({ ...valid, limit: 0 })).rejects.toThrow()
    })

    it('fails when limit is negative', async () => {
        await expect(licenseDetailsSchema.validate({ ...valid, limit: -1 })).rejects.toThrow()
    })

    it('passes when limit is 1 (min boundary)', async () => {
        await expect(licenseDetailsSchema.validate({ ...valid, limit: 1 })).resolves.toBeTruthy()
    })

    it('fails when limit is not a number', async () => {
        await expect(licenseDetailsSchema.validate({ ...valid, limit: 'abc' })).rejects.toThrow()
    })

    it('passes when optional date fields are null', async () => {
        await expect(licenseDetailsSchema.validate({
            limit:            2,
            update_end:       null,
            subscription_end: null,
            support_end:      null,
        })).resolves.toBeTruthy()
    })
})
