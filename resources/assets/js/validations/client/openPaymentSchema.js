import * as yup from 'yup'

export const openPaymentSchema = yup.object({
    name:    yup.string().required('Please enter your name.').max(100, 'Name cannot exceed 100 characters.'),
    email:   yup.string().required('Please enter your email address.').email('Please enter a valid email address.'),
    mobile:  yup.string().required('Please enter your mobile number.').min(8, 'Mobile number must be at least 8 characters.'),
    company: yup.string().required('Please enter your company name.'),
    address: yup.string().required('Please enter your address.'),
    city:    yup.string().required('Please enter your city.'),
    state:   yup.string().required('Please select your state.'),
    zip:     yup.string().required('Please enter your ZIP / postal code.').max(15, 'ZIP code cannot exceed 15 characters.'),
    country: yup.string().required('Please select your country.'),
    amount:  yup.number()
        .typeError('Amount must be a valid number.')
        .required('Please enter the payment amount.')
        .min(1, 'Amount must be at least 1.'),
})
