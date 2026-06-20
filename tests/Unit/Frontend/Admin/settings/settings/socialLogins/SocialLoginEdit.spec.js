jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { name: 'Toggle', template: '<button />', props: ['modelValue', 'disabled'], emits: ['update:modelValue'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: { id: '1' }, query: {} }) }))
jest.mock('@/validations/admin/socialLoginValidations', () => ({ socialLoginSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import SocialLoginEdit from '@/pages/admin/settings/settings/socialLogins/SocialLoginEdit.vue'

describe('SocialLoginEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/edit\/SocialLogins\/1/).reply(200, {
            data: { type: 'Google', client_id: 'gid', client_secret: 'gsecret', redirect_url: 'http://example.com/callback', status: true },
        })
        global.mockHttp.onPost(/\/update-social-login/).reply(200, { data: {} })
        wrapper = mount(SocialLoginEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'loader', 'TextField', 'Switch', 'action-button'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches social login data on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.get[0].url).toMatch(/\/edit\/SocialLogins\/1/)
    })

    it('calls POST update-social-login on submit', async () => {
        await flushPromises()
        wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.post.some(r => r.url.includes('update-social-login'))).toBeTruthy()
    })

    it('calls successHandler after successful update', async () => {
        const { successHandler } = require('@/helpers/responseHandler')
        await flushPromises()
        wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })
})
