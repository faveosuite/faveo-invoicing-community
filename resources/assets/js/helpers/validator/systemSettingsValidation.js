import { useAlertStore } from '@/core/stores/alert'
import { Validator } from '../easy-validator'
import { lang } from '../extraLogics'

export function validateSystemSettings(form) {
    const validatingData = {
        company:          [form.company          ?? '', 'isRequired', { 'max(50)': lang('max_length_exceeded') }],
        company_email:    [form.company_email    ?? '', 'isRequired', 'isEmail'],
        website:          [form.website          ?? '', 'isRequired', 'isUrl'],
        phone:            [form.phone            ?? '', 'isRequired'],
        address:          [form.address          ?? '', 'isRequired'],
        country:          [form.country?.id      ?? '', 'isRequired'],
        state:            [form.state?.id        ?? '', 'isRequired'],
        default_currency: [form.default_currency?.id ?? '', 'isRequired'],
    }

    const validator = new Validator(lang)
    const { errors, isValid } = validator.validate(validatingData)

    useAlertStore().setValidationError(errors)

    return { errors, isValid }
}
