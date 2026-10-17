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
            .max(999.999, () => __('validation.tax_form.rate.max'))
            .test(
                'max-3-decimals',
                () => __('validation.tax_form.rate.decimal'),
                (val) => val === undefined || val === null || /^\d+(\.\d{1,3})?$/.test(String(val)),
            )
            .required(() => __('validation.tax_form.rate.required')),
        priority: yup
            .number()
            .typeError(() => __('validation.tax_form.priority.required'))
            .min(1, () => __('validation.tax_form.priority.min'))
            .required(() => __('validation.tax_form.priority.required')),
    }
}

export function buildTaxCreateSchema() {
    return yup.object(rateShape())
}

export function buildTaxEditSchema() {
    return yup.object(rateShape())
}
