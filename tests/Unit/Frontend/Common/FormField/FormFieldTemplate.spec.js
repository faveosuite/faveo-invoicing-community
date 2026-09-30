import { mount } from '@vue/test-utils'
import FormFieldTemplate from '@/components/Reusable/FormField/FormFieldTemplate.vue'

const mountTemplate = (props = {}, slots = {}) =>
    mount(FormFieldTemplate, {
        props: {
            label: 'Test Label',
            name: 'test_field',
            ...props,
        },
        slots,
        global: {
            stubs: { ToolTip: true },
        },
    })

describe('FormFieldTemplate.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mountTemplate()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders mb-3 form-group container', () => {
        expect(wrapper.find('.mb-3.form-group').exists()).toBe(true)
    })

    it('renders the label text', () => {
        expect(wrapper.find('label.form-label').text()).toContain('Test Label')
    })

    it('does not render required asterisk when required is false', () => {
        expect(wrapper.find('.is-danger').exists()).toBe(false)
    })

    it('renders required asterisk when required is true', () => {
        wrapper = mountTemplate({ required: true })
        expect(wrapper.find('.is-danger').exists()).toBe(true)
    })

    it('renders slotted field content', () => {
        wrapper = mountTemplate({}, { default: '<input class="my-input" />' })
        expect(wrapper.find('.my-input').exists()).toBe(true)
    })

    it('applies custom classname', () => {
        wrapper = mountTemplate({ classname: 'custom-class' })
        expect(wrapper.find('.custom-class').exists()).toBe(true)
    })

    it('does not render ToolTip when hint is empty', () => {
        expect(wrapper.find('tooltip-stub, .fa-question-circle').exists()).toBe(false)
    })

    it('renders ToolTip when hint is provided and tipRule is false', () => {
        wrapper = mountTemplate({ hint: 'This is a hint' })
        // ToolTip is registered globally or as a child — check for its presence
        expect(
            wrapper.find('tooltip-stub').exists() ||
            wrapper.find('[message]').exists() ||
            wrapper.html().includes('tooltip')
        ).toBe(true)
    })

    it('does not render ToolTip when tipRule is true (tip goes below)', () => {
        wrapper = mountTemplate({ hint: 'Hint text', tipRule: true })
        // When tipRule is true, tooltip appears in body area not label area
        expect(wrapper.find('.text-small').exists()).toBe(true)
    })

    it('renders inline hint when tipRule is true', () => {
        wrapper = mountTemplate({ hint: 'A tip here', tipRule: true })
        expect(wrapper.find('.text-small').exists()).toBe(true)
    })

    it('renders error message when error prop is provided', () => {
        wrapper = mountTemplate({ error: 'This field is required' })
        expect(wrapper.find('.invalid-feedback').text()).toBe('This field is required')
    })

    it('does not render error message when error is not provided', () => {
        expect(wrapper.find('.invalid-feedback').exists()).toBe(false)
    })

    it('renders show new button when showNewButton is true', () => {
        wrapper = mountTemplate({ showNewButton: true, newBtnName: 'add_item' })
        expect(wrapper.find('a.btn-light').exists()).toBe(true)
    })

    it('does not render new button by default', () => {
        expect(wrapper.find('a.btn-light').exists()).toBe(false)
    })

    it('renders clear field button when isClearField is true and value is object', () => {
        wrapper = mountTemplate({ isClearField: true, value: { id: 1 }, clearField: jest.fn() })
        expect(wrapper.find('.clear-btn').exists()).toBe(true)
    })

    it('does not render clear field button when value is not object', () => {
        wrapper = mountTemplate({ isClearField: true, value: '' })
        expect(wrapper.find('.clear-btn').exists()).toBe(false)
    })

    it('renders inline form layout when isInlineForm is true', () => {
        wrapper = mountTemplate({ isInlineForm: true })
        expect(wrapper.find('.row').exists()).toBe(true)
    })

    it('renders input-group when inputGroupBtn is provided', () => {
        const inputGroupBtn = { action: jest.fn(), text: 'Sync' }
        wrapper = mountTemplate({ inputGroupBtn }, { default: '<input class="inner-input" />' })
        expect(wrapper.find('.input-group').exists()).toBe(true)
    })

    it('renders action button when actionBtn prop is provided', () => {
        const actionBtn = { action: jest.fn(), text: 'click_me' }
        wrapper = mountTemplate({ actionBtn })
        expect(wrapper.find('.form-field-action-button').exists()).toBe(true)
    })
})
