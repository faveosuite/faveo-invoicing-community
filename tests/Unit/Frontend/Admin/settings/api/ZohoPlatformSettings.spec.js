jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { platform: 'crm' }, query: {} }),
}))
jest.mock('vee-validate', () => ({
    useForm: () => ({ errors: {}, setErrors: jest.fn(), setFieldError: jest.fn() }),
}))
jest.mock('@/validations/admin/zohoValidations.js', () => ({ zohoCredentialsSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ZohoPlatformSettings from '@/pages/admin/settings/api/ZohoPlatformSettings.vue'
import { successHandler } from '@/helpers/responseHandler'

const INTEGRATIONS_RESPONSE = {
    data: [{ id: 5, platform: 'crm' }],
}

const KEYS_RESPONSE = {
    data: { client_id: 'cid123', client_secret: 'csecret456', region: 'us' },
}

const FIELDS_RESPONSE = {
    data: [{ id: 10, field_name: 'First Name' }],
}

describe('ZohoPlatformSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/zoho\/integrations/).reply(200, INTEGRATIONS_RESPONSE)
        global.mockHttp.onGet(/\/zoho\/getKeys\//).reply(200, KEYS_RESPONSE)
        global.mockHttp.onGet(/\/zoho\//).reply(200, FIELDS_RESPONSE)
        global.mockHttp.onPost(/\/zoho\/saveKeys/).reply(200, { data: {} })
        global.mockHttp.onPost(/\/zoho\/mapping\/save/).reply(200, { data: {} })
        wrapper = mount(ZohoPlatformSettings, {
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

    it('calls GET /zoho/getKeys/:id on mount when integrationId is set', async () => {
        await flushPromises()
        expect(
            global.mockHttp.history.get.some(r => /\/zoho\/getKeys\//.test(r.url))
        ).toBe(true)
    })

    it('sets integrationId and populates form from fetched keys', async () => {
        await flushPromises()
        expect(wrapper.vm.integrationId).toBe(5)
        expect(wrapper.vm.form.client_id).toBe('cid123')
        expect(wrapper.vm.form.client_secret).toBe('csecret456')
        expect(wrapper.vm.form.region).toBe('us')
    })

    it('calls POST /zoho/saveKeys when saveConnection is invoked', async () => {
        await flushPromises()
        await wrapper.vm.saveConnection()
        await flushPromises()
        expect(
            global.mockHttp.history.post.some(r => r.url.includes('/zoho/saveKeys'))
        ).toBe(true)
    })

    it('calls POST /zoho/mapping/save when saveMapping is invoked', async () => {
        await flushPromises()
        await wrapper.vm.saveMapping()
        await flushPromises()
        expect(
            global.mockHttp.history.post.some(r => r.url.includes('/zoho/mapping/save'))
        ).toBe(true)
    })

    it('switches activeTab between connection and field-mapping', async () => {
        await flushPromises()
        expect(wrapper.vm.activeTab).toBe('connection')
        wrapper.vm.activeTab = 'field-mapping'
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.activeTab).toBe('field-mapping')
        wrapper.vm.activeTab = 'connection'
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.activeTab).toBe('connection')
    })

    it('does not call GET getKeys when integrationId is null', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/zoho\/integrations/).reply(200, { data: [] })
        global.mockHttp.onGet(/\/zoho\//).reply(200, FIELDS_RESPONSE)

        const w = mount(ZohoPlatformSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
                    'DynamicSelect', 'TextField', 'StaticSelect', 'loader', 'ColumnSelector',
                    'Switch', 'SelectField', 'ZohoCard', 'spinner-loader', 'CurrencyTableActions',
                ],
            },
        })
        await flushPromises()
        expect(
            global.mockHttp.history.get.some(r => /\/zoho\/getKeys\//.test(r.url))
        ).toBe(false)
        w.unmount()
    })

    it('calls successHandler after saveConnection succeeds', async () => {
        await flushPromises()
        await wrapper.vm.saveConnection()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })
})
