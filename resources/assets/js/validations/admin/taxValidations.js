import * as yup from 'yup'

export function buildTaxCreateSchema(form) {
    const shape = {
        name:       yup.string().required(() => __('validation.tax_form.name.required')),
        'tax-name': yup.string().required(() => __('validation.tax_form.name.required')),
    }
    if (form.name === 'Others') {
        shape.rate = yup.string().required(() => __('validation.tax_form.rate.required'))
    }
    return yup.object(shape)
}

export function buildTaxEditSchema(form) {
    const shape = {
        name:           yup.string().required(() => __('validation.tax_form.name.required')),
        tax_classes_id: yup.string().required(() => __('validation.tax_form.name.required')),
    }
    if (form.tax_classes_id === 'Others') {
        shape.rate = yup.string().required(() => __('validation.tax_form.rate.required'))
    }
    return yup.object(shape)
}
