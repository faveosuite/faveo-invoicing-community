jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('@/validations/admin/mobileValidationProviderValidations', () => ({
    buildMobileValidationSchema: jest.fn(() => ({})),
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MobileValidationSettings from '@/pages/admin/settings/api/MobileValidationSettings.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'DynamicSelect', 'AppModal', 'ImageUpload', 'PhoneField',
    'RecaptchaProvider', 'RecaptchaCheckbox', 'RecaptchaV2Invisible', 'RecaptchaV3',
    'ZohoCard', 'spinner-loader',
]

describe('MobileValidationSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/settings\/mobile-validation/).reply(200, {
            data: { provider: 'vonage', api_key: 'key123', api_secret: 'secret', mode: 'basic' },
        })
        globalThis.mockHttp.onPost(/\/mobile-settings-save/).reply(200, { data: { message: 'Saved' } })
        wrapper = mount(MobileValidationSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches settings on mount via GET /settings/mobile-validation', async () => {
        await flushPromises()
        const getCalls = globalThis.mockHttp.history.get.filter(r => /\/settings\/mobile-validation/.test(r.url))
        expect(getCalls.length).toBeGreaterThan(0)
    })

    it('populates form from API response', async () => {
        await flushPromises()
        expect(wrapper.vm.form.provider).toBe('vonage')
        expect(wrapper.vm.form.apikey).toBe('key123')
    })

    it('sends POST /mobile-settings-save on save()', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        const postCalls = globalThis.mockHttp.history.post.filter(r => /\/mobile-settings-save/.test(r.url))
        expect(postCalls.length).toBeGreaterThan(0)
    })

    it('calls successHandler after successful save', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on save failure', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/settings\/mobile-validation/).reply(200, { data: {} })
        globalThis.mockHttp.onPost(/\/mobile-settings-save/).reply(500)
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
