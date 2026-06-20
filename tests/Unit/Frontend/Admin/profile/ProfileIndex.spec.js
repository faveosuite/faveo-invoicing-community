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
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/profile\/countries/).reply(200, { data: [] })
        global.mockHttp.onGet(/\/profile/).reply(200, {
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
        global.mockHttp.onPost(/\/profile/).reply(200, { data: { message: 'Profile updated' } })
        global.mockHttp.onPatch(/\/password/).reply(200, { data: { message: 'Password changed' } })
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
        const profileCalls = global.mockHttp.history.get.filter(r => /\/profile/.test(r.url) && !/countries/.test(r.url))
        expect(profileCalls.length).toBeGreaterThan(0)
    })

    it('fetches countries on mount', async () => {
        await flushPromises()
        const countryCalls = global.mockHttp.history.get.filter(r => /\/profile\/countries/.test(r.url))
        expect(countryCalls.length).toBeGreaterThan(0)
    })

    it('submits profile update via POST /profile', async () => {
        await flushPromises()
        await wrapper.vm.submitProfile()
        await flushPromises()
        const postCalls = global.mockHttp.history.post.filter(r => /\/profile/.test(r.url))
        expect(postCalls.length).toBeGreaterThan(0)
    })

    it('calls successHandler after successful profile update', async () => {
        await flushPromises()
        await wrapper.vm.submitProfile()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on profile update failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/profile\/countries/).reply(200, { data: [] })
        global.mockHttp.onGet(/\/profile/).reply(200, { data: {} })
        global.mockHttp.onPost(/\/profile/).reply(500)
        await flushPromises()
        await wrapper.vm.submitProfile()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('submits password change via PATCH /password', async () => {
        await flushPromises()
        await wrapper.vm.submitPassword()
        await flushPromises()
        const patchCalls = global.mockHttp.history.patch.filter(r => /\/password/.test(r.url))
        expect(patchCalls.length).toBeGreaterThan(0)
    })

    it('calls successHandler after successful password change', async () => {
        await flushPromises()
        await wrapper.vm.submitPassword()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on password change failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/profile\/countries/).reply(200, { data: [] })
        global.mockHttp.onGet(/\/profile/).reply(200, { data: {} })
        global.mockHttp.onPatch(/\/password/).reply(422, { errors: { current_password: ['Wrong'] } })
        await flushPromises()
        await wrapper.vm.submitPassword()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })
})
