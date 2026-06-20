import { buildFrontendPageCreateSchema, buildFrontendPageEditSchema } from '@/validations/admin/pageValidations'

describe('buildFrontendPageCreateSchema', () => {
    const schema = buildFrontendPageCreateSchema('custom')
    const valid = {
        name:    'About Us',
        slug:    'about-us',
        content: '<p>About us page</p>',
    }

    it('passes with valid data', async () => {
        await expect(schema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(schema.validate({ ...valid, name: '' })).rejects.toThrow()
    })

    it('fails when name is missing', async () => {
        const { name: _o, ...rest } = valid
        await expect(schema.validate(rest)).rejects.toThrow()
    })

    it('fails when slug is empty', async () => {
        await expect(schema.validate({ ...valid, slug: '' })).rejects.toThrow()
    })

    it('fails when slug is missing', async () => {
        const { slug: _o, ...rest } = valid
        await expect(schema.validate(rest)).rejects.toThrow()
    })

    it('fails when content is empty', async () => {
        await expect(schema.validate({ ...valid, content: '' })).rejects.toThrow()
    })

    it('fails when content is missing', async () => {
        const { content: _o, ...rest } = valid
        await expect(schema.validate(rest)).rejects.toThrow()
    })
})

describe('buildFrontendPageEditSchema', () => {
    const schema = buildFrontendPageEditSchema('custom')
    const valid = {
        name:            'About Us',
        slug:            'about-us',
        created_at_date: '2024-01-01',
        content:         '<p>About us page</p>',
    }

    it('passes with valid data', async () => {
        await expect(schema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(schema.validate({ ...valid, name: '' })).rejects.toThrow()
    })

    it('fails when slug is empty', async () => {
        await expect(schema.validate({ ...valid, slug: '' })).rejects.toThrow()
    })

    it('fails when created_at_date is empty', async () => {
        await expect(schema.validate({ ...valid, created_at_date: '' })).rejects.toThrow()
    })

    it('fails when created_at_date is missing', async () => {
        const { created_at_date: _o, ...rest } = valid
        await expect(schema.validate(rest)).rejects.toThrow()
    })

    it('fails when content is empty', async () => {
        await expect(schema.validate({ ...valid, content: '' })).rejects.toThrow()
    })
})
