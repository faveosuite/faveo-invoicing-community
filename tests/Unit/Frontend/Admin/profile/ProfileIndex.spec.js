jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('@/validations/admin/profileValidations', () => ({
    profileSchema: {},
    passwordChangeSchema: {},
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ProfileIndex from '@/pages/admin/profile/ProfileIndex.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'SelectField', 'AppModal', 'ImageUpload', 'PhoneField',
    'RecaptchaProvider', 'RecaptchaCheckbox', 'RecaptchaV2Invisible', 'RecaptchaV3',
    'ZohoCard', 'spinner-loader',
]

describe('ProfileIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/profile\/countries/).reply(200, { data: [] })
        globalThis.mockHttp.onGet(/\/profile/).reply(200, {
            data: {
                name: 'Test User',
                email: 'test@example.com',
                mobile: '',
                country_code: 'US',
                timezone: 'UTC',
                language: 'en',
                two_factor_enabled: false,
            },
        })
        globalThis.mockHttp.onPost(/\/profile/).reply(200, { data: { message: 'Profile updated' } })
        globalThis.mockHttp.onPatch(/\/password/).reply(200, { data: { message: 'Password changed' } })
        wrapper = mount(ProfileIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches profile data on mount', async () => {
        await flushPromises()
        const profileCalls = globalThis.mockHttp.history.get.filter(r => /\/profile/.test(r.url) && !/countries/.test(r.url))
        expect(profileCalls.length).toBeGreaterThan(0)
    })

    it('fetches countries on mount', async () => {
        await flushPromises()
        const countryCalls = globalThis.mockHttp.history.get.filter(r => /\/profile\/countries/.test(r.url))
        expect(countryCalls.length).toBeGreaterThan(0)
    })

    it('submits profile update via POST /profile', async () => {
        await flushPromises()
        await wrapper.vm.submitProfile()
        await flushPromises()
        const postCalls = globalThis.mockHttp.history.post.filter(r => /\/profile/.test(r.url))
        expect(postCalls.length).toBeGreaterThan(0)
    })

    it('calls successHandler after successful profile update', async () => {
        await flushPromises()
        await wrapper.vm.submitProfile()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on profile update failure', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/profile\/countries/).reply(200, { data: [] })
        globalThis.mockHttp.onGet(/\/profile/).reply(200, { data: {} })
        globalThis.mockHttp.onPost(/\/profile/).reply(500)
        await flushPromises()
        await wrapper.vm.submitProfile()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('submits password change via PATCH /password', async () => {
        await flushPromises()
        await wrapper.vm.submitPassword()
        await flushPromises()
        const patchCalls = globalThis.mockHttp.history.patch.filter(r => /\/password/.test(r.url))
        expect(patchCalls.length).toBeGreaterThan(0)
    })

    it('calls successHandler after successful password change', async () => {
        await flushPromises()
        await wrapper.vm.submitPassword()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on password change failure', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/profile\/countries/).reply(200, { data: [] })
        globalThis.mockHttp.onGet(/\/profile/).reply(200, { data: {} })
        globalThis.mockHttp.onPatch(/\/password/).reply(422, { errors: { current_password: ['Wrong'] } })
        await flushPromises()
        await wrapper.vm.submitPassword()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('onChange sets form field value by name', async () => {
        await flushPromises()
        wrapper.vm.onChange('John Doe', 'first_name')
        expect(wrapper.vm.form.first_name).toBe('John Doe')
    })

    it('onPwChange sets password form field value', async () => {
        await flushPromises()
        wrapper.vm.onPwChange('secret123', 'current_password')
        expect(wrapper.vm.pwForm.current_password).toBe('secret123')
    })

    it('onTimezoneChange sets form.timezone_id from val.id', async () => {
        await flushPromises()
        wrapper.vm.onTimezoneChange({ id: 'America/New_York' })
        expect(wrapper.vm.form.timezone_id).toBe('America/New_York')
    })

    it('onTimezoneChange sets null when val is null', async () => {
        await flushPromises()
        wrapper.vm.onTimezoneChange(null)
        expect(wrapper.vm.form.timezone_id).toBeNull()
    })

    it('onCountryChange fetches states for a valid country', async () => {
        globalThis.mockHttp.onGet(/\/profile\/states\/US/).reply(200, {
            data: { states: [{ iso2: 'CA', state_subdivision_name: 'California' }] }
        })
        await flushPromises()
        await wrapper.vm.onCountryChange({ id: 'US' })
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => r.url.includes('/profile/states/US'))).toBe(true)
    })

    it('onCountryChange clears form.country and states when val is null', async () => {
        await flushPromises()
        await wrapper.vm.onCountryChange(null)
        expect(wrapper.vm.form.country).toBe('')
        expect(wrapper.vm.states).toEqual([])
    })

    it('openEnableModal sets loading and fetches 2FA data on success', async () => {
        globalThis.mockHttp.onGet(/\/show\/verify-password/).reply(200, { data: {} })
        globalThis.mockHttp.onPost(/\/2fa-recovery-code/).reply(200, { data: { recovery_codes: ['code1'] } })
        await flushPromises()
        await wrapper.vm.openEnableModal()
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => r.url.includes('verify-password'))).toBe(true)
    })

    it('openEnableModal handles error', async () => {
        globalThis.mockHttp.onGet(/\/show\/verify-password/).reply(500)
        await flushPromises()
        await expect(wrapper.vm.openEnableModal()).resolves.not.toThrow()
    })

    it('closeEnableModal resets modal state', async () => {
        await flushPromises()
        wrapper.vm.showEnableModal = true
        wrapper.vm.closeEnableModal()
        expect(wrapper.vm.showEnableModal).toBe(false)
        expect(wrapper.vm.twoFaStep).toBe('password')
    })

    it('loadStates handles error gracefully', async () => {
        globalThis.mockHttp.onGet(/\/profile\/states\/IN/).reply(500)
        await flushPromises()
        await expect(wrapper.vm.loadStates('IN')).resolves.not.toThrow()
    })
})
