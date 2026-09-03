jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/whatsappValidations', () => ({ whatsappSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import WhatsappSettings from '@/pages/admin/settings/settings/WhatsappSettings.vue'

describe('WhatsappSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/whatsapp-integration-info/).reply(200, {
            data: { app_id: 'aid123', app_secret: 'asecret', config_id: 'cid', verify_token: 'vtoken' },
        })
        globalThis.mockHttp.onPost(/\/whatsapp-integration-save/).reply(200, { data: {} })
        wrapper = mount(WhatsappSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'loader', 'TextField', 'action-button'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches whatsapp settings on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.get[0].url).toMatch(/\/whatsapp-integration-info/)
    })

    it('calls POST whatsapp-integration-save on save', async () => {
        await flushPromises()
        wrapper.vm.save()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('whatsapp-integration-save'))).toBeTruthy()
    })

    it('calls successHandler after successful save', async () => {
        const { successHandler } = require('@/helpers/responseHandler')
        await flushPromises()
        wrapper.vm.save()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })
})
