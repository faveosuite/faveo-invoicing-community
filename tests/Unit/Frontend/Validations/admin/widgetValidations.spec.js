import { buildAnalyticsSchema, socialMediaSchema, footerWidgetSchema } from '@/validations/admin/widgetValidations'

describe('buildAnalyticsSchema - without Google Analytics', () => {
    const schema = buildAnalyticsSchema(false)

    it('passes with name and script', async () => {
        await expect(schema.validate({ name: 'Hotjar', script: '<script>/* hotjar */</script>' })).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(schema.validate({ name: '', script: '<script></script>' })).rejects.toThrow()
    })

    it('fails when name is missing', async () => {
        await expect(schema.validate({ script: '<script></script>' })).rejects.toThrow()
    })

    it('fails when script is empty', async () => {
        await expect(schema.validate({ name: 'Analytics', script: '' })).rejects.toThrow()
    })

    it('passes without google_analytics_tag (not required)', async () => {
        await expect(schema.validate({ name: 'Analytics', script: '<script></script>' })).resolves.toBeTruthy()
    })
})

describe('buildAnalyticsSchema - with Google Analytics', () => {
    const schema = buildAnalyticsSchema(true)

    it('passes when google_analytics_tag is provided', async () => {
        await expect(schema.validate({
            name:                 'GA4',
            script:               '<script></script>',
            google_analytics_tag: 'G-XXXXXXXX',
        })).resolves.toBeTruthy()
    })

    it('fails when google_analytics_tag is empty', async () => {
        await expect(schema.validate({
            name:                 'GA4',
            script:               '<script></script>',
            google_analytics_tag: '',
        })).rejects.toThrow()
    })

    it('fails when google_analytics_tag is missing', async () => {
        await expect(schema.validate({ name: 'GA4', script: '<script></script>' })).rejects.toThrow()
    })
})

describe('socialMediaSchema', () => {
    const valid = { name: 'Facebook', link: 'https://facebook.com/mypage' }

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
        const { link: _o, ...rest } = valid
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
