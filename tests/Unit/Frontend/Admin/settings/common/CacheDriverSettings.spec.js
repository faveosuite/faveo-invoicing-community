jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { driver: 'default' }, query: {} }),
}))
jest.mock('vee-validate', () => ({
    useForm: () => ({ errors: {}, setErrors: jest.fn(), setFieldError: jest.fn() }),
}))
jest.mock('@/validations/admin/cacheDriverValidations.js', () => ({ cacheDriverSchemas: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import CacheDriverSettings from '@/pages/admin/settings/common/CacheDriverSettings.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const FORM_RESPONSE = {
    data: {
        fields: [
            { name: 'host', label: 'Host', value: 'localhost' },
            { name: 'port', label: 'Port', value: '6379' },
        ],
    },
}

describe('CacheDriverSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/cache-settings\//).reply(200, FORM_RESPONSE)
        global.mockHttp.onPost(/\/cache-settings\//).reply(200, { data: {} })
        wrapper = mount(CacheDriverSettings, {
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

    it('calls GET /cache-settings/:driver/form on mount', async () => {
        await flushPromises()
        expect(
            global.mockHttp.history.get.some(r => /\/cache-settings\//.test(r.url))
        ).toBe(true)
    })

    it('populates fields from API response', async () => {
        await flushPromises()
        expect(wrapper.vm.fields).toHaveLength(2)
        expect(wrapper.vm.fields[0].name).toBe('host')
    })

    it('populates form values from field definitions', async () => {
        await flushPromises()
        expect(wrapper.vm.form.host).toBe('localhost')
        expect(wrapper.vm.form.port).toBe('6379')
    })

    it('calls POST /cache-settings/:driver when save is invoked', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(
            global.mockHttp.history.post.some(r => /\/cache-settings\//.test(r.url))
        ).toBe(true)
    })

    it('calls successHandler after save succeeds', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('sets loading to false after form loads', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('calls errorHandler when form load fails', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/cache-settings\//).reply(500, { message: 'Server error' })
        const w = mount(CacheDriverSettings, {
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
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })
})
