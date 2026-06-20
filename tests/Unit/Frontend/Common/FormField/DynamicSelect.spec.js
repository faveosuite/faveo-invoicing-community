import { mount } from '@vue/test-utils'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'

// jsdom does not implement IntersectionObserver
global.IntersectionObserver = class {
    constructor() {}
    observe() {}
    disconnect() {}
    unobserve() {}
}

const onChangeMock = jest.fn()

const vSelectStub = {
    template: '<div class="v-select-stub"><slot name="option" /><slot name="list-footer" /><slot name="spinner" /><slot name="no-options" :search="\'\'"/></div>',
    props: ['options', 'modelValue', 'label', 'multiple', 'placeholder', 'disabled', 'clearable', 'searchable', 'filterable', 'closeOnSelect', 'taggable', 'pushTags', 'noDrop', 'loading', 'dropdownShouldOpen', 'inputId'],
    emits: ['update:modelValue', 'search', 'search:blur', 'open', 'close'],
}

const mountDynamic = (props = {}) =>
    mount(DynamicSelect, {
        props: {
            name: 'user_id',
            onChange: onChangeMock,
            ...props,
        },
        global: {
            stubs: {
                'v-select': vSelectStub,
                VSelect: vSelectStub,
                loader: true,
            },
        },
    })

describe('DynamicSelect.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        wrapper = mountDynamic()
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
        wrapper = mountDynamic({ label: 'Select User' })
        expect(wrapper.find('label').text()).toContain('Select User')
    })

    it('renders required asterisk when required is true', () => {
        wrapper = mountDynamic({ label: 'User', required: true })
        expect(wrapper.find('.text-danger').text()).toBe('*')
    })

    it('renders v-select element', () => {
        // v-select is registered globally from vue-select
        expect(wrapper.html()).toContain('v-select')
    })

    it('does not show error when error is not provided', () => {
        expect(wrapper.find('.invalid-feedback').exists()).toBe(false)
    })

    it('shows error message when error prop is provided', () => {
        wrapper = mountDynamic({ error: 'This field is required' })
        expect(wrapper.find('.invalid-feedback').text()).toBe('This field is required')
    })

    it('initializes listElements from elements prop', () => {
        const elements = [{ id: 1, name: 'Alice' }, { id: 2, name: 'Bob' }]
        wrapper = mountDynamic({ elements })
        expect(wrapper.vm.listElements).toEqual(elements)
    })

    it('initializes selectedValue from value prop', () => {
        const value = { id: 1, name: 'Alice' }
        wrapper = mountDynamic({ value })
        expect(wrapper.vm.selectedValue).toEqual(value)
    })

    it('updates selectedValue when value prop changes', async () => {
        const newVal = { id: 2, name: 'Bob' }
        await wrapper.setProps({ value: newVal })
        expect(wrapper.vm.selectedValue).toEqual(newVal)
    })

    it('loads from apiEndpoint when endpoint changes', async () => {
        global.mockHttp.onGet('/api/users').reply(200, {
            data: { data: [{ id: 1, name: 'Alice' }], next_page_url: null },
        })
        wrapper = mountDynamic()
        await wrapper.setProps({ apiEndpoint: '/api/users' })
        await flushPromises()
        expect(wrapper.vm.listElements.length).toBeGreaterThan(0)
    })

    it('defaults multiple to false', () => {
        expect(wrapper.props('multiple')).toBe(false)
    })

    it('defaults clearable to true', () => {
        expect(wrapper.props('clearable')).toBe(true)
    })

    it('defaults searchable to true', () => {
        expect(wrapper.props('searchable')).toBe(true)
    })

    it('defaults disabled to false', () => {
        expect(wrapper.props('disabled')).toBe(false)
    })

    it('defaults optionLabel to name', () => {
        expect(wrapper.props('optionLabel')).toBe('name')
    })

    it('defaults placeholder to Search or Select', () => {
        expect(wrapper.props('placeholder')).toBe('Search or Select')
    })
})
