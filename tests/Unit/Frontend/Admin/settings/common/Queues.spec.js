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
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import Queues from '@/pages/admin/settings/common/Queues.vue'

describe('Queues.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(Queues, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'DataTable', 'SelectField', 'action-button',
                    'inline-loader', 'loader', 'router-link',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the queue card', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('handles verify-php-path POST on copyCommand', async () => {
        global.mockHttp.onPost(/\/verify-php-path/).reply(200, { message: 'OK' })
        Object.assign(navigator, {
            clipboard: { writeText: jest.fn().mockResolvedValue(undefined) },
        })
        await wrapper.vm.copyCommand()
        await flushPromises()
        expect(global.mockHttp.history.post.length).toBeGreaterThan(0)
    })

    it('handles activate POST to correct endpoint', async () => {
        global.mockHttp.onPost(/\/queue\/5\/activate/).reply(200, { message: 'Activated' })
        await wrapper.vm.activate(5)
        await flushPromises()
        expect(global.mockHttp.history.post.some(r => r.url.includes('/queue/5/activate'))).toBe(true)
        expect(successHandler).toHaveBeenCalled()
    })

    it('handles 500 error on activate', async () => {
        global.mockHttp.onPost(/\/queue\/99\/activate/).reply(500)
        await wrapper.vm.activate(99)
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })
})
