import { mount } from '@vue/test-utils'
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'

const onChangeMock = jest.fn()

const elements = [
    { id: 1, name: 'Active' },
    { id: 2, name: 'Inactive' },
    { id: 3, name: 'Pending' },
]

const mountStatic = (props = {}) =>
    mount(StaticSelect, {
        props: {
            label: 'Status',
            elements,
            name: 'status',
            value: '',
            onChange: onChangeMock,
            ...props,
        },
        global: {
            stubs: {
                FormFieldTemplate: {
                    template: '<div class="form-field-template-stub"><slot /></div>',
                    props: ['label', 'name', 'classname', 'hint', 'required', 'labelStyle', 'error'],
                },
            },
        },
    })

describe('StaticSelect.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        wrapper = mountStatic()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders a select element', () => {
        expect(wrapper.find('select').exists()).toBe(true)
    })

    it('renders inside FormFieldTemplate stub', () => {
        expect(wrapper.find('.form-field-template-stub').exists()).toBe(true)
    })

    it('renders default empty "Select" option', () => {
        const options = wrapper.findAll('option')
        expect(options[0].text()).toBe('Select')
    })

    it('hides empty option when hideEmptySelect is true', () => {
        wrapper = mountStatic({ hideEmptySelect: true })
        const options = wrapper.findAll('option')
        expect(options[0].text()).not.toBe('Select')
    })

    it('renders all elements as options', () => {
        const options = wrapper.findAll('option')
        // 1 empty + 3 elements = 4
        expect(options.length).toBe(4)
    })

    it('renders element names as option text', () => {
        const optionTexts = wrapper.findAll('option').map(o => o.text())
        expect(optionTexts).toContain('Active')
        expect(optionTexts).toContain('Inactive')
    })

    it('renders element ids as option values', () => {
        const options = wrapper.findAll('option').filter(o => o.attributes('value') !== '')
        expect(options[0].attributes('value')).toBe('1')
    })

    it('selects value matching value prop on mount', async () => {
        wrapper = mountStatic({ value: 1 })
        await wrapper.vm.$nextTick()
        expect(wrapper.find('select').element.value).toBe('1')
    })

    it('is disabled when disabled prop is true', () => {
        wrapper = mountStatic({ disabled: true })
        expect(wrapper.find('select').attributes('disabled')).toBeDefined()
    })

    it('calls onChange when selection changes', async () => {
        await wrapper.find('select').setValue(1)
        expect(onChangeMock).toHaveBeenCalled()
    })

    it('calls onChange with selected value and name', async () => {
        await wrapper.find('select').setValue(2)
        // v-model on a number option returns numeric value
        expect(onChangeMock).toHaveBeenCalledWith(expect.anything(), 'status')
    })

    it('applies is-invalid class when error is provided', () => {
        wrapper = mountStatic({ error: 'Required' })
        expect(wrapper.find('select').classes()).toContain('is-invalid')
    })

    it('syncs value when value prop changes', async () => {
        await wrapper.setProps({ value: 3 })
        expect(wrapper.vm.selectedValue).toBe(3)
    })

    it('truncates long option names when strlength is set', () => {
        const longElements = [{ id: 1, name: 'A'.repeat(200) }]
        wrapper = mountStatic({ elements: longElements, strlength: 10 })
        const option = wrapper.findAll('option')[1]
        expect(option.text().length).toBeLessThanOrEqual(13) // 10 + "..."
    })
})
