import {
    licenseSchema,
    bannedHostSchema,
    whitelistSchema,
    installationSchema,
    buildNotificationsSchema,
} from '@/validations/admin/licenseValidations'

describe('licenseSchema', () => {
    const valid = {
        product:              'My Product',
        client:               'John Doe',
        license_code:         'LIC-ABC-123',
        license_expire_date:  '2025-12-31',
        license_updates_date: '2025-06-30',
        license_support_date: '2025-03-31',
    }

    it('passes with valid data', async () => {
        await expect(licenseSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when product is empty', async () => {
        await expect(licenseSchema.validate({ ...valid, product: '' })).rejects.toThrow()
    })

    it('fails when product is missing', async () => {
        const { product: _o, ...rest } = valid // NOSONAR
        await expect(licenseSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when client is empty', async () => {
        await expect(licenseSchema.validate({ ...valid, client: '' })).rejects.toThrow()
    })

    it('fails when license_code is empty', async () => {
        await expect(licenseSchema.validate({ ...valid, license_code: '' })).rejects.toThrow()
    })

    it('fails when license_expire_date is null', async () => {
        await expect(licenseSchema.validate({ ...valid, license_expire_date: null })).rejects.toThrow()
    })

    it('fails when license_expire_date is empty string', async () => {
        await expect(licenseSchema.validate({ ...valid, license_expire_date: '' })).rejects.toThrow()
    })

    it('fails when license_updates_date is null', async () => {
        await expect(licenseSchema.validate({ ...valid, license_updates_date: null })).rejects.toThrow()
    })

    it('fails when license_support_date is null', async () => {
        await expect(licenseSchema.validate({ ...valid, license_support_date: null })).rejects.toThrow()
    })
})

describe('bannedHostSchema', () => {
    it('passes with a valid IP', async () => {
        await expect(bannedHostSchema.validate({ banned_host_ip: '192.168.1.1' })).resolves.toBeTruthy() // NOSONAR
    })

    it('fails when banned_host_ip is empty', async () => {
        await expect(bannedHostSchema.validate({ banned_host_ip: '' })).rejects.toThrow()
    })

    it('fails when banned_host_ip is missing', async () => {
        await expect(bannedHostSchema.validate({})).rejects.toThrow()
    })

    it('passes with a valid IPv6 address', async () => {
        await expect(bannedHostSchema.validate({ banned_host_ip: '::1' })).resolves.toBeTruthy()
    })

    it('fails when banned_host_ip is not a valid IP format', async () => {
        await expect(bannedHostSchema.validate({ banned_host_ip: 'not-an-ip' })).rejects.toThrow()
    })
})

describe('whitelistSchema', () => {
    it('passes with a valid IP', async () => {
        await expect(whitelistSchema.validate({ whitelist_host_ip: '10.0.0.1' })).resolves.toBeTruthy() // NOSONAR
    })

    it('fails when whitelist_host_ip is empty', async () => {
        await expect(whitelistSchema.validate({ whitelist_host_ip: '' })).rejects.toThrow()
    })

    it('fails when whitelist_host_ip is missing', async () => {
        await expect(whitelistSchema.validate({})).rejects.toThrow()
    })
})

describe('installationSchema', () => {
    it('passes with a valid IP', async () => {
        await expect(installationSchema.validate({ installation_ip: '203.0.113.5' })).resolves.toBeTruthy() // NOSONAR
    })

    it('fails when installation_ip is empty', async () => {
        await expect(installationSchema.validate({ installation_ip: '' })).rejects.toThrow()
    })

    it('fails when installation_ip is missing', async () => {
        await expect(installationSchema.validate({})).rejects.toThrow()
    })
})

describe('buildNotificationsSchema', () => {
    it('passes when all dynamic fields are present', async () => {
        const schema = buildNotificationsSchema(['email_to', 'email_cc'])
        await expect(schema.validate({ email_to: 'a@b.com', email_cc: 'c@d.com' })).resolves.toBeTruthy()
    })

    it('fails when a dynamic field is empty', async () => {
        const schema = buildNotificationsSchema(['email_to'])
        await expect(schema.validate({ email_to: '' })).rejects.toThrow()
    })

    it('fails when a dynamic field is missing', async () => {
        const schema = buildNotificationsSchema(['email_to'])
        await expect(schema.validate({})).rejects.toThrow()
    })

    it('handles an empty fields array', async () => {
        const schema = buildNotificationsSchema([])
        await expect(schema.validate({})).resolves.toBeTruthy()
    })
})
