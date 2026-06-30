import { productSchema } from '@/validations/admin/productValidations'

describe('productSchema', () => {
    const valid = {
        name:                'My Product',
        type:                { id: 1, name: 'Software' },
        group:               { id: 2, name: 'Cloud' },
        description:         'A full description',
        short_description:   'Short desc',
        product_sku:         'SKU-001',
        product_description: 'Product details',
        shoping_cart_link:   'https://example.com/cart',
    }

    it('passes with valid data (no github)', async () => {
        await expect(productSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(productSchema.validate({ ...valid, name: '' })).rejects.toThrow()
    })

    it('fails when name is missing', async () => {
        const { name: _o, ...rest } = valid // NOSONAR
        await expect(productSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when type is null', async () => {
        await expect(productSchema.validate({ ...valid, type: null })).rejects.toThrow()
    })

    it('fails when group is null', async () => {
        await expect(productSchema.validate({ ...valid, group: null })).rejects.toThrow()
    })

    it('fails when description is empty', async () => {
        await expect(productSchema.validate({ ...valid, description: '' })).rejects.toThrow()
    })

    it('fails when short_description is empty', async () => {
        await expect(productSchema.validate({ ...valid, short_description: '' })).rejects.toThrow()
    })

    it('fails when product_sku is empty', async () => {
        await expect(productSchema.validate({ ...valid, product_sku: '' })).rejects.toThrow()
    })

    it('fails when product_description is empty', async () => {
        await expect(productSchema.validate({ ...valid, product_description: '' })).rejects.toThrow()
    })

    it('passes when file_source is github and github fields are provided', async () => {
        await expect(productSchema.validate({
            ...valid,
            file_source:       'github',
            github_owner:      'myorg',
            github_repository: 'myrepo',
        })).resolves.toBeTruthy()
    })

    it('fails when file_source is github and github_owner is empty', async () => {
        await expect(productSchema.validate({
            ...valid,
            file_source:       'github',
            github_owner:      '',
            github_repository: 'myrepo',
        })).rejects.toThrow()
    })

    it('fails when file_source is github and github_repository is empty', async () => {
        await expect(productSchema.validate({
            ...valid,
            file_source:       'github',
            github_owner:      'myorg',
            github_repository: '',
        })).rejects.toThrow()
    })

    it('passes when file_source is not github and github fields are omitted', async () => {
        await expect(productSchema.validate({
            ...valid,
            file_source: 'upload',
        })).resolves.toBeTruthy()
    })
})
