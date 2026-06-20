jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { platform: 'crm', module: 'contacts' }, query: {} }),
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ZohoMapping from '@/pages/admin/settings/api/ZohoMapping.vue'
const INTEGRATIONS_RESPONSE = {
    data: [{ id: 1, platform: 'crm' }],
}

const FIELDS_RESPONSE = {
    data: [{ id: 10, field_name: 'First Name' }, { id: 11, field_name: 'Last Name' }],
}

describe('ZohoMapping.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/zoho\/integrations/).reply(200, INTEGRATIONS_RESPONSE)
        global.mockHttp.onGet(/\/zoho\//).reply(200, FIELDS_RESPONSE)
        global.mockHttp.onPost(/\/zoho\/mapping\/save/).reply(200, { data: {} })
        wrapper = mount(ZohoMapping, {
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

    it('calls GET /zoho/integrations on mount', async () => {
        await flushPromises()
        expect(
            global.mockHttp.history.get.some(r => r.url.includes('/zoho/integrations'))
        ).toBe(true)
    })

    it('calls GET fields and mapping endpoints on mount', async () => {
        await flushPromises()
        const zohoUrls = global.mockHttp.history.get.filter(r => /\/zoho\//.test(r.url))
        expect(zohoUrls.length).toBeGreaterThanOrEqual(2)
        expect(zohoUrls.some(r => r.url.includes('/fields'))).toBe(true)
        expect(zohoUrls.some(r => r.url.includes('/mapping/data'))).toBe(true)
    })

    it('sets integrationId after fetching integrations', async () => {
        await flushPromises()
        expect(wrapper.vm.integrationId).toBe(1)
    })

    it('calls POST /zoho/mapping/save when save is invoked', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(
            global.mockHttp.history.post.some(r => r.url.includes('/zoho/mapping/save'))
        ).toBe(true)
    })

    it('addRow appends a new empty row', async () => {
        await flushPromises()
        const before = wrapper.vm.rows.length
        wrapper.vm.addRow()
        expect(wrapper.vm.rows.length).toBe(before + 1)
        const last = wrapper.vm.rows[wrapper.vm.rows.length - 1]
        expect(last.zohoId).toBeNull()
        expect(last.targetValue).toBeNull()
    })

    it('removeRow removes the row at the given index', async () => {
        await flushPromises()
        wrapper.vm.addRow()
        wrapper.vm.addRow()
        const before = wrapper.vm.rows.length
        wrapper.vm.removeRow(before - 1)
        expect(wrapper.vm.rows.length).toBe(before - 1)
    })

    it('switchTab calls loadMappings for the new tab', async () => {
        await flushPromises()
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/zoho\//).reply(200, FIELDS_RESPONSE)
        await wrapper.vm.switchTab('accounts')
        await flushPromises()
        expect(wrapper.vm.activeModule).toBe('accounts')
        const zohoUrls = global.mockHttp.history.get.filter(r => /\/zoho\//.test(r.url))
        expect(zohoUrls.length).toBeGreaterThanOrEqual(2)
    })
})
