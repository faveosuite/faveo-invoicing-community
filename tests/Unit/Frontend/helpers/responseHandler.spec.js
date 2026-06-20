import { setActivePinia, createPinia } from 'pinia'
import { errorHandler, successHandler, applyServerValidation } from '@/helpers/responseHandler.js'
import { useAlertStore } from '@/core/stores/alert.js'

// jest.setup.js already calls setActivePinia(createPinia()) in beforeEach,
// but we do it here too for clarity and in case this file runs standalone.
beforeEach(() => {
    setActivePinia(createPinia())
})

// ── Helpers ───────────────────────────────────────────────────────────────────
const makeErr = (status, data = {}) => ({ response: { status, data } })

// ── errorHandler ──────────────────────────────────────────────────────────────
describe('errorHandler', () => {
    it('does nothing when err has duplicateRequestRejection flag', () => {
        const store = useAlertStore()
        errorHandler({ duplicateRequestRejection: true })
        expect(store.message).toBe('')
    })

    it('sets a danger alert for a 422 response with a message', () => {
        const store = useAlertStore()
        errorHandler(makeErr(422, { message: 'Validation failed' }))
        expect(store.type).toBe('danger')
        expect(store.message).toBe('Validation failed')
    })

    it('does not set alert for 422 when message is absent', () => {
        const store = useAlertStore()
        errorHandler(makeErr(422, {}))
        expect(store.message).toBe('')
    })

    it('sets the component_name on a 422 alert', () => {
        const store = useAlertStore()
        errorHandler(makeErr(422, { message: 'Error' }), 'TestComponent')
        expect(store.component_name).toBe('TestComponent')
    })

    it('sets a danger alert for a 500 response with a message', () => {
        const store = useAlertStore()
        errorHandler(makeErr(500, { message: 'Internal server error' }))
        expect(store.type).toBe('danger')
        expect(store.message).toBe('Internal server error')
    })

    it('sets a danger alert for a 400 response', () => {
        const store = useAlertStore()
        errorHandler(makeErr(400, { message: 'Bad request' }))
        expect(store.message).toBe('Bad request')
    })

    it('sets a danger alert for a 401 response', () => {
        const store = useAlertStore()
        errorHandler(makeErr(401, { message: 'Unauthorized' }))
        expect(store.message).toBe('Unauthorized')
    })

    it('sets a danger alert for a 429 response', () => {
        const store = useAlertStore()
        errorHandler(makeErr(429, { message: 'Too many requests' }))
        expect(store.message).toBe('Too many requests')
    })

    it('does not set alert for 500 when data.message is undefined', () => {
        const store = useAlertStore()
        errorHandler(makeErr(500, {}))
        expect(store.message).toBe('')
    })

    it('sets a danger alert for a 412 response with a message', () => {
        const store = useAlertStore()
        errorHandler(makeErr(412, { message: 'Precondition failed' }))
        expect(store.type).toBe('danger')
        expect(store.message).toBe('Precondition failed')
    })

    it('redirects to /404 on a 404 response', () => {
        // window.axios.defaults.baseURL is used for the redirect
        window.axios = { defaults: { baseURL: 'http://localhost' } }
        const originalLocation = window.location
        delete window.location
        window.location = ''

        errorHandler(makeErr(404))

        expect(window.location).toBe('http://localhost/404')
        window.location = originalLocation
    })

    it('does not set alert for unknown status codes', () => {
        const store = useAlertStore()
        errorHandler(makeErr(418, { message: "I'm a teapot" }))
        expect(store.message).toBe('')
    })

    it('does not throw when err has no response (network error)', () => {
        const store = useAlertStore()
        expect(() => errorHandler({})).not.toThrow()
        expect(store.message).toBe('')
    })

    it('throws a TypeError when err is null (no null-guard in source)', () => {
        expect(() => errorHandler(null)).toThrow(TypeError)
    })

    it('defaults componentName to empty string', () => {
        const store = useAlertStore()
        errorHandler(makeErr(422, { message: 'Err' }))
        expect(store.component_name).toBe('')
    })
})

// ── successHandler ────────────────────────────────────────────────────────────
describe('successHandler', () => {
    it('sets a success alert on a 200 response with a message', () => {
        const store = useAlertStore()
        successHandler({ status: 200, data: { message: 'Saved successfully' } })
        expect(store.type).toBe('success')
        expect(store.message).toBe('Saved successfully')
    })

    it('sets a success alert on a 201 response', () => {
        const store = useAlertStore()
        successHandler({ status: 201, data: { message: 'Created' } })
        expect(store.type).toBe('success')
        expect(store.message).toBe('Created')
    })

    it('does not set alert when message is undefined in response data', () => {
        const store = useAlertStore()
        successHandler({ status: 200, data: {} })
        expect(store.message).toBe('')
    })

    it('does not set alert for non-200/201 status codes', () => {
        const store = useAlertStore()
        successHandler({ status: 204, data: { message: 'No content' } })
        expect(store.message).toBe('')
    })

    it('sets the component_name on the alert', () => {
        const store = useAlertStore()
        successHandler({ status: 200, data: { message: 'Done' } }, 'MyComponent')
        expect(store.component_name).toBe('MyComponent')
    })

    it('defaults componentName to empty string', () => {
        const store = useAlertStore()
        successHandler({ status: 200, data: { message: 'Done' } })
        expect(store.component_name).toBe('')
    })
})

// ── applyServerValidation ─────────────────────────────────────────────────────
describe('applyServerValidation', () => {
    let setErrors

    beforeEach(() => {
        setErrors = jest.fn()
    })

    it('delegates to errorHandler when no errors key in response', () => {
        const store = useAlertStore()
        const err = makeErr(500, { message: 'Server error' })
        applyServerValidation(err, { setErrors, fields: ['name'], component: 'Form' })
        expect(store.message).toBe('Server error')
        expect(setErrors).not.toHaveBeenCalled()
    })

    it('delegates to errorHandler when err has no response', () => {
        // no response.data.errors — goes straight to errorHandler; no alert because no status
        applyServerValidation({}, { setErrors, fields: [] })
        expect(setErrors).not.toHaveBeenCalled()
    })

    it('maps known fields to setErrors', () => {
        const err = makeErr(422, {
            errors: {
                name: ['The name field is required.'],
                email: ['Invalid email.'],
            },
        })
        applyServerValidation(err, { setErrors, fields: ['name', 'email'] })
        expect(setErrors).toHaveBeenCalledWith({
            name: 'The name field is required.',
            email: 'Invalid email.',
        })
    })

    it('takes the first error message when errors value is an array', () => {
        const err = makeErr(422, {
            errors: { title: ['Required', 'Too short'] },
        })
        applyServerValidation(err, { setErrors, fields: ['title'] })
        expect(setErrors).toHaveBeenCalledWith({ title: 'Required' })
    })

    it('passes through a string error message directly (not wrapped in array)', () => {
        const err = makeErr(422, {
            errors: { title: 'Required' },
        })
        applyServerValidation(err, { setErrors, fields: ['title'] })
        expect(setErrors).toHaveBeenCalledWith({ title: 'Required' })
    })

    it('does not call setErrors for fields not in the known fields list', () => {
        const err = makeErr(422, {
            errors: { honeypot: ['Spam detected'] },
        })
        applyServerValidation(err, { setErrors, fields: ['name'] })
        expect(setErrors).not.toHaveBeenCalled()
    })

    it('calls errorHandler for unknown field errors (surfaces them via alert)', () => {
        const store = useAlertStore()
        const err = makeErr(422, {
            message: 'Validation failed',
            errors: { hidden_token: ['Invalid token'] },
        })
        applyServerValidation(err, { setErrors, fields: ['name'], component: 'Comp' })
        // unknown field → errorHandler → alert store gets the 422 message
        expect(store.message).toBe('Validation failed')
    })

    it('handles a mix of known and unknown fields correctly', () => {
        const store = useAlertStore()
        const err = makeErr(422, {
            message: 'Validation failed',
            errors: {
                name: ['Name required'],
                honeypot: ['Spam'],
            },
        })
        applyServerValidation(err, { setErrors, fields: ['name'], component: 'Comp' })
        expect(setErrors).toHaveBeenCalledWith({ name: 'Name required' })
        // unknown field → also calls errorHandler
        expect(store.message).toBe('Validation failed')
    })

    it('uses empty array as default for fields option', () => {
        const err = makeErr(422, {
            message: 'Err',
            errors: { name: ['Required'] },
        })
        // all fields become unknown → errorHandler called, setErrors not
        applyServerValidation(err, { setErrors })
        expect(setErrors).not.toHaveBeenCalled()
    })

    it('uses empty string as default for component option', () => {
        const store = useAlertStore()
        const err = makeErr(422, {
            message: 'Err',
            errors: { unknown_field: ['Bad'] },
        })
        applyServerValidation(err, { setErrors })
        expect(store.component_name).toBe('')
    })
})
