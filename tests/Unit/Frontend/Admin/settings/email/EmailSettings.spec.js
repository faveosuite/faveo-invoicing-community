jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/emailValidations', () => ({
    buildEmailSettingsSchema: jest.fn(() => ({})),
    templateEditSchema: {},
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import EmailSettings from '@/pages/admin/settings/email/EmailSettings.vue'

describe('EmailSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/settings\/email/).reply(200, {
            data: {
                driver: 'smtp', email: 'test@example.com', from_name: 'Test',
                host: 'smtp.example.com', port: '587', encryption: 'tls',
                secret: '', domain: '', key: '', region: '',
            },
        })
        globalThis.mockHttp.onPatch(/\/settings\/email/).reply(200, { message: 'Saved' })

        wrapper = mount(EmailSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'TextField', 'DynamicSelect', 'action-button',
                    'loader', 'inline-loader',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches email settings on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.get[0].url).toMatch(/\/settings\/email/)
    })

    it('handles 500 error on fetch', async () => {
        globalThis.mockHttp.onGet(/\/settings\/email/).reply(500)
        const w = mount(EmailSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'TextField', 'DynamicSelect', 'action-button', 'loader', 'inline-loader'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })

    it('submits form via PATCH to /settings/email', async () => {
        await flushPromises()
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(true)

        await wrapper.vm.submit()
        await flushPromises()

        expect(globalThis.mockHttp.history.patch.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.patch[0].url).toMatch(/\/settings\/email/)
    })

    it('calls successHandler after successful submit', async () => {
        await flushPromises()
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(true)

        await wrapper.vm.submit()
        await flushPromises()

        expect(successHandler).toHaveBeenCalled()
    })
})
