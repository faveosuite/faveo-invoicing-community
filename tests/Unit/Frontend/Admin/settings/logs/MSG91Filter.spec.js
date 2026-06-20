jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { errorHandler } from '@/helpers/responseHandler'
import MSG91Filter from '@/pages/admin/settings/logs/MSG91Filter.vue'

describe('MSG91Filter.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/getMsgFilters/).reply(200, {
            data: {
                statuses: ['Delivered', 'Failed'],
                sources:  ['SMS', 'Email'],
                actions:  ['send', 'retry_1'],
            },
        })

        wrapper = mount(MSG91Filter, {
            props: { show: true, baseUrl: '' },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['TextField', 'SelectField', 'DatePicker', 'action-button'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches filter options on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.get[0].url).toMatch(/\/getMsgFilters/)
    })

    it('is hidden when show prop is false', () => {
        const hiddenWrapper = mount(MSG91Filter, {
            props: { show: false, baseUrl: '' },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['TextField', 'SelectField', 'DatePicker', 'action-button'],
            },
        })
        expect(hiddenWrapper.find('.card').exists()).toBeFalsy()
        hiddenWrapper.unmount()
    })

    it('emits apply event when apply is triggered', async () => {
        await flushPromises()
        await wrapper.vm.apply()
        expect(wrapper.emitted('apply')).toBeTruthy()
    })

    it('emits reset event when reset is triggered', async () => {
        await flushPromises()
        await wrapper.vm.reset()
        expect(wrapper.emitted('reset')).toBeTruthy()
    })

    it('includes form fields in apply payload when populated', async () => {
        await flushPromises()
        wrapper.vm.form.requestId = 'REQ123'
        wrapper.vm.form.email = 'test@example.com'
        await wrapper.vm.apply()
        const emitted = wrapper.emitted('apply')
        expect(emitted).toBeTruthy()
        const payload = emitted[0][0]
        expect(payload.request_id).toBe('REQ123')
        expect(payload.email).toBe('test@example.com')
    })

    it('calls errorHandler on fetch failure', async () => {
        global.mockHttp.onGet(/\/getMsgFilters/).reply(500)
        const w = mount(MSG91Filter, {
            props: { show: true, baseUrl: '' },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['TextField', 'SelectField', 'DatePicker', 'action-button'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })
})
