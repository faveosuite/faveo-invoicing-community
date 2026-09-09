import { mount } from '@vue/test-utils'
import FormActions from '@/components/Reusable/FormActions.vue'

const mountFormActions = (props = {}) =>
    mount(FormActions, {
        props,
        global: {
            stubs: {
                'action-button': {
                    template: '<button class="action-btn" @click="$emit(\'click\')"><slot /></button>',
                    props: ['action', 'loading', 'disabled', 'to'],
                    emits: ['click'],
                },
            },
        },
    })

describe('FormActions.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mountFormActions()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the form-actions-group container', () => {
        expect(wrapper.find('.form-actions-group').exists()).toBe(true)
    })

    it('renders save action button by default', () => {
        const buttons = wrapper.findAll('.action-btn')
        expect(buttons.length).toBeGreaterThanOrEqual(1)
    })

    it('does not render cancel button when cancelTo and onCancel are not provided', () => {
        // Only one action-button rendered (the submit one)
        const buttons = wrapper.findAll('.action-btn')
        expect(buttons.length).toBe(1)
    })

    it('renders cancel button when cancelTo is provided', () => {
        wrapper = mountFormActions({ cancelTo: '/back' })
        const buttons = wrapper.findAll('.action-btn')
        expect(buttons.length).toBe(2)
    })

    it('renders cancel button when onCancel function is provided', () => {
        wrapper = mountFormActions({ onCancel: jest.fn() })
        const buttons = wrapper.findAll('.action-btn')
        expect(buttons.length).toBe(2)
    })

    it('emits submit event when action button is clicked', async () => {
        await wrapper.find('.action-btn').trigger('click')
        expect(wrapper.emitted('submit')).toBeTruthy()
    })

    it('renders custom action prop', () => {
        wrapper = mountFormActions({ action: 'update' })
        expect(wrapper.exists()).toBeTruthy()
    })

    it('passes loading prop to action button', () => {
        wrapper = mountFormActions({ loading: true })
        expect(wrapper.exists()).toBeTruthy()
    })

    it('passes disabled prop to action button', () => {
        wrapper = mountFormActions({ disabled: true })
        expect(wrapper.exists()).toBeTruthy()
    })
})
