jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import Currency from '@/pages/admin/settings/common/Currency.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

describe('Currency.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/currency\/list/).reply(200, { data: [] })
        globalThis.mockHttp.onPost(/\/currency\/update-currency/).reply(200, { data: {} })
        globalThis.mockHttp.onPost(/\/currency\/default-currency\//).reply(200, { data: {} })
        globalThis.mockHttp.onPost(/\/currency\/dashboard-currency\//).reply(200, { data: {} })
        wrapper = mount(Currency, {
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

    it('provides /currency/list URL to DataTable', async () => {
        await flushPromises()
        const dt = wrapper.findComponent({ name: 'DataTable' })
        if (dt.exists()) {
            const urlProp = dt.props('url') ?? dt.attributes('url') ?? ''
            expect(urlProp).toMatch(/currency\/list/)
        } else {
            const opts = wrapper.vm.tableOptions ?? {}
            expect(opts.url ?? '').toMatch(/currency\/list/)
        }
    })

    it('calls POST /currency/update-currency when toggleStatus is invoked', async () => {
        await flushPromises()
        await wrapper.vm.toggleStatus({ id: 1, is_active: 1 })
        await flushPromises()
        expect(
            globalThis.mockHttp.history.post.some(r => r.url.includes('/currency/update-currency'))
        ).toBe(true)
    })

    it('calls POST /currency/default-currency/:id when setDefault is invoked', async () => {
        await flushPromises()
        await wrapper.vm.setDefault(1)
        await flushPromises()
        expect(
            globalThis.mockHttp.history.post.some(r => /\/currency\/default-currency\//.test(r.url))
        ).toBe(true)
    })

    it('calls POST /currency/dashboard-currency/:id when setDashboard is invoked', async () => {
        await flushPromises()
        await wrapper.vm.setDashboard(2)
        await flushPromises()
        expect(
            globalThis.mockHttp.history.post.some(r => /\/currency\/dashboard-currency\//.test(r.url))
        ).toBe(true)
    })

    it('calls successHandler after toggleStatus succeeds', async () => {
        await flushPromises()
        await wrapper.vm.toggleStatus({ id: 1, is_active: 1 })
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls successHandler after setDefault succeeds', async () => {
        await flushPromises()
        await wrapper.vm.setDefault(1)
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler when toggleStatus fails', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/currency\/update-currency/).reply(500, { message: 'Server error' })
        await wrapper.vm.toggleStatus({ id: 1, is_active: 1 })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })
})
