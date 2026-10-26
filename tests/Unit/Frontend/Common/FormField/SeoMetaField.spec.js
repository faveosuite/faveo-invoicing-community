import { mount } from '@vue/test-utils'
import SeoMetaField from '@/components/Reusable/FormField/SeoMetaField.vue'

const onChangeMock = jest.fn()

const shortcodes = [
    { code: '{name}', label: 'Title', description: "Adds this page's own name here." },
    { code: '{company}', label: 'Company', description: 'Adds your company name here.' },
]

const mountField = (props = {}) =>
    mount(SeoMetaField, {
        props: {
            name: 'meta_title',
            label: 'Meta Title',
            maxLength: 60,
            onChange: onChangeMock,
            ...props,
        },
        global: {
            stubs: { ToolTip: true },
        },
    })

describe('SeoMetaField.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        wrapper = mountField()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the label', () => {
        expect(wrapper.find('label').text()).toContain('Meta Title')
    })

    it('renders a text input by default', () => {
        expect(wrapper.find('input[type="text"]').exists()).toBe(true)
        expect(wrapper.find('textarea').exists()).toBe(false)
    })

    it('renders a textarea when type is textarea', () => {
        wrapper = mountField({ type: 'textarea' })
        expect(wrapper.find('textarea').exists()).toBe(true)
        expect(wrapper.find('input[type="text"]').exists()).toBe(false)
    })

    it('sets the field id from the name prop', () => {
        expect(wrapper.find('input').attributes('id')).toBe('seo-field-meta_title')
    })

    it('binds the value prop', () => {
        wrapper = mountField({ value: 'Hello world' })
        expect(wrapper.find('input').element.value).toBe('Hello world')
    })

    it('calls onChange with the new value and field name on input', async () => {
        await wrapper.find('input').setValue('New title')
        expect(onChangeMock).toHaveBeenCalledWith('New title', 'meta_title')
    })

    it('shows the current/max character count', () => {
        wrapper = mountField({ value: 'Hello' })
        expect(wrapper.text()).toContain('5')
        expect(wrapper.text()).toContain('60')
    })

    it('marks the counter as over-limit when value exceeds maxLength', () => {
        wrapper = mountField({ value: 'a'.repeat(70), maxLength: 60 })
        const counter = wrapper.findAll('small').at(1)
        expect(counter.classes()).toContain('text-danger')
    })

    it('does not mark the counter as over-limit within maxLength', () => {
        wrapper = mountField({ value: 'short' })
        const counter = wrapper.findAll('small').at(1)
        expect(counter.classes()).not.toContain('text-danger')
    })

    it('renders no shortcode buttons by default', () => {
        expect(wrapper.findAll('.shortcode-btn').length).toBe(0)
    })

    it('renders a button per shortcode', () => {
        wrapper = mountField({ shortcodes })
        expect(wrapper.findAll('.shortcode-btn').length).toBe(2)
    })

    it('renders the shortcode label text', () => {
        wrapper = mountField({ shortcodes })
        const buttons = wrapper.findAll('.shortcode-btn')
        expect(buttons[0].text()).toContain('Title')
        expect(buttons[1].text()).toContain('Company')
    })

    it('uses btn-light styling (not btn-secondary) for shortcode buttons', () => {
        wrapper = mountField({ shortcodes })
        const btn = wrapper.findAll('.shortcode-btn')[0]
        expect(btn.classes()).toContain('btn-light')
        expect(btn.classes()).not.toContain('btn-secondary')
    })

    it('uses a plain plus icon (not a circular plus) for shortcode buttons', () => {
        wrapper = mountField({ shortcodes })
        const icon = wrapper.findAll('.shortcode-btn')[0].find('i')
        expect(icon.classes()).toContain('fa-plus')
        expect(icon.classes()).not.toContain('fa-plus-circle')
    })

    it('inserts the shortcode into an empty field', async () => {
        wrapper = mountField({ shortcodes, value: '' })
        const input = wrapper.find('input').element
        input.setSelectionRange(0, 0)
        await wrapper.findAll('.shortcode-btn')[0].trigger('click')
        expect(onChangeMock).toHaveBeenCalledWith('{name}', 'meta_title')
    })

    it('inserts the shortcode at the current cursor position', async () => {
        wrapper = mountField({ shortcodes, value: 'Hello World' })
        const input = wrapper.find('input').element
        input.setSelectionRange(5, 5) // right after "Hello"
        await wrapper.findAll('.shortcode-btn')[0].trigger('click')
        expect(onChangeMock).toHaveBeenCalledWith('Hello{name} World', 'meta_title')
    })

    it('replaces a text selection with the shortcode', async () => {
        wrapper = mountField({ shortcodes, value: 'Hello World' })
        const input = wrapper.find('input').element
        input.setSelectionRange(0, 5) // "Hello" selected
        await wrapper.findAll('.shortcode-btn')[0].trigger('click')
        expect(onChangeMock).toHaveBeenCalledWith('{name} World', 'meta_title')
    })

    it('disables the input when disabled is true', () => {
        wrapper = mountField({ disabled: true })
        expect(wrapper.find('input').attributes('disabled')).toBeDefined()
    })

    it('disables shortcode buttons when disabled is true', () => {
        wrapper = mountField({ shortcodes, disabled: true })
        wrapper.findAll('.shortcode-btn').forEach(btn => {
            expect(btn.attributes('disabled')).toBeDefined()
        })
    })

    it('shows the error message when error prop is set', () => {
        wrapper = mountField({ error: 'This field is required' })
        expect(wrapper.find('.invalid-feedback').text()).toBe('This field is required')
    })

    it('does not show an error message by default', () => {
        expect(wrapper.find('.invalid-feedback').exists()).toBe(false)
    })

    it('adds the is-invalid class to the input when there is an error', () => {
        wrapper = mountField({ error: 'Required' })
        expect(wrapper.find('input').classes()).toContain('is-invalid')
    })

    it('renders a tooltip when the tooltip prop is provided', () => {
        wrapper = mountField({ tooltip: 'Some hint text' })
        const html = wrapper.html().toLowerCase()
        expect(
            wrapper.find('tooltip-stub').exists() ||
            wrapper.find('tool-tip-stub').exists() ||
            html.includes('tooltip')
        ).toBe(true)
    })

    it('does not render a tooltip by default', () => {
        expect(wrapper.find('tooltip-stub').exists()).toBe(false)
        expect(wrapper.find('tool-tip-stub').exists()).toBe(false)
    })
})
