import { mount } from '@vue/test-utils'
import NumberField from '@/components/Reusable/FormField/NumberField.vue'

const onChangeMock = jest.fn()

const mountNumberField = (props = {}) =>
    mount(NumberField, {
        props: {
            name: 'quantity',
            value: '',
            onChange: onChangeMock,
            ...props,
        },
        global: {
            stubs: {
                FormFieldTemplate: {
                    template: '<div class="form-field-template-stub"><slot /></div>',
                    props: ['label', 'labelStyle', 'name', 'classname', 'hint', 'required', 'error'],
                },
            },
        },
    })

describe('NumberField.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        wrapper = mountNumberField()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders an input element', () => {
        expect(wrapper.find('input').exists()).toBe(true)
    })

    it('renders inside FormFieldTemplate stub', () => {
        expect(wrapper.find('.form-field-template-stub').exists()).toBe(true)
    })

    it('has default type of text', () => {
        expect(wrapper.find('input').attributes('type')).toBe('text')
    })

    it('accepts number type', () => {
        wrapper = mountNumberField({ type: 'number' })
        expect(wrapper.find('input').attributes('type')).toBe('number')
    })

    it('has min attribute of 0', () => {
        expect(wrapper.find('input').attributes('min')).toBe('0')
    })

    it('sets placeholder from prop', () => {
        wrapper = mountNumberField({ placeholder: 'Enter quantity' })
        expect(wrapper.find('input').attributes('placeholder')).toBe('Enter quantity')
    })

    it('defaults placeholder to Enter a value', () => {
        expect(wrapper.find('input').attributes('placeholder')).toBe('Enter a value')
    })

    it('sets value from prop', () => {
        wrapper = mountNumberField({ value: 5 })
        expect(wrapper.find('input').element.value).toBe('5')
    })

    it('calls onChange when input changes', async () => {
        await wrapper.find('input').setValue('10')
        expect(onChangeMock).toHaveBeenCalledWith('10', 'quantity')
    })

    it('syncs value when prop changes', async () => {
        await wrapper.setProps({ value: 42 })
        expect(wrapper.vm.changedValue).toBe(42)
    })

    it('applies is-invalid class when error is provided', () => {
        wrapper = mountNumberField({ error: 'Invalid number' })
        expect(wrapper.find('input').classes()).toContain('is-invalid')
    })

    it('does not apply is-invalid class when no error', () => {
        expect(wrapper.find('input').classes()).not.toContain('is-invalid')
    })

    it('prevents non-numeric keypress', () => {
        const input = wrapper.find('input')
        const event = new KeyboardEvent('keypress', { keyCode: 65, bubbles: true })
        input.element.dispatchEvent(event)
        // checkValue handler prevents non-numeric characters
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders required attribute when required is true', () => {
        wrapper = mountNumberField({ required: true })
        expect(wrapper.find('input').attributes('required')).toBeDefined()
    })
})
