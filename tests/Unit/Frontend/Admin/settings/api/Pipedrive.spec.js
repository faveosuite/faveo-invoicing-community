jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('@/validations/admin/pipedriveValidations', () => ({
    apiKeySchema: { validate: jest.fn(() => Promise.resolve(true)) },
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import Pipedrive from '@/pages/admin/settings/api/Pipedrive.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'SelectField', 'AppModal', 'ImageUpload', 'PhoneField',
    'RecaptchaProvider', 'RecaptchaCheckbox', 'RecaptchaV2Invisible', 'RecaptchaV3',
    'ZohoCard', 'spinner-loader',
]

describe('Pipedrive.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/settings\/pipedrive/).reply(200, {
            data: {
                pipedrive_key: 'pd-key',
                require_pipedrive_user_verification: false,
                groups: {},
            },
        })
        global.mockHttp.onPost(/\/updatepipedriveDetails/).reply(200, { data: { message: 'Connected' } })
        wrapper = mount(Pipedrive, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches settings on mount via GET /settings/pipedrive', async () => {
        await flushPromises()
        const getCalls = global.mockHttp.history.get.filter(r => /\/settings\/pipedrive/.test(r.url))
        expect(getCalls.length).toBeGreaterThan(0)
    })

    it('populates form from API response', async () => {
        await flushPromises()
        expect(wrapper.vm.form.apiKey).toBe('pd-key')
    })

    it('connectionStatus is connected after mount when a key is present', async () => {
        await flushPromises()
        expect(wrapper.vm.connectionStatus).toBe('connected')
    })

    it('sends POST /updatepipedriveDetails on connect()', async () => {
        await flushPromises()
        await wrapper.vm.connect()
        await flushPromises()
        const postCalls = global.mockHttp.history.post.filter(r => /\/updatepipedriveDetails/.test(r.url))
        expect(postCalls.length).toBeGreaterThan(0)
    })

    it('calls successHandler after successful connect', async () => {
        await flushPromises()
        await wrapper.vm.connect()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on connect failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/settings\/pipedrive/).reply(200, { data: {} })
        global.mockHttp.onPost(/\/updatepipedriveDetails/).reply(500)
        await flushPromises()
        await wrapper.vm.connect()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('loading is false after mount completes', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })
})
