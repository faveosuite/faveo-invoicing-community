import { nextTick } from 'vue'

export function extractId(val) {
    if (val === null || val === undefined) return null
    return typeof val === 'object' ? val.id : val
}

export async function scrollToFirstError() {
    await nextTick()
    const el = document.querySelector('.is-invalid')
    if (!el) return
    const reduce = globalThis.matchMedia?.('(prefers-reduced-motion: reduce)').matches
    el.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' })
    const target = el.matches('input, select, textarea, button, [tabindex]')
        ? el
        : el.querySelector('input, select, textarea, button, [tabindex]')
    target?.focus({ preventScroll: true })
}

/**
 * Validates form data against a yup schema.
 * On failure: sets vee-validate errors and scrolls to the first invalid field.
 * On success: returns true so the caller can proceed with submission.
 *
 * Usage:
 *   if (!await validateForm(schema, form, setErrors)) return
 */
export async function validateForm(schema, form, setErrors) {
    try {
        schema.validateSync(form, { abortEarly: false })
        // vee-validate's setErrors only clears keys present in the object passed in,
        // so `setErrors({})` is a no-op — explicitly null out every schema field to
        // clear stale errors left over from a previous failed attempt.
        const clear = {}
        Object.keys(schema.fields || {}).forEach(key => { clear[key] = undefined })
        setErrors(clear)
        return true
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        await scrollToFirstError()
        return false
    }
}

// type="number" still lets you type +, -, e/E (scientific notation) and a decimal point.
// Use on @keydown for fields the backend wants as plain non-negative numbers.
export function blockInvalidKey(e, allowDecimal = false) {
    const blocked = allowDecimal ? ['e', 'E', '+', '-'] : ['e', 'E', '+', '-', '.']
    if (blocked.includes(e.key)) e.preventDefault()
}
