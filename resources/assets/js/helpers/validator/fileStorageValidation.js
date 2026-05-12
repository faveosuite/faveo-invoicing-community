import { useAlertStore } from '@/core/stores/alert'
import { Validator } from '../easy-validator'
import { lang } from '../extraLogics'

export function validateFileStorage(form) {
    const validatingData = {}

    if (form.disk === 'system') {
        validatingData.path = [form.path ?? '', 'isRequired']
    } else {
        validatingData.s3_bucket       = [form.s3_bucket       ?? '', 'isRequired']
        validatingData.s3_region       = [form.s3_region       ?? '', 'isRequired']
        validatingData.s3_access_key   = [form.s3_access_key   ?? '', 'isRequired']
        validatingData.s3_secret_key   = [form.s3_secret_key   ?? '', 'isRequired']
        validatingData.s3_endpoint_url = [form.s3_endpoint_url ?? '', 'isRequired']
    }

    const validator = new Validator(lang)
    const { errors, isValid } = validator.validate(validatingData)
    useAlertStore().setValidationError(errors)
    return { errors, isValid }
}
