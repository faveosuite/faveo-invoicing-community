import * as yup from 'yup'
import { scrollToFirstError, validateForm } from '@/helpers/formUtils.js'

// ── scrollToFirstError ────────────────────────────────────────────────────────
describe('scrollToFirstError', () => {
    afterEach(() => {
        document.body.innerHTML = ''
    })

    it('scrolls to the first .is-invalid element when one exists', async () => {
        document.body.innerHTML = '<input class="is-invalid" />'
        const el = document.querySelector('.is-invalid')
        el.scrollIntoView = jest.fn()

        await scrollToFirstError()

        expect(el.scrollIntoView).toHaveBeenCalledWith({ behavior: 'smooth', block: 'center' })
    })

    it('does not throw when no .is-invalid element exists', async () => {
        document.body.innerHTML = '<input class="form-control" />'
        await expect(scrollToFirstError()).resolves.toBeUndefined()
    })

    it('does not throw when the DOM is completely empty', async () => {
        document.body.innerHTML = ''
        await expect(scrollToFirstError()).resolves.toBeUndefined()
    })

    it('scrolls to the first element when multiple .is-invalid elements exist', async () => {
        document.body.innerHTML = `
            <input id="first" class="is-invalid" />
            <input id="second" class="is-invalid" />
        `
        const first = document.querySelector('#first')
        const second = document.querySelector('#second')
        first.scrollIntoView = jest.fn()
        second.scrollIntoView = jest.fn()

        await scrollToFirstError()

        expect(first.scrollIntoView).toHaveBeenCalledTimes(1)
        expect(second.scrollIntoView).not.toHaveBeenCalled()
    })
})

// ── validateForm ──────────────────────────────────────────────────────────────
describe('validateForm', () => {
    let setErrors

    const nameSchema = yup.object({
        name: yup.string().required('Name is required'),
        email: yup.string().email('Invalid email').required('Email is required'),
    })

    beforeEach(() => {
        setErrors = jest.fn()
        // Provide a .is-invalid target so scrollToFirstError does not throw
        document.body.innerHTML = '<input class="is-invalid" />'
        const el = document.querySelector('.is-invalid')
        el.scrollIntoView = jest.fn()
    })

    afterEach(() => {
        document.body.innerHTML = ''
    })

    it('returns true when the schema passes validation', async () => {
        const result = await validateForm(nameSchema, { name: 'Alice', email: 'alice@example.com' }, setErrors)
        expect(result).toBe(true)
    })

    it('does not call setErrors when validation passes', async () => {
        await validateForm(nameSchema, { name: 'Alice', email: 'alice@example.com' }, setErrors)
        expect(setErrors).not.toHaveBeenCalled()
    })

    it('returns false when the schema fails validation', async () => {
        const result = await validateForm(nameSchema, { name: '', email: '' }, setErrors)
        expect(result).toBe(false)
    })

    it('calls setErrors with field-level error messages on failure', async () => {
        await validateForm(nameSchema, { name: '', email: '' }, setErrors)
        expect(setErrors).toHaveBeenCalledTimes(1)
        const errMap = setErrors.mock.calls[0][0]
        expect(errMap.name).toBeDefined()
        expect(errMap.email).toBeDefined()
    })

    it('reports the correct error message for a required field', async () => {
        await validateForm(nameSchema, { name: '', email: 'a@b.com' }, setErrors)
        const errMap = setErrors.mock.calls[0][0]
        expect(errMap.name).toBe('Name is required')
    })

    it('only records the first error per field (abortEarly:false, no duplicates)', async () => {
        await validateForm(nameSchema, { name: '', email: '' }, setErrors)
        const errMap = setErrors.mock.calls[0][0]
        // each key maps to a single string, not an array
        expect(typeof errMap.name).toBe('string')
        expect(typeof errMap.email).toBe('string')
    })

    it('reports an invalid email error', async () => {
        await validateForm(nameSchema, { name: 'Alice', email: 'not-an-email' }, setErrors)
        const errMap = setErrors.mock.calls[0][0]
        expect(errMap.email).toBe('Invalid email')
    })

    it('calls scrollToFirstError (via nextTick) when validation fails', async () => {
        const querySpy = jest.spyOn(document, 'querySelector')
        await validateForm(nameSchema, { name: '', email: '' }, setErrors)
        expect(querySpy).toHaveBeenCalledWith('.is-invalid')
        querySpy.mockRestore()
    })

    it('works with an empty schema and an empty form object (passes)', async () => {
        const emptySchema = yup.object({})
        const result = await validateForm(emptySchema, {}, setErrors)
        expect(result).toBe(true)
    })

    it('handles null form values without throwing', async () => {
        const result = await validateForm(nameSchema, { name: null, email: null }, setErrors)
        expect(result).toBe(false)
        expect(setErrors).toHaveBeenCalled()
    })
})
