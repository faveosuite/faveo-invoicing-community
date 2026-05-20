import { useAlertStore } from '@/core/stores/alert'
import { Validator } from '@/helpers/easy-validator/index'
import { message as defaultMessages } from '@/helpers/easy-validator/messages'

function resolveMessage(key) {
    if (key === 'this_field_is_required') {
        const t = typeof __ === 'function' ? __('message.field_required') : ''
        return (t && t !== 'message.field_required') ? t : 'This field is required'
    }
    return defaultMessages[key]
}

export function useFormValidation() {
    const alertStore = useAlertStore()
    const validator  = new Validator(resolveMessage)

    /**
     * Validate form fields using easy-validator rules.
     * Clears existing errors first, then sets new ones on failure.
     *
     * @param {Object} rules  { fieldName: [value, 'rule1', 'rule2', ...], ... }
     * @returns {boolean}     true if all fields are valid
     */
    function validate(rules) {
        alertStore.unsetValidationError()

        const normalized = {}
        Object.entries(rules).forEach(([field, fieldRules]) => {
            const raw = fieldRules[0]
            // Normalize for isRequired: null/undefined → '', object with id (selected option) → its id string, plain value → string
            let value
            if (raw === null || raw === undefined) {
                value = ''
            } else if (typeof raw === 'object' && !Array.isArray(raw)) {
                value = raw.id != null ? String(raw.id) : ''
            } else {
                value = String(raw)
            }
            normalized[field] = [value, ...fieldRules.slice(1)]
        })

        const { errors, isValid } = validator.validate(normalized)
        if (!isValid) alertStore.setValidationError(errors)
        return isValid
    }

    /**
     * Remove the error for a single field (call in onChange handlers).
     */
    function clearFieldError(name) {
        const errs = alertStore.validation_errors
        if (name in errs) {
            const updated = { ...errs }
            delete updated[name]
            alertStore.setValidationError(updated)
        }
    }

    /**
     * Clear all validation errors (call in onMounted to prevent stale errors).
     */
    function clearAllErrors() {
        alertStore.unsetValidationError()
    }

    return { validate, clearFieldError, clearAllErrors }
}
