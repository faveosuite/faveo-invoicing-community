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

    // ── save: validateForm returns false guard ─────────────────────────────
    it('save does not POST when validateForm returns false', async () => {
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        await flushPromises()
        global.mockHttp.reset()
        await wrapper.vm.save()
        await flushPromises()
        expect(global.mockHttp.history.post.length).toBe(0)
    })

    it('save sets saving to false after completion', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(wrapper.vm.saving).toBe(false)
    })

    // ── save with power mode includes accepted_output ──────────────────────
    it('save includes accepted_output in payload when mode is power', async () => {
        await flushPromises()
        wrapper.vm.form.mode = 'power'
        wrapper.vm.selectedBits = [2, 4]
        global.mockHttp.onPost(/\/email-settings-save/).reply(200, { data: {} })
        await wrapper.vm.save()
        await flushPromises()
        const postCall = global.mockHttp.history.post.find(r => r.url.includes('/email-settings-save'))
        const body = JSON.parse(postCall.data)
        expect(body).toHaveProperty('accepted_output')
        expect(body.accepted_output).toBe(6) // 2 | 4
    })

    it('save does NOT include accepted_output in payload when mode is quick', async () => {
        await flushPromises()
        wrapper.vm.form.mode = 'quick'
        global.mockHttp.onPost(/\/email-settings-save/).reply(200, { data: {} })
        await wrapper.vm.save()
        await flushPromises()
        const postCall = global.mockHttp.history.post.find(r => r.url.includes('/email-settings-save'))
        const body = JSON.parse(postCall.data)
        expect(body).not.toHaveProperty('accepted_output')
    })

    // ── mount error path ───────────────────────────────────────────────────
    it('handles GET /settings/email-validation error on mount without throwing', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/settings\/email-validation/).reply(500)
        const w = mount(EmailValidationSettings, {
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        await flushPromises()
        expect(w.vm.loading).toBe(false)
        w.unmount()
    })

    // ── statusOptions and selectedBits populated from response ─────────────
    it('populates statusOptions and selectedBits from API response', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/settings\/email-validation/).reply(200, {
            data: {
                provider: 'reoon',
                api_key: 'k',
                mode: 'power',
                status_options: [{ bit: 1, name: 'Valid' }, { bit: 2, name: 'Invalid' }],
                selected_bits: [1],
            },
        })
        const w = mount(EmailValidationSettings, {
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        await flushPromises()
        expect(w.vm.statusOptions.length).toBe(2)
        expect(w.vm.selectedBits).toEqual([1])
        w.unmount()
    })

    // ── acceptedOutput computed ────────────────────────────────────────────
    it('acceptedOutput computes bitwise OR of selectedBits', async () => {
        await flushPromises()
        wrapper.vm.selectedBits = [1, 4, 8]
        expect(wrapper.vm.acceptedOutput).toBe(13) // 1 | 4 | 8
    })

    it('acceptedOutput is 0 when selectedBits is empty', async () => {
        await flushPromises()
        wrapper.vm.selectedBits = []
        expect(wrapper.vm.acceptedOutput).toBe(0)
    })

    // ── form.provider reactive update ──────────────────────────────────────
    it('form.provider updates when changed programmatically', async () => {
        await flushPromises()
        wrapper.vm.form.provider = 'reoon'
        expect(wrapper.vm.form.provider).toBe('reoon')
    })

    // ── form.mode reactive update ──────────────────────────────────────────
    it('form.mode updates when changed programmatically', async () => {
        await flushPromises()
        wrapper.vm.form.mode = 'power'
        expect(wrapper.vm.form.mode).toBe('power')
    })
})
