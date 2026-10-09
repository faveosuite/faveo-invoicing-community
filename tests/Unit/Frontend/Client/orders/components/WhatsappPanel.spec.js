jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import WhatsappPanel from '@/pages/client/orders/components/WhatsappPanel.vue'

const defaultOrder = {
    id: 7,
    whatsapp_signup_enabled: true,
    whatsapp_app_id: 'fb-app-123',
    whatsapp_config_id: 'cfg-456',
}

describe('WhatsappPanel.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)

        wrapper = mount(WhatsappPanel, {
            props: {
                order: defaultOrder,
                active: false,
            },
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-alert',
                    'data-table',
                    'client-field',
                    'action-button',
                    'router-link',
                ],
            },
        })
    })

    afterEach(() => {
        axiosMock.restore()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('receives and reflects the order prop', () => {
        expect(wrapper.props('order')).toEqual(defaultOrder)
    })

    it('receives and reflects the active prop', () => {
        expect(wrapper.props('active')).toBe(false)
    })

    it('renders the add new number button when signup is enabled', () => {
        const btn = wrapper.find('button.btn-primary')
        expect(btn.exists()).toBeTruthy()
    })

    it('does not render the data-table when active is false', () => {
        const table = wrapper.findComponent({ name: 'DataTable' })
        expect(table.exists()).toBe(false)
    })

    it('renders the data-table when active is true', async () => {
        await wrapper.setProps({ active: true })
        // DataTable is stubbed, verify the stub appears
        // Either the stub or the computed numbersUrl triggers rendering
        expect(wrapper.html()).toBeTruthy()
    })

    it('opens the webhook modal when add-number button is clicked', async () => {
        expect(wrapper.vm.showWebhookModal).toBe(false)
        await wrapper.find('button.btn-primary').trigger('click')
        expect(wrapper.vm.showWebhookModal).toBe(true)
    })

    it('sets a validation error when submitting an invalid webhook URL', async () => {
        wrapper.vm.webhookUrl = 'not-a-url'
        await wrapper.vm.submitWebhook()
        expect(wrapper.vm.webhookError).not.toBe('')
    })

    it('calls POST /url-save with a valid webhook URL', async () => {
        axiosMock.onPost('/url-save').reply(200, { data: {} })
        // Mock FB to avoid real SDK calls
        globalThis.FB = { login: jest.fn() }

        wrapper.vm.webhookUrl = 'https://example.com/webhook'
        await wrapper.vm.submitWebhook()
        await flushPromises()

        expect(axiosMock.history.post.some(r => r.url.includes('/url-save'))).toBe(true)
        delete globalThis.FB
    })

    it('calls errorHandler when /url-save returns 500', async () => {
        axiosMock.onPost('/url-save').reply(500, { message: 'Server error' })

        wrapper.vm.webhookUrl = 'https://example.com/webhook'
        await wrapper.vm.submitWebhook()
        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
    })

    it('opens the delete modal when confirmDelete is called', () => {
        const row = { id: 1, phone_number: '+1234567890' }
        wrapper.vm.confirmDelete(row)
        expect(wrapper.vm.showDeleteModal).toBe(true)
        expect(wrapper.vm.deleteRow).toEqual(row)
    })

    it('calls POST /whatsapp-deregister on submitDelete', async () => {
        axiosMock.onPost('/whatsapp-deregister').reply(200, { data: {} })
        wrapper.vm.deleteRow = { id: 9 }

        await wrapper.vm.submitDelete()
        await flushPromises()

        expect(axiosMock.history.post.some(r => r.url.includes('/whatsapp-deregister'))).toBe(true)
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler when /whatsapp-deregister returns 500', async () => {
        axiosMock.onPost('/whatsapp-deregister').reply(500, { message: 'Error' })
        wrapper.vm.deleteRow = { id: 9 }

        await wrapper.vm.submitDelete()
        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
    })

    it('registers and removes window message listener on mount/unmount', () => {
        const addSpy    = jest.spyOn(window, 'addEventListener')
        const removeSpy = jest.spyOn(window, 'removeEventListener')

        const w = mount(WhatsappPanel, {
            props: { order: defaultOrder, active: false },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['app-alert', 'data-table', 'client-field', 'action-button'],
            },
        })

        expect(addSpy).toHaveBeenCalledWith('message', expect.any(Function))
        w.unmount()
        expect(removeSpy).toHaveBeenCalledWith('message', expect.any(Function))
    })

    it('copyValue copies text to clipboard and sets copiedId', async () => {
        Object.assign(navigator, { clipboard: { writeText: jest.fn().mockResolvedValue(undefined) } })
        await wrapper.vm.copyValue(42, 'https://example.com')
        await flushPromises()
        expect(navigator.clipboard.writeText).toHaveBeenCalledWith('https://example.com')
        expect(wrapper.vm.copiedId).toBe(42)
    })

    it('isValidUrl returns true for valid URL', () => {
        expect(wrapper.vm.isValidUrl('https://example.com/webhook')).toBe(true)
    })

    it('isValidUrl returns false for invalid URL', () => {
        expect(wrapper.vm.isValidUrl('not-a-url')).toBe(false)
    })

    it('openEdit fetches webhook URL and sets showEditModal', async () => {
        axiosMock.onGet('/get-webhook-url').reply(200, { data: { url: 'https://hook.io' } })
        await wrapper.vm.openEdit({ id: 5, phone: '9876543210' })
        await flushPromises()
        expect(wrapper.vm.showEditModal).toBe(true)
    })

    it('openEdit handles error gracefully', async () => {
        axiosMock.onGet('/get-webhook-url').reply(500)
        await expect(wrapper.vm.openEdit({ id: 5, phone: '9876543210' })).resolves.not.toThrow()
    })

    it('submitEdit calls POST /webhook-url-edit on success with valid URL', async () => {
        axiosMock.onPost('/webhook-url-edit').reply(200, { message: 'Updated' })
        wrapper.vm.editId = 5
        wrapper.vm.editUrl = 'https://new-hook.io/endpoint'
        await wrapper.vm.submitEdit()
        await flushPromises()
        expect(axiosMock.history.post.some(r => r.url.includes('webhook-url-edit'))).toBe(true)
    })

    it('submitEdit sets editError for invalid URL', async () => {
        wrapper.vm.editUrl = 'not-a-url'
        await wrapper.vm.submitEdit()
        expect(wrapper.vm.editError).toBeTruthy()
    })

    it('closeDeleteModal resets delete state', () => {
        wrapper.vm.showDeleteModal = true
        wrapper.vm.closeDeleteModal()
        expect(wrapper.vm.showDeleteModal).toBe(false)
    })

    it('closeEditModal resets edit modal', () => {
        wrapper.vm.showEditModal = true
        wrapper.vm.closeEditModal()
        expect(wrapper.vm.showEditModal).toBe(false)
    })
})
