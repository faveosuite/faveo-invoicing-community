import { mount } from '@vue/test-utils'
import ActionButton from '@/components/Reusable/ActionButton.vue'

describe('ActionButton.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(ActionButton, {
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders a button by default', () => {
        expect(wrapper.element.tagName).toBe('BUTTON')
    })

    it('applies btn class', () => {
        expect(wrapper.classes()).toContain('btn')
    })

    it('renders action save with correct classes', () => {
        wrapper = mount(ActionButton, {
            props: { action: 'save' },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.classes()).toContain('btn-primary')
        expect(wrapper.find('.action-btn-icon').classes()).toContain('fa-save')
    })

    it('renders action delete with danger variant', () => {
        wrapper = mount(ActionButton, {
            props: { action: 'delete' },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.classes()).toContain('btn-danger')
    })

    it('renders action cancel with secondary variant', () => {
        wrapper = mount(ActionButton, {
            props: { action: 'cancel' },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.classes()).toContain('btn-secondary')
    })

    it('shows spinner when loading is true', () => {
        wrapper = mount(ActionButton, {
            props: { loading: true },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.find('.action-btn-spinner').exists()).toBe(true)
        expect(wrapper.find('.action-btn-icon').exists()).toBe(false)
    })

    it('does not show spinner when loading is false', () => {
        wrapper = mount(ActionButton, {
            props: { loading: false, icon: 'fas fa-save' },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.find('.action-btn-spinner').exists()).toBe(false)
    })

    it('is disabled when disabled prop is true', () => {
        wrapper = mount(ActionButton, {
            props: { disabled: true },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.attributes('disabled')).toBeDefined()
    })

    it('is disabled when loading is true', () => {
        wrapper = mount(ActionButton, {
            props: { loading: true },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.attributes('disabled')).toBeDefined()
    })

    it('applies size class when size prop is given', () => {
        wrapper = mount(ActionButton, {
            props: { size: 'sm' },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.classes()).toContain('btn-sm')
    })

    it('applies custom variant class', () => {
        wrapper = mount(ActionButton, {
            props: { variant: 'warning' },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.classes()).toContain('btn-warning')
    })

    it('shows custom icon when icon prop is provided', () => {
        wrapper = mount(ActionButton, {
            props: { icon: 'fas fa-star' },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.find('.action-btn-icon').classes()).toContain('fa-star')
    })

    it('shows label when label prop is provided', () => {
        wrapper = mount(ActionButton, {
            props: { label: 'Custom Label' },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.find('.action-btn-label').text()).toBe('Custom Label')
    })

    it('hides label when iconOnly is true', () => {
        wrapper = mount(ActionButton, {
            props: { action: 'save', iconOnly: true },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.find('.action-btn-label').exists()).toBe(false)
    })

    it('shows label when iconOnly is false', () => {
        wrapper = mount(ActionButton, {
            props: { action: 'save', iconOnly: false },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.find('.action-btn-label').exists()).toBe(true)
    })

    it('renders table_btn class for table actions (edit)', () => {
        wrapper = mount(ActionButton, {
            props: { action: 'edit' },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.classes()).toContain('table_btn')
    })

    it('does not render table_btn class when variant is overridden', () => {
        wrapper = mount(ActionButton, {
            props: { action: 'edit', variant: 'primary' },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.classes()).not.toContain('table_btn')
    })

    it('renders button type attribute', () => {
        wrapper = mount(ActionButton, {
            props: { type: 'submit' },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.attributes('type')).toBe('submit')
    })

    it('has aria-busy when loading', () => {
        wrapper = mount(ActionButton, {
            props: { loading: true },
            global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } },
        })
        expect(wrapper.attributes('aria-busy')).toBe('true')
    })
})
