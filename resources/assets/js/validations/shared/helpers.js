import * as yup from 'yup'

// Handles: null | undefined | '' | 'plain-id' | { id, name, ... }
export const reqSelect = (msg) =>
    yup.mixed().nullable().test('required-select', msg, (val) => {
        if (val === null || val === undefined || val === '') return false
        if (typeof val === 'object') return val.id != null && String(val.id).trim() !== ''
        return String(val).trim() !== ''
    })
