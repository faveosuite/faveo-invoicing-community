import { cloudSettingsSchema, cloudProductSchema } from '@/validations/admin/cloudValidations'

describe('cloudSettingsSchema', () => {
    const valid = {
        cloud_central_domain: 'https://cloud.example.com',
        cloud_cname:          'cname.example.com',
        cloud_top_message:    'Welcome',
        cloud_label_field:    'Domain',
        cloud_label_radio:    'Type',
    }

    it('passes with valid data', async () => {
        await expect(cloudSettingsSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when cloud_central_domain is missing', async () => {
        const { cloud_central_domain: _o, ...rest } = valid // NOSONAR
        await expect(cloudSettingsSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when cloud_central_domain is empty', async () => {
        await expect(cloudSettingsSchema.validate({ ...valid, cloud_central_domain: '' })).rejects.toThrow()
    })

    it('fails when cloud_central_domain is not a URL', async () => {
        await expect(cloudSettingsSchema.validate({ ...valid, cloud_central_domain: 'not-a-url' })).rejects.toThrow()
    })

    it('fails when cloud_cname is empty', async () => {
        await expect(cloudSettingsSchema.validate({ ...valid, cloud_cname: '' })).rejects.toThrow()
    })

    it('fails when cloud_top_message is empty', async () => {
        await expect(cloudSettingsSchema.validate({ ...valid, cloud_top_message: '' })).rejects.toThrow()
    })

    it('fails when cloud_label_field is empty', async () => {
        await expect(cloudSettingsSchema.validate({ ...valid, cloud_label_field: '' })).rejects.toThrow()
    })

    it('fails when cloud_label_radio is empty', async () => {
        await expect(cloudSettingsSchema.validate({ ...valid, cloud_label_radio: '' })).rejects.toThrow()
    })
})

describe('cloudProductSchema', () => {
    const valid = {
        cloud_product:     { id: 1, name: 'Product A' },
        cloud_free_plan:   { id: 2, name: 'Free Plan' },
        cloud_product_key: 'MY_KEY',
    }

    it('passes with valid data', async () => {
        await expect(cloudProductSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when cloud_product is null', async () => {
        await expect(cloudProductSchema.validate({ ...valid, cloud_product: null })).rejects.toThrow()
    })

    it('fails when cloud_product is empty string', async () => {
        await expect(cloudProductSchema.validate({ ...valid, cloud_product: '' })).rejects.toThrow()
    })

    it('fails when cloud_free_plan is null', async () => {
        await expect(cloudProductSchema.validate({ ...valid, cloud_free_plan: null })).rejects.toThrow()
    })

    it('fails when cloud_free_plan is empty string', async () => {
        await expect(cloudProductSchema.validate({ ...valid, cloud_free_plan: '' })).rejects.toThrow()
    })

    it('fails when cloud_product_key is empty', async () => {
        await expect(cloudProductSchema.validate({ ...valid, cloud_product_key: '' })).rejects.toThrow()
    })

    it('fails when cloud_product_key is missing', async () => {
        const { cloud_product_key: _o, ...rest } = valid // NOSONAR
        await expect(cloudProductSchema.validate(rest)).rejects.toThrow()
    })
})
