import {
    loginSchema,
    registerSchema,
    forgotSchema,
    resetSchema,
    otpSchema,
    twoFaSchema,
    recoverySchema,
    passwordChecks,
} from '@/validations/client/authSchemas'

describe('loginSchema', () => {
    const valid = { email_username: 'user@example.com', password1: 'password123' }

    it('passes with valid data', async () => {
        await expect(loginSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when email_username is empty', async () => {
        await expect(loginSchema.validate({ ...valid, email_username: '' })).rejects.toThrow()
    })

    it('fails when email_username is missing', async () => {
        const { email_username: _o, ...rest } = valid // NOSONAR
        await expect(loginSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when password1 is empty', async () => {
        await expect(loginSchema.validate({ ...valid, password1: '' })).rejects.toThrow()
    })

    it('passes with a username (non-email)', async () => {
        await expect(loginSchema.validate({ ...valid, email_username: 'myusername' })).resolves.toBeTruthy()
    })
})

describe('registerSchema', () => {
    const valid = {
        first_name:            'John',
        last_name:             'Doe',
        email:                 'john@example.com',
        company:               'Acme',
        address:               '123 Main St',
        country:               { id: 1, name: 'USA' },
        mobile:                '+1234567890',
        password:              'Secure@123',
        password_confirmation: 'Secure@123',
    }

    it('passes with valid data', async () => {
        await expect(registerSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when first_name is empty', async () => {
        await expect(registerSchema.validate({ ...valid, first_name: '' })).rejects.toThrow()
    })

    it('fails when first_name does not match NAME_RE (starts with digit)', async () => {
        await expect(registerSchema.validate({ ...valid, first_name: '1John' })).rejects.toThrow()
    })

    it('fails when last_name is empty', async () => {
        await expect(registerSchema.validate({ ...valid, last_name: '' })).rejects.toThrow()
    })

    it('fails when email is empty', async () => {
        await expect(registerSchema.validate({ ...valid, email: '' })).rejects.toThrow()
    })

    it('fails when email has invalid format (double dot)', async () => {
        await expect(registerSchema.validate({ ...valid, email: 'a..b@example.com' })).rejects.toThrow()
    })

    it('fails when email has no domain', async () => {
        await expect(registerSchema.validate({ ...valid, email: 'notanemail' })).rejects.toThrow()
    })

    it('fails when company is empty', async () => {
        await expect(registerSchema.validate({ ...valid, company: '' })).rejects.toThrow()
    })

    it('fails when country is null', async () => {
        await expect(registerSchema.validate({ ...valid, country: null })).rejects.toThrow()
    })

    it('fails when password does not meet strength requirements', async () => {
        await expect(registerSchema.validate({ ...valid, password: 'weakpass', password_confirmation: 'weakpass' })).rejects.toThrow()
    })

    it('fails when password_confirmation does not match password', async () => {
        await expect(registerSchema.validate({ ...valid, password_confirmation: 'Secure@999' })).rejects.toThrow()
    })
})

describe('forgotSchema', () => {
    it('passes with a valid email', async () => {
        await expect(forgotSchema.validate({ email: 'user@example.com' })).resolves.toBeTruthy()
    })

    it('fails when email is empty', async () => {
        await expect(forgotSchema.validate({ email: '' })).rejects.toThrow()
    })

    it('fails when email has invalid format', async () => {
        await expect(forgotSchema.validate({ email: 'notanemail' })).rejects.toThrow()
    })

    it('fails when email has consecutive dots', async () => {
        await expect(forgotSchema.validate({ email: 'a..b@example.com' })).rejects.toThrow()
    })
})

describe('resetSchema', () => {
    const valid = { password: 'Secure@123', password_confirmation: 'Secure@123' }

    it('passes with matching strong passwords', async () => {
        await expect(resetSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when password is empty', async () => {
        await expect(resetSchema.validate({ ...valid, password: '' })).rejects.toThrow()
    })

    it('fails when password does not meet STRONG_PASS regex', async () => {
        await expect(resetSchema.validate({ ...valid, password: 'weakpass', password_confirmation: 'weakpass' })).rejects.toThrow()
    })

    it('fails when password_confirmation does not match', async () => {
        await expect(resetSchema.validate({ ...valid, password_confirmation: 'Different@1' })).rejects.toThrow()
    })

    it('fails when password_confirmation is empty', async () => {
        await expect(resetSchema.validate({ ...valid, password_confirmation: '' })).rejects.toThrow()
    })
})

describe('otpSchema', () => {
    it('passes with a 6-digit OTP', async () => {
        await expect(otpSchema.validate({ otp: '123456' })).resolves.toBeTruthy()
    })

    it('fails when otp is empty', async () => {
        await expect(otpSchema.validate({ otp: '' })).rejects.toThrow()
    })

    it('fails when otp has fewer than 6 digits', async () => {
        await expect(otpSchema.validate({ otp: '12345' })).rejects.toThrow()
    })

    it('fails when otp has more than 6 digits', async () => {
        await expect(otpSchema.validate({ otp: '1234567' })).rejects.toThrow()
    })

    it('fails when otp contains non-digits', async () => {
        await expect(otpSchema.validate({ otp: '12345a' })).rejects.toThrow()
    })
})

describe('twoFaSchema', () => {
    it('passes with a 6-digit TOTP', async () => {
        await expect(twoFaSchema.validate({ totp: '654321' })).resolves.toBeTruthy()
    })

    it('fails when totp is empty', async () => {
        await expect(twoFaSchema.validate({ totp: '' })).rejects.toThrow()
    })

    it('fails when totp has only 5 digits', async () => {
        await expect(twoFaSchema.validate({ totp: '12345' })).rejects.toThrow()
    })

    it('fails when totp contains letters', async () => {
        await expect(twoFaSchema.validate({ totp: 'abc123' })).rejects.toThrow()
    })
})

describe('recoverySchema', () => {
    it('passes with a valid recovery code', async () => {
        await expect(recoverySchema.validate({ rec_code: 'REC-ABC-123' })).resolves.toBeTruthy()
    })

    it('fails when rec_code is empty', async () => {
        await expect(recoverySchema.validate({ rec_code: '' })).rejects.toThrow()
    })

    it('fails when rec_code is missing', async () => {
        await expect(recoverySchema.validate({})).rejects.toThrow()
    })

    it('fails when rec_code is only whitespace', async () => {
        await expect(recoverySchema.validate({ rec_code: '   ' })).rejects.toThrow()
    })
})

describe('passwordChecks', () => {
    it('returns all false for empty string', () => {
        const result = passwordChecks('')
        expect(result.length).toBe(false)
        expect(result.lower).toBe(false)
        expect(result.upper).toBe(false)
        expect(result.number).toBe(false)
        expect(result.special).toBe(false)
    })

    it('detects length requirement (8–16 chars)', () => {
        expect(passwordChecks('abcdefgh').length).toBe(true)
        expect(passwordChecks('abc').length).toBe(false)
        expect(passwordChecks('a'.repeat(17)).length).toBe(false)
    })

    it('detects lowercase letters', () => {
        expect(passwordChecks('abc').lower).toBe(true)
        expect(passwordChecks('ABC').lower).toBe(false)
    })

    it('detects uppercase letters', () => {
        expect(passwordChecks('ABC').upper).toBe(true)
        expect(passwordChecks('abc').upper).toBe(false)
    })

    it('detects numbers', () => {
        expect(passwordChecks('abc1').number).toBe(true)
        expect(passwordChecks('abcd').number).toBe(false)
    })

    it('detects special characters', () => {
        expect(passwordChecks('abc@').special).toBe(true)
        expect(passwordChecks('abcd').special).toBe(false)
    })

    it('returns all true for a fully compliant password', () => {
        const result = passwordChecks('Secure@1')
        expect(result.length).toBe(true)
        expect(result.lower).toBe(true)
        expect(result.upper).toBe(true)
        expect(result.number).toBe(true)
        expect(result.special).toBe(true)
    })
})
