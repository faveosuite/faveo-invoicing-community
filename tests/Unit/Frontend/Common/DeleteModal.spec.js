import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'

const onCloseMock = jest.fn()

const mountModal = (props = {}) =>
    mount(DeleteModal, {
        props: {
            showModal: true,
            onClose: onCloseMock,
            deleteUrl: '/delete/1',
            ...props,
        },
        global: {
            plugins: [createTestingPinia()],
            stubs: {
                Teleport: { template: '<div><slot /></div>' },
                AppAlert: true,
                'spinner-loader': true,
            },
        },
    })

describe('DeleteModal.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        global.mockHttp.onDelete('/delete/1').reply(200, { data: { message: 'Deleted' } })
        wrapper = mountModal()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders modal content', () => {
        expect(wrapper.find('.modal').exists()).toBe(true)
    })

    it('renders the modal title', () => {
        expect(wrapper.find('.modal-title').text()).toBe('Delete')
    })

    it('renders custom title when provided', () => {
        wrapper = mountModal({ title: 'Confirm Removal' })
        expect(wrapper.find('.modal-title').text()).toBe('Confirm Removal')
    })

    it('renders the modal message', () => {
        expect(wrapper.find('.modal-body p').text()).toBe('Are you sure you want to delete this record?')
    })

    it('renders custom message when provided', () => {
        wrapper = mountModal({ message: 'Really delete this?' })
        expect(wrapper.find('.modal-body p').text()).toBe('Really delete this?')
    })

    it('calls onClose when cancel button is clicked', async () => {
        await wrapper.find('button.btn-secondary').trigger('click')
        expect(onCloseMock).toHaveBeenCalled()
    })

    it('calls onClose when close (x) button is clicked', async () => {
        await wrapper.find('button.btn-close').trigger('click')
        expect(onCloseMock).toHaveBeenCalled()
    })

    it('renders submit button with danger variant by default', () => {
        expect(wrapper.find('button.btn-danger').exists()).toBe(true)
    })

    it('renders submit button with custom variant', () => {
        wrapper = mountModal({ btnVariant: 'success' })
        expect(wrapper.find('button.btn-success').exists()).toBe(true)
    })

    it('renders custom button label', () => {
        wrapper = mountModal({ btnLabel: 'Yes, delete' })
        expect(wrapper.find('button.btn-danger').text()).toContain('Yes, delete')
    })

    it('emits deleted event on successful delete', async () => {
        await wrapper.find('button.btn-danger').trigger('click')
        await flushPromises()
        expect(wrapper.emitted('deleted')).toBeTruthy()
    })

    it('calls onClose after successful delete', async () => {
        await wrapper.find('button.btn-danger').trigger('click')
        await flushPromises()
        expect(onCloseMock).toHaveBeenCalled()
    })

    it('disables buttons while loading', async () => {
        let resolveReq = null
        global.mockHttp.reset()
        global.mockHttp.onDelete('/delete/1').reply(
            () => new Promise(r => { resolveReq = r })
        )
        wrapper = mountModal()
        // Start submit — don't await so we can check mid-flight state
        wrapper.find('button.btn-danger').trigger('click')
        await wrapper.vm.$nextTick()
        expect(wrapper.find('button.btn-secondary').attributes('disabled')).toBeDefined()
        // Resolve and clean up
        if (resolveReq) resolveReq([200, { data: { message: 'OK' } }])
        await flushPromises()
    })

    it('uses POST method when method prop is post', async () => {
        global.mockHttp.onPost('/delete/1').reply(200, { data: { message: 'OK' } })
        wrapper = mountModal({ method: 'post' })
        await wrapper.find('button.btn-danger').trigger('click')
        await flushPromises()
        expect(wrapper.emitted('deleted')).toBeTruthy()
    })
})
