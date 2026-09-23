jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { name: 'Toggle', template: '<button />', props: ['modelValue', 'disabled'], emits: ['update:modelValue'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import SystemManagers from '@/pages/admin/settings/settings/SystemManagers.vue'

describe('SystemManagers.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/system-managers/).reply(200, {
            data: {
                account_managers: [{ id: 1, name: 'Admin One' }],
                sales_managers: [{ id: 2, name: 'Admin Two' }],
                account_managers_auto_assign: true,
                sales_managers_auto_assign: false,
            },
        })
        globalThis.mockHttp.onPost(/\/updateSystemManager/).reply(200, { data: {} })
        wrapper = mount(SystemManagers, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'loader', 'Switch', 'DynamicSelect', 'action-button'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches system managers data on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.get[0].url).toMatch(/\/system-managers/)
    })

    it('calls POST updateSystemManager on submit', async () => {
        await flushPromises()
        wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('updateSystemManager'))).toBeTruthy()
    })

    it('calls successHandler after successful submit', async () => {
        const { successHandler } = require('@/helpers/responseHandler')
        await flushPromises()
        wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })
})
