import { buildEmailSettingsSchema, templateEditSchema } from '@/validations/admin/emailValidations'

describe('buildEmailSettingsSchema - smtp', () => {
    const schema = buildEmailSettingsSchema('smtp')

    it('passes with host and port', async () => {
        await expect(schema.validate({ host: 'smtp.example.com', port: '587' })).resolves.toBeTruthy()
    })

    it('fails when host is empty', async () => {
        await expect(schema.validate({ host: '', port: '587' })).rejects.toThrow()
    })

    it('fails when host is missing', async () => {
        await expect(schema.validate({ port: '587' })).rejects.toThrow()
    })

    it('fails when port is empty', async () => {
        await expect(schema.validate({ host: 'smtp.example.com', port: '' })).rejects.toThrow()
    })

    it('fails when port is missing', async () => {
        await expect(schema.validate({ host: 'smtp.example.com' })).rejects.toThrow()
    })
})

describe('buildEmailSettingsSchema - mailgun', () => {
    const schema = buildEmailSettingsSchema('mailgun')

    it('passes with secret and domain', async () => {
        await expect(schema.validate({ secret: 'key-abc', domain: 'mg.example.com' })).resolves.toBeTruthy()
    })

    it('fails when secret is empty', async () => {
        await expect(schema.validate({ secret: '', domain: 'mg.example.com' })).rejects.toThrow()
    })

    it('fails when domain is empty', async () => {
        await expect(schema.validate({ secret: 'key-abc', domain: '' })).rejects.toThrow()
    })
})

describe('buildEmailSettingsSchema - ses', () => {
    const schema = buildEmailSettingsSchema('ses')

    it('passes with secret, key, and region', async () => {
        await expect(schema.validate({ secret: 's3cr3t', key: 'AKID', region: 'us-east-1' })).resolves.toBeTruthy()
    })

    it('fails when secret is missing', async () => {
        await expect(schema.validate({ key: 'AKID', region: 'us-east-1' })).rejects.toThrow()
    })

    it('fails when key is missing', async () => {
        await expect(schema.validate({ secret: 's3cr3t', region: 'us-east-1' })).rejects.toThrow()
    })

    it('fails when region is missing', async () => {
        await expect(schema.validate({ secret: 's3cr3t', key: 'AKID' })).rejects.toThrow()
    })
})

describe('buildEmailSettingsSchema - mandrill', () => {
    const schema = buildEmailSettingsSchema('mandrill')

    it('passes with secret', async () => {
        await expect(schema.validate({ secret: 'mandrill-secret' })).resolves.toBeTruthy()
    })

    it('fails when secret is empty', async () => {
        await expect(schema.validate({ secret: '' })).rejects.toThrow()
    })
})

describe('buildEmailSettingsSchema - sparkpost', () => {
    const schema = buildEmailSettingsSchema('sparkpost')

    it('passes with secret', async () => {
        await expect(schema.validate({ secret: 'sp-secret' })).resolves.toBeTruthy()
    })

    it('fails when secret is missing', async () => {
        await expect(schema.validate({})).rejects.toThrow()
    })
})

describe('buildEmailSettingsSchema - unknown driver', () => {
    const schema = buildEmailSettingsSchema('sendmail')

    it('passes with empty object for driver with no required fields', async () => {
        await expect(schema.validate({})).resolves.toBeTruthy()
    })
})

describe('templateEditSchema', () => {
    const valid = { name: 'Welcome', type: 'html', data: '<p>Hello</p>' }

    it('passes with valid data', async () => {
        await expect(templateEditSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when name is empty', async () => {
        await expect(templateEditSchema.validate({ ...valid, name: '' })).rejects.toThrow()
    })

    it('fails when name is missing', async () => {
        const { name: _o, ...rest } = valid // NOSONAR
        await expect(templateEditSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when type is empty', async () => {
        await expect(templateEditSchema.validate({ ...valid, type: '' })).rejects.toThrow()
    })

    it('fails when data is empty', async () => {
        await expect(templateEditSchema.validate({ ...valid, data: '' })).rejects.toThrow()
    })
})
