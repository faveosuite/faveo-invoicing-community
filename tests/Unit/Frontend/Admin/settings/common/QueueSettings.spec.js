jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: { id: '1' }, query: {} }) }))
jest.mock('@/validations/admin/queueDriverValidations.js', () => ({ queueDriverSchemas: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { errorHandler } from '@/helpers/responseHandler'
import QueueSettings from '@/pages/admin/settings/common/QueueSettings.vue'

describe('QueueSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/queue\/1\/form/).reply(200, {
            data: {
                fields: [
                    { name: 'host', label: 'Host', required: true, type: 'text', value: 'localhost' },
                ],
                driver: 'database',
            },
        })
        global.mockHttp.onPost(/\/queue\/1/).reply(200, { message: 'Saved' })

        wrapper = mount(QueueSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'TextField', 'action-button', 'loader', 'inline-loader'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches queue form data on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.get[0].url).toMatch(/\/queue\/1\/form/)
    })

    it('handles 500 error on fetch', async () => {
        global.mockHttp.onGet(/\/queue\/1\/form/).reply(500)
        const w = mount(QueueSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'TextField', 'action-button', 'loader', 'inline-loader'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })

    it('submits form via POST to correct endpoint', async () => {
        await flushPromises()
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(true)

        await wrapper.vm.save()
        await flushPromises()

        expect(global.mockHttp.history.post.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.post[0].url).toMatch(/\/queue\/1/)
    })
})
