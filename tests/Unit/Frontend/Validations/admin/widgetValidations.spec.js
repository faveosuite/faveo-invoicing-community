import { analyticsSchema, socialMediaSchema, footerWidgetSchema } from '@/validations/admin/widgetValidations'

describe('analyticsSchema', () => {
    it('passes with name and script', async () => {
        await expect(analyticsSchema.validate({ name: 'Hotjar', script: '<script>/* hotjar */</script>' })).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(analyticsSchema.validate({ name: '', script: '<script></script>' })).rejects.toThrow()
    })

    it('fails when name is missing', async () => {
        await expect(analyticsSchema.validate({ script: '<script></script>' })).rejects.toThrow()
    })

    it('fails when script is empty', async () => {
        await expect(analyticsSchema.validate({ name: 'Analytics', script: '' })).rejects.toThrow()
    })
})

describe('socialMediaSchema', () => {
    const valid = { name: 'Facebook', link: 'https://facebook.com/mypage', class: 'social-icons-facebook', fa_class: 'fab fa-facebook' }

    it('passes with valid data', async () => {
        await expect(socialMediaSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(socialMediaSchema.validate({ ...valid, name: '' })).rejects.toThrow()
    })

    it('fails when link is empty', async () => {
        await expect(socialMediaSchema.validate({ ...valid, link: '' })).rejects.toThrow()
    })

    it('fails when link is not a valid URL', async () => {
        await expect(socialMediaSchema.validate({ ...valid, link: 'not-a-url' })).rejects.toThrow()
    })

    it('fails when link is missing', async () => {
        const { link: _o, ...rest } = valid // NOSONAR
        await expect(socialMediaSchema.validate(rest)).rejects.toThrow()
    })
})

describe('footerWidgetSchema', () => {
    it('passes with a valid name', async () => {
        await expect(footerWidgetSchema.validate({ name: 'Footer Links' })).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(footerWidgetSchema.validate({ name: '' })).rejects.toThrow()
    })

    it('fails when name is missing', async () => {
        await expect(footerWidgetSchema.validate({})).rejects.toThrow()
    })
})
