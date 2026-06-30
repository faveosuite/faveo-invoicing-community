import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import Alert from '@/components/Reusable/Alert.vue'
import { useAlertStore } from '@/core/stores/alert.js'

describe('Alert.vue', () => {
    let wrapper

    beforeAll(() => {
        // jsdom does not implement scrollIntoView
        globalThis.HTMLElement.prototype.scrollIntoView = jest.fn()
    })

    beforeEach(() => {
        wrapper = mount(Alert, {
            global: { plugins: [createTestingPinia()] },
            props: { componentName: 'test' },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('is hidden when store type is empty', () => {
        expect(wrapper.find('[role="alert"]').exists()).toBe(false)
    })

    it('shows alert when store has matching component_name', async () => {
        const store = useAlertStore()
        store.type = 'success'
        store.message = 'Saved!'
        store.component_name = 'test'
        await wrapper.vm.$nextTick()
        expect(wrapper.find('[role="alert"]').exists()).toBe(true)
    })

    it('does not show alert when component_name does not match', async () => {
        const store = useAlertStore()
        store.type = 'success'
        store.message = 'Saved!'
        store.component_name = 'other-component'
        await wrapper.vm.$nextTick()
        expect(wrapper.find('[role="alert"]').exists()).toBe(false)
    })

    it('applies correct alert class for success type', async () => {
        const store = useAlertStore()
        store.type = 'success'
        store.message = 'All good'
        store.component_name = 'test'
        await wrapper.vm.$nextTick()
        expect(wrapper.find('[role="alert"]').classes()).toContain('alert-success')
    })

    it('applies correct alert class for danger type', async () => {
        const store = useAlertStore()
        store.type = 'danger'
        store.message = 'Error occurred'
        store.component_name = 'test'
        await wrapper.vm.$nextTick()
        expect(wrapper.find('[role="alert"]').classes()).toContain('alert-danger')
    })

    it('applies correct alert class for warning type', async () => {
        const store = useAlertStore()
        store.type = 'warning'
        store.message = 'Watch out'
        store.component_name = 'test'
        await wrapper.vm.$nextTick()
        expect(wrapper.find('[role="alert"]').classes()).toContain('alert-warning')
    })

    it('renders message content', async () => {
        const store = useAlertStore()
        store.type = 'success'
        store.message = 'Record created'
        store.component_name = 'test'
        await wrapper.vm.$nextTick()
        expect(wrapper.find('[role="alert"]').text()).toContain('Record created')
    })

    it('shows success icon for success type', async () => {
        const store = useAlertStore()
        store.type = 'success'
        store.message = 'OK'
        store.component_name = 'test'
        await wrapper.vm.$nextTick()
        expect(wrapper.find('.fa-circle-check').exists()).toBe(true)
    })

    it('shows danger icon for danger type', async () => {
        const store = useAlertStore()
        store.type = 'danger'
        store.message = 'Error'
        store.component_name = 'test'
        await wrapper.vm.$nextTick()
        expect(wrapper.find('.fa-triangle-exclamation').exists()).toBe(true)
    })

    it('shows warning icon for warning type', async () => {
        const store = useAlertStore()
        store.type = 'warning'
        store.message = 'Warning'
        store.component_name = 'test'
        await wrapper.vm.$nextTick()
        expect(wrapper.find('.fa-circle-exclamation').exists()).toBe(true)
    })

    it('shows info icon for info type', async () => {
        const store = useAlertStore()
        store.type = 'info'
        store.message = 'Info'
        store.component_name = 'test'
        await wrapper.vm.$nextTick()
        expect(wrapper.find('.fa-circle-info').exists()).toBe(true)
    })

    it('calls unsetAlert when close button is clicked', async () => {
        const store = useAlertStore()
        store.type = 'success'
        store.message = 'OK'
        store.component_name = 'test'
        await wrapper.vm.$nextTick()
        await wrapper.find('.btn-close').trigger('click')
        expect(store.unsetAlert).toHaveBeenCalled()
    })

    it('uses default componentName of empty string', () => {
        const w = mount(Alert, {
            global: { plugins: [createTestingPinia()] },
        })
        expect(w.exists()).toBeTruthy()
    })
})
