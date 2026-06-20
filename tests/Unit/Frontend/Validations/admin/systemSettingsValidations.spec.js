import {
    systemSettingsSchema,
    buildFileStorageSchema,
    pdfSettingsSchema,
    webhookUrlSchema,
} from '@/validations/admin/systemSettingsValidations'

describe('systemSettingsSchema', () => {
    const valid = {
        company:          'Acme Corp',
        company_email:    'admin@acme.com',
        website:          'https://acme.com',
        phone:            '+1234567890',
        address:          '123 Main St',
        country:          { id: 1, name: 'USA' },
        state:            { id: 1, name: 'California' },
        default_currency: { id: 1, name: 'USD' },
    }

    it('passes with valid data', async () => {
        await expect(systemSettingsSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when company is empty', async () => {
        await expect(systemSettingsSchema.validate({ ...valid, company: '' })).rejects.toThrow()
    })

    it('fails when company exceeds 50 characters', async () => {
        await expect(systemSettingsSchema.validate({ ...valid, company: 'A'.repeat(51) })).rejects.toThrow()
    })

    it('passes when company is exactly 50 characters', async () => {
        await expect(systemSettingsSchema.validate({ ...valid, company: 'A'.repeat(50) })).resolves.toBeTruthy()
    })

    it('fails when company_email is empty', async () => {
        await expect(systemSettingsSchema.validate({ ...valid, company_email: '' })).rejects.toThrow()
    })

    it('fails when company_email is not a valid email', async () => {
        await expect(systemSettingsSchema.validate({ ...valid, company_email: 'not-an-email' })).rejects.toThrow()
    })

    it('fails when website is empty', async () => {
        await expect(systemSettingsSchema.validate({ ...valid, website: '' })).rejects.toThrow()
    })

    it('fails when website is not a valid URL', async () => {
        await expect(systemSettingsSchema.validate({ ...valid, website: 'not-a-url' })).rejects.toThrow()
    })

    it('fails when phone is empty', async () => {
        await expect(systemSettingsSchema.validate({ ...valid, phone: '' })).rejects.toThrow()
    })

    it('fails when address is empty', async () => {
        await expect(systemSettingsSchema.validate({ ...valid, address: '' })).rejects.toThrow()
    })

    it('fails when country is null', async () => {
        await expect(systemSettingsSchema.validate({ ...valid, country: null })).rejects.toThrow()
    })

    it('fails when state is null', async () => {
        await expect(systemSettingsSchema.validate({ ...valid, state: null })).rejects.toThrow()
    })

    it('fails when default_currency is null', async () => {
        await expect(systemSettingsSchema.validate({ ...valid, default_currency: null })).rejects.toThrow()
    })
})

describe('buildFileStorageSchema - system disk', () => {
    const schema = buildFileStorageSchema('system')

    it('passes with a valid path', async () => {
        await expect(schema.validate({ path: '/var/www/storage' })).resolves.toBeTruthy()
    })

    it('fails when path is empty', async () => {
        await expect(schema.validate({ path: '' })).rejects.toThrow()
    })

    it('fails when path is missing', async () => {
        await expect(schema.validate({})).rejects.toThrow()
    })
})

describe('buildFileStorageSchema - s3 disk', () => {
    const schema = buildFileStorageSchema('s3')
    const valid = {
        s3_bucket:       'my-bucket',
        s3_region:       'us-east-1',
        s3_access_key:   'AKID',
        s3_secret_key:   'secret',
        s3_endpoint_url: 'https://s3.amazonaws.com',
    }

    it('passes with valid S3 data', async () => {
        await expect(schema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when s3_bucket is empty', async () => {
        await expect(schema.validate({ ...valid, s3_bucket: '' })).rejects.toThrow()
    })

    it('fails when s3_region is empty', async () => {
        await expect(schema.validate({ ...valid, s3_region: '' })).rejects.toThrow()
    })

    it('fails when s3_access_key is empty', async () => {
        await expect(schema.validate({ ...valid, s3_access_key: '' })).rejects.toThrow()
    })

    it('fails when s3_secret_key is empty', async () => {
        await expect(schema.validate({ ...valid, s3_secret_key: '' })).rejects.toThrow()
    })

    it('fails when s3_endpoint_url is empty', async () => {
        await expect(schema.validate({ ...valid, s3_endpoint_url: '' })).rejects.toThrow()
    })
})

describe('pdfSettingsSchema', () => {
    const valid = {
        node_path:   '/usr/local/bin/node',
        npm_path:    '/usr/local/bin/npm',
        chrome_path: '/usr/bin/google-chrome',
    }

    it('passes with valid data', async () => {
        await expect(pdfSettingsSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when node_path is empty', async () => {
        await expect(pdfSettingsSchema.validate({ ...valid, node_path: '' })).rejects.toThrow()
    })

    it('fails when npm_path is missing', async () => {
        const { npm_path: _o, ...rest } = valid
        await expect(pdfSettingsSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when chrome_path is empty', async () => {
        await expect(pdfSettingsSchema.validate({ ...valid, chrome_path: '' })).rejects.toThrow()
    })
})

describe('webhookUrlSchema', () => {
    it('passes with a valid URL', async () => {
        await expect(webhookUrlSchema.validate({ editWebhookUrl: 'https://example.com/webhook' })).resolves.toBeTruthy()
    })

    it('fails when editWebhookUrl is empty', async () => {
        await expect(webhookUrlSchema.validate({ editWebhookUrl: '' })).rejects.toThrow()
    })

    it('fails when editWebhookUrl is not a valid URL', async () => {
        await expect(webhookUrlSchema.validate({ editWebhookUrl: 'not-a-url' })).rejects.toThrow()
    })

    it('fails when editWebhookUrl is missing', async () => {
        await expect(webhookUrlSchema.validate({})).rejects.toThrow()
    })
})
