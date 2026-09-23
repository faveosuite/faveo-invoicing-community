jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('@/validations/admin/recaptchaValidations', () => ({
    buildRecaptchaSchema: jest.fn(() => ({})),
}))
jest.mock('@recaptcha', () => ({
    RecaptchaProvider: { template: '<div><slot /></div>' },
    RecaptchaCheckbox: { template: '<div />' },
    RecaptchaV2Invisible: { template: '<div />' },
    RecaptchaV3: { template: '<div />' },
}), { virtual: true })

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import Recaptcha from '@/pages/admin/settings/api/Recaptcha.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'DynamicSelect', 'AppModal', 'ImageUpload', 'PhoneField',
    'RecaptchaProvider', 'RecaptchaCheckbox', 'RecaptchaV2Invisible', 'RecaptchaV3',
    'ZohoCard', 'spinner-loader',
]

describe('Recaptcha.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/recaptcha-settings/).reply(200, {
            data: {
                captcha_version: 'v3_invisible',
                failover_action: 'none',
                v3_site_key: '',
                v3_secret_key: '',
                score_threshold: 0.5,
                v2_site_key: '',
                v2_secret_key: '',
                theme: 'light',
                size: 'normal',
                badge_position: 'bottomright',
            },
        })
        globalThis.mockHttp.onPatch(/\/recaptcha-settings/).reply(200, { data: { message: 'Saved' } })
        wrapper = mount(Recaptcha, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches settings on mount via GET /recaptcha-settings', async () => {
        await flushPromises()
        const getCalls = globalThis.mockHttp.history.get.filter(r => /\/recaptcha-settings/.test(r.url))
        expect(getCalls.length).toBeGreaterThan(0)
    })

    it('populates form from API response', async () => {
        await flushPromises()
        expect(wrapper.vm.form.captcha_version).toBe('v3_invisible')
        expect(wrapper.vm.form.theme).toBe('light')
    })

    it('sends PATCH /recaptcha-settings on save()', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        const patchCalls = globalThis.mockHttp.history.patch.filter(r => /\/recaptcha-settings/.test(r.url))
        expect(patchCalls.length).toBeGreaterThan(0)
    })

    it('calls successHandler after successful save', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on save failure', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/recaptcha-settings/).reply(200, { data: {} })
        globalThis.mockHttp.onPatch(/\/recaptcha-settings/).reply(500)
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
