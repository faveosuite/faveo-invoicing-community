import { userCreateSchema, userEditSchema } from '@/validations/admin/userValidations'

describe('userCreateSchema', () => {
    const valid = {
        first_name:  'John',
        last_name:   'Doe',
        email:       'john@example.com',
        company:     'Acme Inc',
        address:     '123 Main St',
        mobile:      '+1234567890',
        country:     { id: 1, name: 'USA' },
        timezone_id: { id: 1, name: 'UTC' },
    }

    it('passes with valid data', async () => {
        await expect(userCreateSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when first_name is empty', async () => {
        await expect(userCreateSchema.validate({ ...valid, first_name: '' })).rejects.toThrow()
    })

    it('fails when last_name is missing', async () => {
        const { last_name: _o, ...rest } = valid
        await expect(userCreateSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when email is empty', async () => {
        await expect(userCreateSchema.validate({ ...valid, email: '' })).rejects.toThrow()
    })

    it('fails when email is not a valid email address', async () => {
        await expect(userCreateSchema.validate({ ...valid, email: 'not-an-email' })).rejects.toThrow()
    })

    it('fails when company is empty', async () => {
        await expect(userCreateSchema.validate({ ...valid, company: '' })).rejects.toThrow()
    })

    it('fails when address is empty', async () => {
        await expect(userCreateSchema.validate({ ...valid, address: '' })).rejects.toThrow()
    })

    it('fails when mobile is empty', async () => {
        await expect(userCreateSchema.validate({ ...valid, mobile: '' })).rejects.toThrow()
    })

    it('fails when country is null', async () => {
        await expect(userCreateSchema.validate({ ...valid, country: null })).rejects.toThrow()
    })

    it('fails when timezone_id is null', async () => {
        await expect(userCreateSchema.validate({ ...valid, timezone_id: null })).rejects.toThrow()
    })
})

describe('userEditSchema', () => {
    const valid = {
        first_name:  'Jane',
        last_name:   'Smith',
        email:       'jane@example.com',
        company:     'Corp Ltd',
        address:     '456 Elm St',
        mobile:      '+9876543210',
        timezone_id: { id: 2, name: 'EST' },
    }

    it('passes with valid data', async () => {
        await expect(userEditSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('passes without country (not in edit schema)', async () => {
        await expect(userEditSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when email is not a valid email address', async () => {
        await expect(userEditSchema.validate({ ...valid, email: 'invalid' })).rejects.toThrow()
    })

    it('fails when first_name is empty', async () => {
        await expect(userEditSchema.validate({ ...valid, first_name: '' })).rejects.toThrow()
    })

    it('fails when timezone_id is null', async () => {
        await expect(userEditSchema.validate({ ...valid, timezone_id: null })).rejects.toThrow()
    })
})
