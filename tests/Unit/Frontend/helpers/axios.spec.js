jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))

import http, { parseErrorMessage, parseValidationErrors } from '@/plugins/axios'

// ── parseErrorMessage ────────────────────────────────────────────────────────
describe('parseErrorMessage()', () => {
    it('joins first messages from validation errors on 422', () => {
        const err = { response: { status: 422, data: { errors: { name: ['Required'], email: ['Invalid'] } } } }
        expect(parseErrorMessage(err)).toBe('Required Invalid')
    })

    it('joins first messages from validation errors on 412', () => {
        const err = { response: { status: 412, data: { errors: { field: ['Too short'] } } } }
        expect(parseErrorMessage(err)).toBe('Too short')
    })

    it('falls back to res.message when errors object is empty', () => {
        const err = { response: { status: 422, data: { errors: {}, message: 'Validation failed' } } }
        expect(parseErrorMessage(err)).toBe('Validation failed')
    })

    it('falls back to "Validation failed." when errors is empty and no message', () => {
        const err = { response: { status: 422, data: { errors: {} } } }
        expect(parseErrorMessage(err)).toBe('Validation failed.')
    })

    it('returns res.message for non-validation status codes', () => {
        const err = { response: { status: 500, data: { message: 'Server error' } } }
        expect(parseErrorMessage(err)).toBe('Server error')
    })

    it('returns "Something went wrong." when res.message is absent', () => {
        const err = { response: { status: 500, data: {} } }
        expect(parseErrorMessage(err)).toBe('Something went wrong.')
    })

    it('returns "Something went wrong." when response data is missing', () => {
        const err = { response: { status: 500 } }
        expect(parseErrorMessage(err)).toBe('Something went wrong.')
    })
})

// ── parseValidationErrors ────────────────────────────────────────────────────
describe('parseValidationErrors()', () => {
    it('returns field-keyed first-error map for 422 responses', () => {
        const err = { response: { status: 422, data: { errors: { name: ['Required', 'Too short'], email: ['Invalid'] } } } }
        expect(parseValidationErrors(err)).toEqual({ name: 'Required', email: 'Invalid' })
    })

    it('returns field-keyed first-error map for 412 responses', () => {
        const err = { response: { status: 412, data: { errors: { field: ['Must be > 0'] } } } }
        expect(parseValidationErrors(err)).toEqual({ field: 'Must be > 0' })
    })

    it('returns null for non-validation status codes', () => {
        const err = { response: { status: 500, data: { message: 'Error' } } }
        expect(parseValidationErrors(err)).toBeNull()
    })

    it('returns null when response has no errors key', () => {
        const err = { response: { status: 422, data: { message: 'Error' } } }
        expect(parseValidationErrors(err)).toBeNull()
    })

    it('returns null for network errors with no response', () => {
        const err = { message: 'Network Error' }
        expect(parseValidationErrors(err)).toBeNull()
    })
})

// ── Request interceptor — CSRF token ─────────────────────────────────────────
describe('axios request interceptor', () => {
    it('attaches X-CSRF-TOKEN header from the meta tag', async () => {
        globalThis.mockHttp.onGet('/test-csrf').reply(200, {})
        await http.get('/test-csrf')
        const request = globalThis.mockHttp.history.get.find(r => r.url === '/test-csrf')
        expect(request.headers['X-CSRF-TOKEN']).toBe('test-csrf-token')
    })
})

// ── Response error interceptor ────────────────────────────────────────────────
describe('axios response error interceptor', () => {
    let originalHref, originalPathname

    beforeEach(() => {
        originalHref = globalThis.location.href
        originalPathname = globalThis.location.pathname
    })

    afterEach(() => {
        Object.defineProperty(window, 'location', {
            writable: true,
            value: { href: originalHref, pathname: originalPathname },
        })
    })

    it('redirects to /login on 401 when not on login page', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet('/protected').reply(401)
        Object.defineProperty(window, 'location', {
            writable: true,
            value: { href: 'http://localhost/', pathname: '/' },
        })
        try { await http.get('/protected') } catch { /* expected */ }
        expect(globalThis.location.href).toContain('/login')
    })

    it('does not redirect on 401 when _skipAuthRedirect is set', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet('/hydrate').reply(401)
        Object.defineProperty(window, 'location', {
            writable: true,
            value: { href: 'http://localhost/', pathname: '/' },
        })
        try {
            await http.get('/hydrate', { _skipAuthRedirect: true })
        } catch { /* expected */ }
        expect(globalThis.location.href).not.toContain('/login')
    })

    it('rejects the promise for non-401/419 errors', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet('/server-error').reply(500, { message: 'Server error' })
        await expect(http.get('/server-error')).rejects.toThrow()
    })

    it('retries the request once on 419 with a fresh CSRF token', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet('/csrf-expired')
            .replyOnce(419, {})
            .onGet('/csrf-expired')
            .replyOnce(200, { ok: true })

        const res = await http.get('/csrf-expired')
        expect(res.status).toBe(200)
        const requests = globalThis.mockHttp.history.get.filter(r => r.url === '/csrf-expired')
        expect(requests.length).toBe(2)
    })
})

describe('parseErrorMessage — additional branches', () => {
    it('returns "Something went wrong." when response is undefined', () => {
        expect(parseErrorMessage({ response: undefined })).toBe('Something went wrong.')
    })

    it('returns res.message for 404 (non-validation)', () => {
        expect(parseErrorMessage({ response: { status: 404, data: { message: 'Not found' } } })).toBe('Not found')
    })

    it('falls back to "Validation failed." when 412 errors is empty and no message', () => {
        const err = { response: { status: 412, data: { errors: {}, message: '' } } }
        expect(parseErrorMessage(err)).toBe('Validation failed.')
    })
})

describe('parseValidationErrors — additional branches', () => {
    it('returns field errors for 412 status', () => {
        const err = { response: { status: 412, data: { errors: { name: ['Required'] } } } }
        expect(parseValidationErrors(err)).toEqual({ name: 'Required' })
    })

    it('returns null when no errors key on 422', () => {
        expect(parseValidationErrors({ response: { status: 422, data: { message: 'Error' } } })).toBeNull()
    })
})
