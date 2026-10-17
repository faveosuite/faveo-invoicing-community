import { licenseTypeCreateSchema, licenseTypeEditSchema } from '@/validations/admin/licenseTypeValidations'

describe('licenseTypeCreateSchema', () => {
    it('passes with a valid license_type_name', async () => {
        await expect(licenseTypeCreateSchema.validate({ license_type_name: 'Standard' })).resolves.toBeTruthy()
    })

    it('fails when license_type_name is empty', async () => {
        await expect(licenseTypeCreateSchema.validate({ license_type_name: '' })).rejects.toThrow()
    })

    it('fails when license_type_name is missing', async () => {
        await expect(licenseTypeCreateSchema.validate({})).rejects.toThrow()
    })
})

describe('licenseTypeEditSchema', () => {
    it('passes with a valid license_type_edit_name', async () => {
        await expect(licenseTypeEditSchema.validate({ license_type_edit_name: 'Enterprise' })).resolves.toBeTruthy()
    })

    it('fails when license_type_edit_name is empty', async () => {
        await expect(licenseTypeEditSchema.validate({ license_type_edit_name: '' })).rejects.toThrow()
    })

    it('fails when license_type_edit_name is missing', async () => {
        await expect(licenseTypeEditSchema.validate({})).rejects.toThrow()
    })
})
