import { nextTick } from 'vue'

export function extractId(val) {
    if (val === null || val === undefined) return null
    return typeof val === 'object' ? val.id : val
}

export async function scrollToFirstError() {
    await nextTick()
    document.querySelector('.is-invalid')
        ?.scrollIntoView({ behavior: 'smooth', block: 'center' })
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
        return true
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        await scrollToFirstError()
        return false
    }
}
