import { mount } from '@vue/test-utils'
import SelectField from '@/components/Reusable/FormField/SelectField.vue'

const onChangeMock = jest.fn()

const elements = [
    { id: 1, name: 'Option A' },
    { id: 2, name: 'Option B' },
    { id: 3, name: 'Option C' },
]

const mountSelect = (props = {}) =>
    mount(SelectField, {
        props: {
            name: 'category',
            label: 'Category',
            elements,
            value: '',
            onChange: onChangeMock,
            ...props,
        },
        global: {
            stubs: {
                ToolTip: { template: '<span class="tooltip-stub" :message="message"></span>', props: ['message', 'size'] },
                Tooltip: { template: '<span class="tooltip-stub" :message="message"></span>', props: ['message', 'size'] },
            },
        },
    })

describe('SelectField.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        wrapper = mountSelect()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders mb-3 container', () => {
        expect(wrapper.find('.mb-3').exists()).toBe(true)
    })

    it('renders label when label is provided', () => {
        expect(wrapper.find('label').text()).toContain('Category')
    })

    it('does not render label when label is empty', () => {
        wrapper = mountSelect({ label: '' })
        expect(wrapper.find('label').exists()).toBe(false)
    })

    it('renders required asterisk when required is true', () => {
        wrapper = mountSelect({ required: true })
        expect(wrapper.find('.text-danger').text()).toBe('*')
    })

    it('renders v-select element', () => {
        // v-select renders via vue-select — check the HTML contains it
        expect(wrapper.html()).toContain('v-select')
    })

    it('does not show error by default', () => {
        expect(wrapper.find('.invalid-feedback').exists()).toBe(false)
    })

    it('shows error message when error prop is provided', () => {
        wrapper = mountSelect({ error: 'Please select a category' })
        expect(wrapper.find('.invalid-feedback').text()).toBe('Please select a category')
    })

    it('defaults multiple to false', () => {
        expect(wrapper.props('multiple')).toBe(false)
    })

    it('defaults clearable to true', () => {
        expect(wrapper.props('clearable')).toBe(true)
    })

    it('defaults searchable to false', () => {
        expect(wrapper.props('searchable')).toBe(false)
    })

    it('defaults disabled to false', () => {
        expect(wrapper.props('disabled')).toBe(false)
    })

    it('defaults placeholder to Select', () => {
        expect(wrapper.props('placeholder')).toBe('Select')
    })

    it('defaults optionLabel to name', () => {
        expect(wrapper.props('optionLabel')).toBe('name')
    })

    it('renders tooltip when tooltip prop is provided', () => {
        wrapper = mountSelect({ tooltip: 'Choose category' })
        expect(wrapper.find('.tooltip-stub').exists()).toBe(true)
    })

    it('accepts taggable prop', () => {
        wrapper = mountSelect({ taggable: true })
        expect(wrapper.props('taggable')).toBe(true)
    })

    it('syncs selectedValue when value prop changes', async () => {
        await wrapper.setProps({ value: { id: 2, name: 'Option B' } })
        expect(wrapper.vm.selectedValue).toEqual({ id: 2, name: 'Option B' })
    })
})
