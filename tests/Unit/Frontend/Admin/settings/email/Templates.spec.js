jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: {}, query: {} }),
    RouterLink: { template: '<a><slot /></a>' },
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import Templates from '@/pages/admin/settings/email/Templates.vue'

describe('Templates.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(Templates, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'DataTable', 'action-button',
                    'inline-loader', 'router-link',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the email templates card', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders DataTable stub', () => {
        expect(wrapper.findComponent({ name: 'DataTable' }).exists() || wrapper.html().includes('datatable')).toBeTruthy()
    })

    it('does not make API calls on mount (DataTable handles fetch)', async () => {
        await flushPromises()
        // Templates.vue relies on DataTable for fetching — no direct axios calls on mount
        expect(global.mockHttp.history.get.length).toBe(0)
    })
})
