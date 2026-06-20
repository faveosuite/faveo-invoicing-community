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
    }

    it('passes with valid data', async () => {
        await expect(profileSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when first_name is empty', async () => {
        await expect(profileSchema.validate({ ...valid, first_name: '' })).rejects.toThrow()
    })

    it('fails when first_name is missing', async () => {
        const { first_name: _o, ...rest } = valid
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
        current_password:      'OldPass1',
        password:              'NewPass!1',
        password_confirmation: 'NewPass!1',
    }

    it('passes with matching passwords', async () => {
        await expect(passwordChangeSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when current_password is empty', async () => {
        await expect(passwordChangeSchema.validate({ ...valid, current_password: '' })).rejects.toThrow()
    })

    it('fails when current_password is missing', async () => {
        const { current_password: _o, ...rest } = valid
        await expect(passwordChangeSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when password is empty', async () => {
        await expect(passwordChangeSchema.validate({ ...valid, password: '' })).rejects.toThrow()
    })

    it('fails when password_confirmation is empty', async () => {
        await expect(passwordChangeSchema.validate({ ...valid, password_confirmation: '' })).rejects.toThrow()
    })

    it('fails when password_confirmation does not match password', async () => {
        await expect(passwordChangeSchema.validate({ ...valid, password_confirmation: 'DifferentPass!' })).rejects.toThrow()
    })
})
