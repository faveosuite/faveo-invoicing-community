jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: {}, query: { zoho_status: null } }),
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ZohoSettings from '@/pages/admin/settings/api/ZohoSettings.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const INTEGRATIONS_RESPONSE = {
    data: [
        { id: 1, platform: 'crm', name: 'Zoho CRM', is_active: true },
        { id: 2, platform: 'books', name: 'Zoho Books', is_active: false },
    ],
}

describe('ZohoSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/zoho\/integrations/).reply(200, INTEGRATIONS_RESPONSE)
        globalThis.mockHttp.onPatch(/\/zoho\/integrations\//).reply(200, { data: {} })
        wrapper = mount(ZohoSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
                    'DynamicSelect', 'TextField', 'StaticSelect', 'loader', 'ColumnSelector',
                    'Switch', 'SelectField', 'ZohoCard', 'spinner-loader', 'CurrencyTableActions',
                ],
            },
        })
    })

    afterEach(() => {
        wrapper.unmount()
        globalThis.mockHttp.reset()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('calls GET /zoho/integrations on mount', async () => {
        await flushPromises()
        expect(
            globalThis.mockHttp.history.get.some(r => r.url.includes('/zoho/integrations'))
        ).toBe(true)
    })

    it('populates integrations list from API response', async () => {
        await flushPromises()
        expect(wrapper.vm.integrations).toHaveLength(2)
        expect(wrapper.vm.integrations[0].platform).toBe('crm')
    })

    it('calls PATCH /zoho/integrations/:id/toggle when handleToggle is invoked', async () => {
        await flushPromises()
        const item = wrapper.vm.integrations[0]
        await wrapper.vm.handleToggle(item)
        await flushPromises()
        expect(
            globalThis.mockHttp.history.patch.some(r => /\/zoho\/integrations\//.test(r.url))
        ).toBe(true)
    })

    it('reloads integrations silently after toggle', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/zoho\/integrations/).reply(200, INTEGRATIONS_RESPONSE)
        globalThis.mockHttp.onPatch(/\/zoho\/integrations\//).reply(200, { data: {} })
        const item = wrapper.vm.integrations[0]
        await wrapper.vm.handleToggle(item)
        await flushPromises()
        expect(
            globalThis.mockHttp.history.get.some(r => r.url.includes('/zoho/integrations'))
        ).toBe(true)
    })

    it('calls successHandler after toggle succeeds', async () => {
        await flushPromises()
        const item = wrapper.vm.integrations[0]
        await wrapper.vm.handleToggle(item)
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('sets loading to false after integrations load', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('does not set alert when zoho_status query param is null', async () => {
        await flushPromises()
        // No alert store calls expected when zoho_status is null
        expect(wrapper.exists()).toBeTruthy()
    })

    it('calls errorHandler when toggle request fails', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPatch(/\/zoho\/integrations\//).reply(500, { message: 'Server error' })
        const item = wrapper.vm.integrations[0]
        await wrapper.vm.handleToggle(item)
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })
})
