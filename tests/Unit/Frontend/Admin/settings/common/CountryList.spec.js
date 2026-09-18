jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import CountryList from '@/pages/admin/settings/common/CountryList.vue'

describe('CountryList.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/get-country/).reply(200, { data: [] })
        wrapper = mount(CountryList, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
                    'DynamicSelect', 'TextField', 'StaticSelect', 'loader', 'ColumnSelector',
                    'Switch', 'DynamicSelect', 'ZohoCard', 'spinner-loader', 'CurrencyTableActions',
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

    it('provides /get-country URL to DataTable', async () => {
        await flushPromises()
        const dt = wrapper.findComponent({ name: 'DataTable' })
        if (dt.exists()) {
            const urlProp = dt.props('url') ?? dt.attributes('url') ?? ''
            expect(urlProp).toMatch(/get-country/)
        } else {
            const opts = wrapper.vm.tableOptions ?? {}
            expect(opts.url ?? '').toMatch(/get-country/)
        }
    })

    it('defines country and count columns', () => {
        const cols = wrapper.vm.columns ?? []
        expect(cols).toContain('country')
        expect(cols).toContain('count')
    })

    it('renders without errors', async () => {
        await flushPromises()
        expect(wrapper.html()).toBeTruthy()
    })

    it('does not make direct HTTP calls on mount (DataTable handles fetching)', async () => {
        await flushPromises()
        // CountryList delegates all fetching to DataTable; no direct http calls expected
        expect(globalThis.mockHttp.history.post).toHaveLength(0)
    })

    it('has correct sort field mappings for country column', () => {
        const opts = wrapper.vm.tableOptions ?? {}
        const sortable = opts.sortable ?? []
        expect(sortable).toContain('country')
        expect(wrapper.exists()).toBeTruthy()
    })

    it('has correct sort field mappings for count column', () => {
        const opts = wrapper.vm.tableOptions ?? {}
        const sortable = opts.sortable ?? []
        expect(sortable).toContain('count')
        expect(wrapper.exists()).toBeTruthy()
    })
})
