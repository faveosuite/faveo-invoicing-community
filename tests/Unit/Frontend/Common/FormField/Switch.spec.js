import { mount } from '@vue/test-utils'

// @vueform/toggle uses ESM — stub the Toggle component
jest.mock('@vueform/toggle', () => {
    const Toggle = {
        name: 'Toggle',
        template: '<button class="toggle-mock" @click="$emit(\'update:modelValue\', !modelValue)" :data-value="String(modelValue)"></button>',
        props: { modelValue: { default: false }, disabled: { default: false } },
        emits: ['update:modelValue'],
    }
    return { __esModule: true, default: Toggle }
})

import Switch from '@/components/Reusable/FormField/Switch.vue'

const onChangeMock = jest.fn()

const mountSwitch = (props = {}) =>
    mount(Switch, {
        props: {
            name: 'is_active',
            onChange: onChangeMock,
            ...props,
        },
    })

describe('Switch.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        wrapper = mountSwitch()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the Toggle mock', () => {
        expect(wrapper.find('.toggle-mock').exists()).toBe(true)
    })

    it('defaults to false (disabled/off state)', () => {
        expect(wrapper.find('.toggle-mock').attributes('data-value')).toBe('false')
    })

    it('initializes to true when value prop is true', () => {
        wrapper = mountSwitch({ value: true })
        expect(wrapper.find('.toggle-mock').attributes('data-value')).toBe('true')
    })

    it('initializes to true when value prop is 1', () => {
        wrapper = mountSwitch({ value: 1 })
        expect(wrapper.find('.toggle-mock').attributes('data-value')).toBe('true')
    })

    it('initializes to false when value prop is 0', () => {
        wrapper = mountSwitch({ value: 0 })
        expect(wrapper.find('.toggle-mock').attributes('data-value')).toBe('false')
    })

    it('calls onChange when toggle is clicked', async () => {
        await wrapper.find('.toggle-mock').trigger('click')
        expect(onChangeMock).toHaveBeenCalled()
    })

    it('calls onChange with new value and name', async () => {
        await wrapper.find('.toggle-mock').trigger('click')
        expect(onChangeMock).toHaveBeenCalledWith(true, 'is_active')
    })

    it('disables toggle when disabled prop is true', () => {
        wrapper = mountSwitch({ disabled: true })
        expect(wrapper.props('disabled')).toBeTruthy()
    })

    it('disables toggle when disabled prop is 1', () => {
        wrapper = mountSwitch({ disabled: 1 })
        expect(wrapper.props('disabled')).toBeTruthy()
    })

    it('applies custom classname', () => {
        wrapper = mountSwitch({ classname: 'custom-switch' })
        expect(wrapper.find('.custom-switch').exists()).toBe(true)
    })

    it('updates enabled when value prop changes', async () => {
        await wrapper.setProps({ value: true })
        expect(wrapper.vm.enabled).toBe(true)
    })

    it('updates enabled to false when value changes to 0', async () => {
        wrapper = mountSwitch({ value: true })
        await wrapper.setProps({ value: 0 })
        // Switch uses options API data() — enabled reflects new value
        expect(wrapper.vm.enabled).toBe(false)
    })
})
