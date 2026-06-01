import * as yup from 'yup'

// Generic tax rate form: a named percentage rate scoped to a location and
// tax class. No GST/CGST/SGST special-casing.
function rateShape() {
    return {
        name: yup.string().required(() => __('validation.tax_form.name.required')),
        rate: yup
            .number()
            .typeError(() => __('validation.tax_form.rate.required'))
            .min(0)
            .required(() => __('validation.tax_form.rate.required')),
    }
}

export function buildTaxCreateSchema() {
    return yup.object(rateShape())
}

export function buildTaxEditSchema() {
    return yup.object(rateShape())
}
