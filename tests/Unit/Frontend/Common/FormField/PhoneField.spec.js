import { mount } from '@vue/test-utils'

// The component imports intl-tel-input/intlTelInputWithUtils — stub it out
jest.mock('intl-tel-input/intlTelInputWithUtils', () => {
    const iti = {
        destroy: jest.fn(),
        getNumber: jest.fn(() => ''),
        getSelectedCountry: jest.fn(() => ({ dialCode: '1', iso2: 'us' })),
        isValidNumber: jest.fn(() => true),
        setNumber: jest.fn(),
        setCountry: jest.fn(),
    }
    return { __esModule: true, default: jest.fn(() => iti) }
})

import PhoneField from '@/components/Reusable/FormField/PhoneField.vue'

const onChangeMock = jest.fn()

const mountPhone = (props = {}) =>
    mount(PhoneField, {
        props: {
            name: 'phone',
            onChange: onChangeMock,
            ...props,
        },
    })

describe('PhoneField.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        wrapper = mountPhone()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the mb-3 container', () => {
        expect(wrapper.find('.mb-3').exists()).toBe(true)
    })

    it('renders a tel input', () => {
        expect(wrapper.find('input[type="tel"]').exists()).toBe(true)
    })

    it('does not render label when label is empty', () => {
        expect(wrapper.find('label').exists()).toBe(false)
    })

    it('renders label when label prop is provided', () => {
        wrapper = mountPhone({ label: 'Phone Number' })
        expect(wrapper.find('label').text()).toContain('Phone Number')
    })

    it('renders required asterisk when required is true', () => {
        wrapper = mountPhone({ label: 'Phone', required: true })
        expect(wrapper.find('.text-danger').text()).toBe('*')
    })

    it('does not render required asterisk when required is false', () => {
        wrapper = mountPhone({ label: 'Phone', required: false })
        expect(wrapper.find('.text-danger').exists()).toBe(false)
    })

    it('does not show error message by default', () => {
        expect(wrapper.find('.invalid-feedback').exists()).toBe(false)
    })

    it('shows error message when error prop is provided', () => {
        wrapper = mountPhone({ error: 'Invalid phone number' })
        expect(wrapper.find('.invalid-feedback').text()).toBe('Invalid phone number')
    })

    it('applies is-invalid class to input when error is provided', () => {
        wrapper = mountPhone({ error: 'Required' })
        expect(wrapper.find('input').classes()).toContain('is-invalid')
    })

    it('calls onChange when input fires', async () => {
        const input = wrapper.find('input')
        await input.setValue('1234567890')
        await input.trigger('input')
        expect(onChangeMock).toHaveBeenCalled()
    })

    it('calls onChange with correct value and name', async () => {
        const input = wrapper.find('input')
        input.element.value = '9876543210'
        await input.trigger('input')
        expect(onChangeMock).toHaveBeenCalledWith('9876543210', 'phone')
    })

    it('emits countryChange event on mount when ITI is initialized', () => {
        // intl-tel-input mock calls emitCountry on mount — event is emitted
        expect(wrapper.emitted('countryChange')).toBeTruthy()
    })

    it('defaults value to empty string', () => {
        expect(wrapper.props('value')).toBe('')
    })

    it('accepts numeric value prop', () => {
        wrapper = mountPhone({ value: 1234567890 })
        expect(wrapper.props('value')).toBe(1234567890)
    })

    it('defaults initialCountry to auto', () => {
        expect(wrapper.props('initialCountry')).toBe('auto')
    })
})
