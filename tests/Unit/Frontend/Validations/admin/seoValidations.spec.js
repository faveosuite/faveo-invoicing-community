import { seoDefaultPageSchema } from '@/validations/admin/seoValidations'

describe('seoDefaultPageSchema', () => {
    const valid = {
        meta_title:       'Default Title',
        meta_description: 'Default description',
        og_title:         'Default OG Title',
        og_description:   'Default OG description',
    }

    it('passes with valid data', async () => {
        await expect(seoDefaultPageSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('passes with an empty object (all fields are optional)', async () => {
        await expect(seoDefaultPageSchema.validate({})).resolves.toBeTruthy()
    })

    it('passes when fields are explicitly null', async () => {
        await expect(seoDefaultPageSchema.validate({
            meta_title:       null,
            meta_description: null,
            og_title:         null,
            og_description:   null,
        })).resolves.toBeTruthy()
    })

    it('passes when fields are empty strings', async () => {
        await expect(seoDefaultPageSchema.validate({
            meta_title:       '',
            meta_description: '',
            og_title:         '',
            og_description:   '',
        })).resolves.toBeTruthy()
    })

    it('fails when meta_title is not a string', async () => {
        await expect(seoDefaultPageSchema.validate({ ...valid, meta_title: { bad: 'shape' } })).rejects.toThrow()
    })

    it('fails when meta_description is not a string', async () => {
        await expect(seoDefaultPageSchema.validate({ ...valid, meta_description: { bad: 'shape' } })).rejects.toThrow()
    })

    it('fails when og_title is not a string', async () => {
        await expect(seoDefaultPageSchema.validate({ ...valid, og_title: { bad: 'shape' } })).rejects.toThrow()
    })

    it('fails when og_description is not a string', async () => {
        await expect(seoDefaultPageSchema.validate({ ...valid, og_description: { bad: 'shape' } })).rejects.toThrow()
    })
})
