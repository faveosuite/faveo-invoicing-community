import { couponSchema } from '@/validations/admin/couponValidations'

describe('couponSchema', () => {
    const valid = {
        code:    'SAVE10',
        type:    'percentage',
        value:   '10',
        applied: 'all',
        uses:    '100',
        start:   '2024-01-01',
        expiry:  '2024-12-31',
    }

    it('passes with valid data', async () => {
        await expect(couponSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when code is empty', async () => {
        await expect(couponSchema.validate({ ...valid, code: '' })).rejects.toThrow()
    })

    it('fails when code is missing', async () => {
        const { code: _o, ...rest } = valid
        await expect(couponSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when type is null', async () => {
        await expect(couponSchema.validate({ ...valid, type: null })).rejects.toThrow()
    })

    it('fails when type is empty string', async () => {
        await expect(couponSchema.validate({ ...valid, type: '' })).rejects.toThrow()
    })

    it('fails when value is empty', async () => {
        await expect(couponSchema.validate({ ...valid, value: '' })).rejects.toThrow()
    })

    it('fails when applied is null', async () => {
        await expect(couponSchema.validate({ ...valid, applied: null })).rejects.toThrow()
    })

    it('fails when applied is empty string', async () => {
        await expect(couponSchema.validate({ ...valid, applied: '' })).rejects.toThrow()
    })

    it('fails when uses is empty', async () => {
        await expect(couponSchema.validate({ ...valid, uses: '' })).rejects.toThrow()
    })

    it('fails when start is empty', async () => {
        await expect(couponSchema.validate({ ...valid, start: '' })).rejects.toThrow()
    })

    it('fails when expiry is empty', async () => {
        await expect(couponSchema.validate({ ...valid, expiry: '' })).rejects.toThrow()
    })
})
