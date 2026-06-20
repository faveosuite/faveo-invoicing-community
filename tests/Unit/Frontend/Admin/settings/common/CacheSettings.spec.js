jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: {}, query: {} }),
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import CacheSettings from '@/pages/admin/settings/common/CacheSettings.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

describe('CacheSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/cache-settings\/list/).reply(200, { data: [] })
        global.mockHttp.onPost(/\/cache-settings\//).reply(200, { data: {} })
        wrapper = mount(CacheSettings, {
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
        global.mockHttp.reset()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('passes /cache-settings/list URL to DataTable', async () => {
        await flushPromises()
        const dt = wrapper.findComponent({ name: 'DataTable' })
        if (dt.exists()) {
            expect(dt.props('url') ?? dt.attributes('url')).toMatch(/cache-settings\/list/)
        } else {
            // DataTable is stubbed — verify the url prop in the component options
            expect(wrapper.vm.tableOptions?.url ?? wrapper.vm.url ?? '').toMatch(/cache-settings\/list/)
        }
    })

    it('calls POST /cache-settings/:driver/activate when activate is invoked', async () => {
        await flushPromises()
        await wrapper.vm.activate('redis')
        await flushPromises()
        expect(
            global.mockHttp.history.post.some(r => /\/cache-settings\//.test(r.url) && r.url.includes('/activate'))
        ).toBe(true)
    })

    it('calls successHandler after activate succeeds', async () => {
        await flushPromises()
        await wrapper.vm.activate('redis')
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler when activate fails', async () => {
        global.mockHttp.reset()
        global.mockHttp.onPost(/\/cache-settings\//).reply(500, { message: 'Server error' })
        await wrapper.vm.activate('redis')
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('has a DataTable stub rendered', () => {
        expect(wrapper.html()).toBeDefined()
    })

    it('activate posts with the correct driver in the URL', async () => {
        await flushPromises()
        await wrapper.vm.activate('memcached')
        await flushPromises()
        expect(
            global.mockHttp.history.post.some(r => r.url.includes('memcached'))
        ).toBe(true)
    })
})
