import { setActivePinia, createPinia } from 'pinia'
import { errorHandler, successHandler } from '@/helpers/responseHandler.js'
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

    it('sets fallback message for 422 when message is absent', () => {
        const store = useAlertStore()
        errorHandler(makeErr(422, {}))
        expect(store.type).toBe('danger')
        expect(store.message).toBe('Something went wrong.')
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

    it('sets fallback message for 500 when data.message is undefined', () => {
        const store = useAlertStore()
        errorHandler(makeErr(500, {}))
        expect(store.type).toBe('danger')
        expect(store.message).toBe('Something went wrong.')
    })

    it('sets a danger alert for a 412 response with a message', () => {
        const store = useAlertStore()
        errorHandler(makeErr(412, { message: 'Precondition failed' }))
        expect(store.type).toBe('danger')
        expect(store.message).toBe('Precondition failed')
    })

    it('redirects via router to /404 on a 404 response', () => {
        const push = jest.fn()
        globalThis.__router = { push }
        errorHandler(makeErr(404))
        expect(push).toHaveBeenCalledWith('/404')
        delete globalThis.__router
    })

    it('sets a danger alert for unknown status codes with message', () => {
        const store = useAlertStore()
        errorHandler(makeErr(418, { message: "I'm a teapot" }))
        expect(store.type).toBe('danger')
        expect(store.message).toBe("I'm a teapot")
    })

    it('sets fallback message for network errors (no response)', () => {
        const store = useAlertStore()
        expect(() => errorHandler({})).not.toThrow()
        expect(store.type).toBe('danger')
        expect(store.message).toBe('Something went wrong.')
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

// ── errorHandler field-level validation (setErrors) ─────────────────────────────
// Backend shape: RequestJsonValidation / Handler::invalidJson() send field errors
// as a 412 with message: { field: "msg" } — no `errors` key at all.
describe('errorHandler with setErrors', () => {
    let setErrors

    beforeEach(() => {
        setErrors = jest.fn()
    })

    it('falls back to the alert when message is a plain string, not a field map', () => {
        const store = useAlertStore()
        const err = makeErr(500, { message: 'Server error' })
        errorHandler(err, 'Form', { setErrors })
        expect(store.message).toBe('Server error')
        expect(setErrors).not.toHaveBeenCalled()
    })

    it('falls back to the alert for a non-412 status even with an object message', () => {
        const store = useAlertStore()
        const err = makeErr(400, { message: { name: 'x' } })
        errorHandler(err, 'Form', { setErrors })
        expect(store.message).toBe('x')
        expect(setErrors).not.toHaveBeenCalled()
    })

    it('maps every returned field to setErrors on a 412', () => {
        const err = makeErr(412, {
            message: {
                name: 'The name field is required.',
                email: 'Invalid email.',
            },
        })
        errorHandler(err, 'Form', { setErrors })
        expect(setErrors).toHaveBeenCalledWith({
            name: 'The name field is required.',
            email: 'Invalid email.',
        })
    })

    it('takes the first error message when a field value is an array', () => {
        const err = makeErr(412, {
            message: { title: ['Required', 'Too short'] },
        })
        errorHandler(err, 'Form', { setErrors })
        expect(setErrors).toHaveBeenCalledWith({ title: 'Required' })
    })

    it('does not also show the top alert once field errors are mapped', () => {
        const store = useAlertStore()
        const err = makeErr(412, {
            message: { name: 'Name required' },
        })
        errorHandler(err, 'Comp', { setErrors })
        expect(setErrors).toHaveBeenCalledWith({ name: 'Name required' })
        expect(store.message).toBe('')
    })

    it('flattens the object message into the alert when setErrors is not given', () => {
        const store = useAlertStore()
        const err = makeErr(412, {
            message: { name: 'Name required', email: 'Email required' },
        })
        errorHandler(err, 'Comp')
        expect(store.message).toBe('Name required Email required')
    })
})
