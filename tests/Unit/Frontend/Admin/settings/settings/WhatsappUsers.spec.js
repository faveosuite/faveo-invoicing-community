jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/systemSettingsValidations', () => ({ webhookUrlSchema: {} }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDateTime: jest.fn((v) => v) }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import WhatsappUsers from '@/pages/admin/settings/settings/WhatsappUsers.vue'

describe('WhatsappUsers.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(WhatsappUsers, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'AppModal', 'DataTable', 'TextField', 'action-button'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the DataTable stub', () => {
        expect(wrapper.html().toLowerCase()).toContain('data-table-stub')
    })

    it('openEdit sets editRow and editWebhookUrl', () => {
        const row = { id: 1, callback_url: 'https://example.com/webhook' }
        wrapper.vm.openEdit(row)
        expect(wrapper.vm.editRow).toEqual(row)
        expect(wrapper.vm.editWebhookUrl).toBe('https://example.com/webhook')
    })

    it('closeEdit clears editRow and editWebhookUrl', () => {
        wrapper.vm.editRow = { id: 1 }
        wrapper.vm.editWebhookUrl = 'https://example.com/webhook'
        wrapper.vm.closeEdit()
        expect(wrapper.vm.editRow).toBeNull()
        expect(wrapper.vm.editWebhookUrl).toBe('')
    })

    it('calls POST webhook-url-edit on saveWebhook', async () => {
        global.mockHttp.onPost(/\/webhook-url-edit/).reply(200, { data: {} })
        wrapper.vm.editRow = { id: 3, callback_url: 'https://hook.example.com' }
        wrapper.vm.editWebhookUrl = 'https://hook.example.com/updated'
        wrapper.vm.saveWebhook()
        await flushPromises()
        expect(global.mockHttp.history.post.some(r => r.url.includes('webhook-url-edit'))).toBeTruthy()
    })

    it('calls successHandler after successful saveWebhook', async () => {
        const { successHandler } = require('@/helpers/responseHandler')
        global.mockHttp.onPost(/\/webhook-url-edit/).reply(200, { data: {} })
        wrapper.vm.editRow = { id: 4, callback_url: '' }
        wrapper.vm.editWebhookUrl = 'https://hook.example.com'
        wrapper.vm.saveWebhook()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })
})
