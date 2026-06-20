jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/emailValidationProviderValidations', () => ({
    emailValidationProviderSchema: {},
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import EmailValidationSettings from '@/pages/admin/settings/api/EmailValidationSettings.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'SelectField', 'AppModal', 'ImageUpload', 'PhoneField',
    'RecaptchaProvider', 'RecaptchaCheckbox', 'RecaptchaV2Invisible', 'RecaptchaV3',
    'ZohoCard', 'spinner-loader', 'router-link',
]

describe('EmailValidationSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/settings\/email-validation/).reply(200, {
            data: {
                provider: 'reoon',
                api_key: 'test-key',
                mode: 'quick',
                status_options: [],
            },
        })
        global.mockHttp.onPost(/\/email-settings-save/).reply(200, { data: { message: 'Saved' } })
        wrapper = mount(EmailValidationSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches settings on mount via GET /settings/email-validation', async () => {
        await flushPromises()
        const getCalls = global.mockHttp.history.get.filter(r => /\/settings\/email-validation/.test(r.url))
        expect(getCalls.length).toBeGreaterThan(0)
    })

    it('populates form from API response', async () => {
        await flushPromises()
        expect(wrapper.vm.form.provider).toBe('reoon')
        expect(wrapper.vm.form.apikey).toBe('test-key')
        expect(wrapper.vm.form.mode).toBe('quick')
    })

    it('sends POST /email-settings-save on save()', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        const postCalls = global.mockHttp.history.post.filter(r => /\/email-settings-save/.test(r.url))
        expect(postCalls.length).toBeGreaterThan(0)
    })

    it('calls successHandler after successful save', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on save failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/settings\/email-validation/).reply(200, { data: {} })
        global.mockHttp.onPost(/\/email-settings-save/).reply(500)
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('loading is false after mount completes', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('saving is false by default', () => {
        expect(wrapper.vm.saving).toBe(false)
    })
})
