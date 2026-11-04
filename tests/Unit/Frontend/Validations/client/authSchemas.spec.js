import {
    loginSchema,
    registerSchema,
    socialCompleteSchema,
    forgotSchema,
    resetSchema,
    otpSchema,
    twoFaSchema,
    recoverySchema,
    passwordChecks,
} from '@/validations/client/authSchemas'

describe('loginSchema', () => {
    const valid = { login: 'user@example.com', password: 'password123' }

    it('passes with valid data', async () => {
        await expect(loginSchema.validate(valid)).resolves.toBeTruthy()
    })

    it('fails when login is empty', async () => {
        await expect(loginSchema.validate({ ...valid, login: '' })).rejects.toThrow()
    })

    it('fails when login is missing', async () => {
        const { login: _o, ...rest } = valid // NOSONAR
        await expect(loginSchema.validate(rest)).rejects.toThrow()
    })

    it('fails when password is empty', async () => {
        await expect(loginSchema.validate({ ...valid, password: '' })).rejects.toThrow()
    })

    it('passes with a username (non-email)', async () => {
        await expect(loginSchema.validate({ ...valid, login: 'myusername' })).resolves.toBeTruthy()
    })
})

describe('registerSchema', () => {
    const valid = {
        first_name:            'John',
        last_name:             'Doe',
        email:                 'john@example.com',
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

    it('does not ask for company, address or country (collected later)', async () => {
        await expect(registerSchema.validate({ ...valid, company: '', address: '', country: null })).resolves.toBeTruthy()
    })

    it('fails when password does not meet strength requirements', async () => {
        await expect(registerSchema.validate({ ...valid, password: 'weakpass', password_confirmation: 'weakpass' })).rejects.toThrow()
    })

    it('fails when password_confirmation does not match password', async () => {
        await expect(registerSchema.validate({ ...valid, password_confirmation: 'Secure@999' })).rejects.toThrow()
    })
})

describe('socialCompleteSchema', () => {
    const valid = { first_name: 'John', last_name: 'Doe', email: 'john@example.com', mobile: '+1234567890' }

    it('passes with name, email and mobile only (no company/address/country)', async () => {
        await expect(socialCompleteSchema.validate(valid)).resolves.toBeTruthy()
    })

    it.each(['first_name', 'email', 'mobile'])('fails when %s is empty', async (field) => {
        await expect(socialCompleteSchema.validate({ ...valid, [field]: '' })).rejects.toThrow()
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
        await expect(twoFaSchema.validate({ code: '654321' })).resolves.toBeTruthy()
    })

    it('fails when code is empty', async () => {
        await expect(twoFaSchema.validate({ code: '' })).rejects.toThrow()
    })

    it('fails when code has only 5 digits', async () => {
        await expect(twoFaSchema.validate({ code: '12345' })).rejects.toThrow()
    })

    it('fails when code contains letters', async () => {
        await expect(twoFaSchema.validate({ code: 'abc123' })).rejects.toThrow()
    })
})

describe('recoverySchema', () => {
    it('passes with a valid recovery code', async () => {
        await expect(recoverySchema.validate({ recovery_code: 'REC-ABC-123' })).resolves.toBeTruthy()
    })

    it('fails when recovery_code is empty', async () => {
        await expect(recoverySchema.validate({ recovery_code: '' })).rejects.toThrow()
    })

    it('fails when recovery_code is missing', async () => {
        await expect(recoverySchema.validate({})).rejects.toThrow()
    })

    it('fails when recovery_code is only whitespace', async () => {
        await expect(recoverySchema.validate({ recovery_code: '   ' })).rejects.toThrow()
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

    it('detects length requirement (8–64 chars)', () => {
        expect(passwordChecks('abcdefgh').length).toBe(true)
        expect(passwordChecks('abc').length).toBe(false)
        expect(passwordChecks('a'.repeat(64)).length).toBe(true)
        expect(passwordChecks('a'.repeat(65)).length).toBe(false)
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

    it('detects special characters (any symbol or space, as Laravel does)', () => {
        expect(passwordChecks('abc@').special).toBe(true)
        expect(passwordChecks('abc&').special).toBe(true)
        expect(passwordChecks('abc-').special).toBe(true)
        expect(passwordChecks('abcd').special).toBe(false)
        expect(passwordChecks('ab cd').special).toBe(true) // like Laravel's symbols(): spaces count
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
