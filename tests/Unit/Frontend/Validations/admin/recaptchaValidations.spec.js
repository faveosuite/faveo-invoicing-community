import { buildRecaptchaSchema } from '@/validations/admin/recaptchaValidations'

// Use v2_checkbox for the base fields test: it adds v2_site_key + v2_secret_key
// but not the v3 fields. We include those in the valid object.
describe('buildRecaptchaSchema - base enum fields (v2_checkbox)', () => {
    const schema = buildRecaptchaSchema({ captcha_version: 'v2_checkbox', failover_action: 'none' })

    const valid = {
        captcha_version: 'v2_checkbox',
        failover_action: 'none',
        theme:           'light',
        size:            'normal',
        badge_position:  'bottomright',
        v2_site_key:     'v2-site',
        v2_secret_key:   'v2-secret',
    }

    it('passes with valid data', async () => {
        await expect(schema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when captcha_version is missing', async () => {
        const { captcha_version: _o, ...rest } = valid // NOSONAR
        await expect(schema.validate(rest)).rejects.toThrow()
    })

    it('fails when captcha_version is an invalid value', async () => {
        await expect(schema.validate({ ...valid, captcha_version: 'v4_invisible' })).rejects.toThrow()
    })

    it('fails when failover_action is an invalid value', async () => {
        await expect(schema.validate({ ...valid, failover_action: 'block' })).rejects.toThrow()
    })

    it('fails when theme is an invalid value', async () => {
        await expect(schema.validate({ ...valid, theme: 'red' })).rejects.toThrow()
    })

    it('fails when size is an invalid value', async () => {
        await expect(schema.validate({ ...valid, size: 'huge' })).rejects.toThrow()
    })

    it('fails when badge_position is an invalid value', async () => {
        await expect(schema.validate({ ...valid, badge_position: 'topright' })).rejects.toThrow()
    })

    it('passes with dark theme', async () => {
        await expect(schema.validate({ ...valid, theme: 'dark' })).resolves.toBeTruthy()
    })

    it('passes with compact size', async () => {
        await expect(schema.validate({ ...valid, size: 'compact' })).resolves.toBeTruthy()
    })
})

describe('buildRecaptchaSchema - v3_invisible (needsV3, no v2 failover)', () => {
    const schema = buildRecaptchaSchema({ captcha_version: 'v3_invisible', failover_action: 'none' })

    const valid = {
        captcha_version: 'v3_invisible',
        failover_action: 'none',
        theme:           'light',
        size:            'normal',
        badge_position:  'bottomright',
        v3_site_key:     'v3-site',
        v3_secret_key:   'v3-secret',
        score_threshold: 0.5,
    }

    it('passes with all v3 fields', async () => {
        await expect(schema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when v3_site_key is empty', async () => {
        await expect(schema.validate({ ...valid, v3_site_key: '' })).rejects.toThrow()
    })

    it('fails when v3_secret_key is missing', async () => {
        const { v3_secret_key: _o, ...rest } = valid // NOSONAR
        await expect(schema.validate(rest)).rejects.toThrow()
    })

    it('fails when score_threshold is missing', async () => {
        const { score_threshold: _o, ...rest } = valid // NOSONAR
        await expect(schema.validate(rest)).rejects.toThrow()
    })

    it('passes with score_threshold at 0 (min boundary)', async () => {
        await expect(schema.validate({ ...valid, score_threshold: 0 })).resolves.toBeTruthy()
    })

    it('passes with score_threshold at 1 (max boundary)', async () => {
        await expect(schema.validate({ ...valid, score_threshold: 1 })).resolves.toBeTruthy()
    })

    it('fails when score_threshold exceeds 1', async () => {
        await expect(schema.validate({ ...valid, score_threshold: 1.1 })).rejects.toThrow()
    })

    it('fails when score_threshold is below 0', async () => {
        await expect(schema.validate({ ...valid, score_threshold: -0.1 })).rejects.toThrow()
    })
})

describe('buildRecaptchaSchema - v2_invisible (needsV2)', () => {
    const schema = buildRecaptchaSchema({ captcha_version: 'v2_invisible', failover_action: 'none' })

    const valid = {
        captcha_version: 'v2_invisible',
        failover_action: 'none',
        theme:           'light',
        size:            'normal',
        badge_position:  'bottomright',
        v2_site_key:     'v2-site',
        v2_secret_key:   'v2-secret',
    }

    it('passes with v2 fields', async () => {
        await expect(schema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when v2_site_key is empty', async () => {
        await expect(schema.validate({ ...valid, v2_site_key: '' })).rejects.toThrow()
    })

    it('fails when v2_secret_key is missing', async () => {
        const { v2_secret_key: _o, ...rest } = valid // NOSONAR
        await expect(schema.validate(rest)).rejects.toThrow()
    })
})

describe('buildRecaptchaSchema - v3 with v2 failover (needsV3 + needsV2)', () => {
    const schema = buildRecaptchaSchema({ captcha_version: 'v3_invisible', failover_action: 'v2_checkbox' })

    const valid = {
        captcha_version: 'v3_invisible',
        failover_action: 'v2_checkbox',
        theme:           'light',
        size:            'normal',
        badge_position:  'bottomright',
        v3_site_key:     'v3-site',
        v3_secret_key:   'v3-secret',
        score_threshold: 0.5,
        v2_site_key:     'v2-site',
        v2_secret_key:   'v2-secret',
    }

    it('passes when both v3 and v2 keys are present', async () => {
        await expect(schema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when v2_site_key is missing', async () => {
        const { v2_site_key: _o, ...rest } = valid // NOSONAR
        await expect(schema.validate(rest)).rejects.toThrow()
    })

    it('fails when v3_site_key is missing', async () => {
        const { v3_site_key: _o, ...rest } = valid // NOSONAR
        await expect(schema.validate(rest)).rejects.toThrow()
    })
})
