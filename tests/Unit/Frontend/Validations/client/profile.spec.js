import { profileSchema, passwordChangeSchema } from '@/validations/client/profile'

describe('profileSchema', () => {
    const valid = {
        first_name: 'Alice',
        last_name:  'Walker',
        user_name:  'alicewalker',
        company:    'TechCo',
        mobile:     '+1234567890',
        address:    '456 Oak Ave',
        country:    { id: 1, name: 'USA' },
        timezone_id: { id: 1, name: 'UTC' },
    }

    it('passes with valid data', async () => {
        await expect(profileSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when first_name is empty', async () => {
        await expect(profileSchema.validate({ ...valid, first_name: '' })).rejects.toThrow()
    })

    it('fails when first_name is missing', async () => {
        const { first_name: _o, ...rest } = valid // NOSONAR
        await expect(profileSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when last_name is empty', async () => {
        await expect(profileSchema.validate({ ...valid, last_name: '' })).rejects.toThrow()
    })

    it('fails when user_name is empty', async () => {
        await expect(profileSchema.validate({ ...valid, user_name: '' })).rejects.toThrow()
    })

    it('fails when company is empty', async () => {
        await expect(profileSchema.validate({ ...valid, company: '' })).rejects.toThrow()
    })

    it('fails when mobile is empty', async () => {
        await expect(profileSchema.validate({ ...valid, mobile: '' })).rejects.toThrow()
    })

    it('fails when address is empty', async () => {
        await expect(profileSchema.validate({ ...valid, address: '' })).rejects.toThrow()
    })

    it('fails when country is null', async () => {
        await expect(profileSchema.validate({ ...valid, country: null })).rejects.toThrow()
    })

    it('fails when country is empty string', async () => {
        await expect(profileSchema.validate({ ...valid, country: '' })).rejects.toThrow()
    })
})

describe('passwordChangeSchema', () => {
    const valid = {
        old_password:     'OldPass1',
        new_password:     'NewPass!1',
        confirm_password: 'NewPass!1',
    }

    it('passes with matching passwords', async () => {
        await expect(passwordChangeSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when old_password is empty', async () => {
        await expect(passwordChangeSchema.validate({ ...valid, old_password: '' })).rejects.toThrow()
    })

    it('fails when old_password is missing', async () => {
        const { old_password: _o, ...rest } = valid // NOSONAR
        await expect(passwordChangeSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when new_password is empty', async () => {
        await expect(passwordChangeSchema.validate({ ...valid, new_password: '' })).rejects.toThrow()
    })

    it('fails when confirm_password is empty', async () => {
        await expect(passwordChangeSchema.validate({ ...valid, confirm_password: '' })).rejects.toThrow()
    })

    it('fails when confirm_password does not match new_password', async () => {
        await expect(passwordChangeSchema.validate({ ...valid, confirm_password: 'DifferentPass!' })).rejects.toThrow()
    })
})
