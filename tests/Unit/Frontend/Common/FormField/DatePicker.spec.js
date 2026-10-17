import { mount } from '@vue/test-utils'
import DatePicker from '@/components/Reusable/FormField/DatePicker.vue'

const onChangeMock = jest.fn()

const DatePickerStub = {
    template: '<input class="vue-datepicker-stub" :value="value" @change="$emit(\'change\', $event.target.value)" />',
    props: ['value', 'type', 'format', 'placeholder', 'disabled', 'clearable', 'range', 'editable', 'confirm', 'inputClass', 'inputAttr'],
    emits: ['change', 'confirm', 'update:value'],
}

const mountDatePicker = (props = {}) =>
    mount(DatePicker, {
        props: {
            name: 'start_date',
            onChange: onChangeMock,
            ...props,
        },
        global: {
            stubs: {
                VueDatePicker: DatePickerStub,
                'vue-datepicker-next': DatePickerStub,
                VueDatepickerNext: DatePickerStub,
            },
        },
    })

describe('DatePicker.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        wrapper = mountDatePicker()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the mb-3 container', () => {
        expect(wrapper.find('.mb-3').exists()).toBe(true)
    })

    it('does not render label when label is empty', () => {
        expect(wrapper.find('label').exists()).toBe(false)
    })

    it('renders label when label prop is provided', () => {
        wrapper = mountDatePicker({ label: 'Start Date' })
        expect(wrapper.find('label').text()).toContain('Start Date')
    })

    it('renders required asterisk when required is true', () => {
        wrapper = mountDatePicker({ label: 'Date', required: true })
        expect(wrapper.find('.text-danger').text()).toBe('*')
    })

    it('does not render required asterisk when required is false', () => {
        wrapper = mountDatePicker({ label: 'Date', required: false })
        expect(wrapper.find('.text-danger').exists()).toBe(false)
    })

    it('renders a date picker element', () => {
        // VueDatePicker may render as stub or as real component
        expect(
            wrapper.find('.vue-datepicker-stub').exists() ||
            wrapper.find('.mx-datepicker').exists() ||
            wrapper.html().includes('datepicker') ||
            wrapper.html().includes('mx-input')
        ).toBe(true)
    })

    it('does not show error message when error is not provided', () => {
        expect(wrapper.find('.invalid-feedback').exists()).toBe(false)
    })

    it('shows error message when error prop is provided', () => {
        wrapper = mountDatePicker({ error: 'Date is required' })
        expect(wrapper.find('.invalid-feedback').text()).toBe('Date is required')
    })

    it('handles onChange by calling the onChange prop', () => {
        // onDateChange wraps the prop, verify the function exists
        expect(typeof wrapper.vm.onDateChange).toBe('function')
        wrapper.vm.onDateChange('2024-01-15')
        expect(onChangeMock).toHaveBeenCalledWith('2024-01-15', 'start_date')
    })

    it('defaults type to date', () => {
        expect(wrapper.props('type')).toBe('date')
    })

    it('defaults format to YYYY-MM-DD', () => {
        expect(wrapper.props('format')).toBe('YYYY-MM-DD')
    })

    it('defaults disabled to false', () => {
        expect(wrapper.props('disabled')).toBe(false)
    })

    it('defaults clearable to true', () => {
        expect(wrapper.props('clearable')).toBe(true)
    })

    it('defaults range to false', () => {
        expect(wrapper.props('range')).toBe(false)
    })

    it('accepts range prop as true', () => {
        wrapper = mountDatePicker({ range: true })
        expect(wrapper.props('range')).toBe(true)
    })

    it('updates internal value when value prop changes', async () => {
        await wrapper.setProps({ value: '2024-06-15' })
        expect(wrapper.vm.selectedValue).toBe('2024-06-15')
    })
})
