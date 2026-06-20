jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import PdfSettings from '@/pages/admin/settings/settings/PdfSettings.vue'

describe('PdfSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/pdf-settings/).reply(200, {
            data: { node_path: '/usr/bin/node', npm_path: '/usr/bin/npm', chrome_path: '/usr/bin/google-chrome' },
        })
        global.mockHttp.onPost(/\/pdf-settings/).reply(200, { data: {} })
        wrapper = mount(PdfSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'loader', 'TextField', 'action-button'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches pdf settings on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.get[0].url).toMatch(/\/pdf-settings/)
    })

    it('calls POST pdf-settings on submit', async () => {
        await flushPromises()
        wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.post.some(r => r.url.includes('pdf-settings'))).toBeTruthy()
    })

    it('calls successHandler after successful submit', async () => {
        const { successHandler } = require('@/helpers/responseHandler')
        await flushPromises()
        wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })
})
