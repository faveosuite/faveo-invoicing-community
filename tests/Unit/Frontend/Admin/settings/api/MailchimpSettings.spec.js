jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('@/validations/admin/mailchimpValidations', () => ({
    connectionSchema: { validate: jest.fn(() => Promise.resolve(true)) },
    listSchema: { validate: jest.fn(() => Promise.resolve(true)) },
}))
jest.mock('vue-select', () => ({ __esModule: true, default: { template: '<div />' } }), { virtual: true })

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MailchimpSettings from '@/pages/admin/settings/api/MailchimpSettings.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'SelectField', 'AppModal', 'ImageUpload', 'PhoneField',
    'RecaptchaProvider', 'RecaptchaCheckbox', 'RecaptchaV2Invisible', 'RecaptchaV3',
    'ZohoCard', 'spinner-loader', 'v-select',
]

describe('MailchimpSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/settings\/mailchimp/).reply(200, {
            data: {
                apiKey: '',
                listId: null,
                subscribeStatus: 'subscribed',
                connectionStatus: 'idle',
                lists: [],
            },
        })
        global.mockHttp.onPost(/\/updateMailchimpDetails/).reply(200, {
            data: { message: 'Connected', lists: [] },
        })
        global.mockHttp.onPatch(/\/mailchimp$/).reply(200, { data: { message: 'Saved' } })
        global.mockHttp.onGet(/\/mailchimp\/mapping-data/).reply(200, { data: {} })
        wrapper = mount(MailchimpSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches settings on mount via GET /settings/mailchimp', async () => {
        await flushPromises()
        const getCalls = global.mockHttp.history.get.filter(r => /\/settings\/mailchimp/.test(r.url))
        expect(getCalls.length).toBeGreaterThan(0)
    })

    it('connectionStatus initialises as idle', async () => {
        await flushPromises()
        expect(wrapper.vm.connectionStatus).toBe('idle')
    })

    it('calls POST /updateMailchimpDetails on connect()', async () => {
        await flushPromises()
        wrapper.vm.form.apiKey = 'test-key-us1'
        await wrapper.vm.connect()
        await flushPromises()
        const postCalls = global.mockHttp.history.post.filter(r => /\/updateMailchimpDetails/.test(r.url))
        expect(postCalls.length).toBeGreaterThan(0)
    })

    it('calls successHandler after successful connect', async () => {
        await flushPromises()
        wrapper.vm.form.apiKey = 'test-key-us1'
        await wrapper.vm.connect()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on connect failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/settings\/mailchimp/).reply(200, { data: {} })
        global.mockHttp.onPost(/\/updateMailchimpDetails/).reply(500)
        await flushPromises()
        wrapper.vm.form.apiKey = 'test-key-us1'
        await wrapper.vm.connect()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('calls PATCH /mailchimp on saveConnection()', async () => {
        await flushPromises()
        wrapper.vm.connectionStatus = 'connected'
        wrapper.vm.form.listId = 'list-123'
        wrapper.vm.form.subscribeStatus = 'subscribed'
        await wrapper.vm.saveConnection()
        await flushPromises()
        const patchCalls = global.mockHttp.history.patch.filter(r => /\/mailchimp$/.test(r.url))
        expect(patchCalls.length).toBeGreaterThan(0)
    })

    it('calls successHandler after successful saveConnection', async () => {
        await flushPromises()
        wrapper.vm.connectionStatus = 'connected'
        wrapper.vm.form.listId = 'list-123'
        wrapper.vm.form.subscribeStatus = 'subscribed'
        await wrapper.vm.saveConnection()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })
})
