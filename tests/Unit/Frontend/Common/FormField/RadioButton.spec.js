import { mount } from '@vue/test-utils'
import RadioButton from '@/components/Reusable/FormField/RadioButton.vue'

const onChangeMock = jest.fn()

const options = [
    { name: 'option_yes', value: 1 },
    { name: 'option_no', value: 0 },
]

const mountRadio = (props = {}) =>
    mount(RadioButton, {
        props: {
            options,
            onChange: onChangeMock,
            ...props,
        },
        global: {
            stubs: { ToolTip: true },
        },
    })

describe('RadioButton.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        wrapper = mountRadio()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders radio inputs', () => {
        const radios = wrapper.findAll('input[type="radio"]')
        expect(radios.length).toBe(2)
    })

    it('renders labels for each option', () => {
        expect(wrapper.findAll('.form-check-label').length).toBe(2)
    })

    it('sets name attribute on all radio inputs', () => {
        wrapper.findAll('input[type="radio"]').forEach(input => {
            expect(input.attributes('name')).toBe('radio')
        })
    })

    it('uses custom name prop', () => {
        wrapper = mountRadio({ name: 'status' })
        wrapper.findAll('input[type="radio"]').forEach(input => {
            expect(input.attributes('name')).toBe('status')
        })
    })

    it('defaults value to 0', () => {
        expect(wrapper.vm.checked).toBe(0)
    })

    it('sets checked value from value prop', () => {
        wrapper = mountRadio({ value: 1 })
        expect(wrapper.vm.checked).toBe(1)
    })

    it('calls onChange when radio selection changes', async () => {
        const radio = wrapper.findAll('input[type="radio"]')[0]
        await radio.setValue(1)
        expect(onChangeMock).toHaveBeenCalled()
    })

    it('renders label text', () => {
        expect(wrapper.find('label.form-label').text()).toContain('label')
    })

    it('uses custom label prop', () => {
        wrapper = mountRadio({ label: 'Status' })
        expect(wrapper.find('label.form-label').text()).toContain('Status')
    })

    it('disables all radios when disabled is true', () => {
        wrapper = mountRadio({ disabled: true })
        wrapper.findAll('input[type="radio"]').forEach(input => {
            expect(input.attributes('disabled')).toBeDefined()
        })
    })

    it('does not disable radios by default', () => {
        wrapper.findAll('input[type="radio"]').forEach(input => {
            expect(input.attributes('disabled')).toBeUndefined()
        })
    })

    it('applies custom classname', () => {
        wrapper = mountRadio({ classname: 'my-radio-group' })
        expect(wrapper.find('.my-radio-group').exists()).toBe(true)
    })

    it('defaults classname to mb-3', () => {
        expect(wrapper.find('.mb-3').exists()).toBe(true)
    })

    it('renders ToolTip when hint is provided', () => {
        wrapper = mountRadio({ hint: 'Pick one option' })
        const html = wrapper.html().toLowerCase()
        expect(
            wrapper.find('tooltip-stub').exists() ||
            wrapper.find('tool-tip-stub').exists() ||
            html.includes('tooltip') ||
            html.includes('tool-tip')
        ).toBe(true)
    })

    it('does not render ToolTip when hint is empty', () => {
        expect(wrapper.find('tooltip-stub').exists()).toBe(false)
    })

    it('syncs checked value when value prop changes', async () => {
        await wrapper.setProps({ value: 1 })
        expect(wrapper.vm.checked).toBe(1)
    })

    it('renders option with hint as tooltip', () => {
        const optionsWithHint = [
            { name: 'option_a', value: 1, hint: 'Hint for A' },
        ]
        wrapper = mountRadio({ options: optionsWithHint })
        const html = wrapper.html().toLowerCase()
        expect(
            wrapper.find('tooltip-stub').exists() ||
            wrapper.find('tool-tip-stub').exists() ||
            html.includes('tooltip') ||
            html.includes('tool-tip')
        ).toBe(true)
    })
})
