import { mount } from '@vue/test-utils'
import Checkbox from '@/components/Reusable/FormField/Checkbox.vue'

const onChangeMock = jest.fn()

const mountCheckbox = (props = {}) =>
    mount(Checkbox, {
        props: {
            name: 'accept_terms',
            onChange: onChangeMock,
            ...props,
        },
    })

describe('Checkbox.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        wrapper = mountCheckbox()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders a checkbox input', () => {
        expect(wrapper.find('input[type="checkbox"]').exists()).toBe(true)
    })

    it('renders inside form-check div', () => {
        expect(wrapper.find('.form-check').exists()).toBe(true)
    })

    it('sets input name attribute', () => {
        expect(wrapper.find('input').attributes('name')).toBe('accept_terms')
    })

    it('sets input id attribute to name', () => {
        expect(wrapper.find('input').attributes('id')).toBe('accept_terms')
    })

    it('is unchecked by default when value is false', () => {
        expect(wrapper.find('input').element.checked).toBe(false)
    })

    it('is checked when value prop is true', () => {
        wrapper = mountCheckbox({ value: true })
        expect(wrapper.find('input').element.checked).toBe(true)
    })

    it('is checked when value prop is 1', () => {
        wrapper = mountCheckbox({ value: 1 })
        expect(wrapper.find('input').element.checked).toBe(true)
    })

    it('is disabled when disabled prop is true', () => {
        wrapper = mountCheckbox({ disabled: true })
        expect(wrapper.find('input').attributes('disabled')).toBeDefined()
    })

    it('is disabled when disabled prop is 1', () => {
        wrapper = mountCheckbox({ disabled: 1 })
        expect(wrapper.find('input').attributes('disabled')).toBeDefined()
    })

    it('is not disabled by default', () => {
        expect(wrapper.find('input').attributes('disabled')).toBeUndefined()
    })

    it('does not render label when label prop is empty', () => {
        expect(wrapper.find('label').exists()).toBe(false)
    })

    it('renders label text when label prop is provided', () => {
        wrapper = mountCheckbox({ label: 'Accept Terms' })
        expect(wrapper.find('label').text()).toBe('Accept Terms')
    })

    it('label has for attribute matching name', () => {
        wrapper = mountCheckbox({ label: 'Accept' })
        expect(wrapper.find('label').attributes('for')).toBe('accept_terms')
    })

    it('applies custom classname to form-check', () => {
        wrapper = mountCheckbox({ classname: 'my-custom-class' })
        expect(wrapper.find('.form-check').classes()).toContain('my-custom-class')
    })

    it('calls onChange when checkbox is toggled', async () => {
        const input = wrapper.find('input')
        await input.setValue(true)
        expect(onChangeMock).toHaveBeenCalled()
    })

    it('calls onChange with correct value and name', async () => {
        const input = wrapper.find('input')
        await input.setValue(true)
        expect(onChangeMock).toHaveBeenCalledWith(true, 'accept_terms')
    })

    it('syncs checked state when value prop changes', async () => {
        await wrapper.setProps({ value: true })
        expect(wrapper.find('input').element.checked).toBe(true)
    })
})
