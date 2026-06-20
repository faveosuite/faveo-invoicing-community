jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { errorHandler } from '@/helpers/responseHandler'
import ActivityFilter from '@/pages/admin/settings/logs/ActivityFilter.vue'

describe('ActivityFilter.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/get-activity-filters/).reply(200, {
            data: {
                modules: ['Users', 'Orders'],
                users: [{ id: 1, name: 'Admin' }],
            },
        })

        wrapper = mount(ActivityFilter, {
            props: { show: true, baseUrl: '' },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['SelectField', 'DatePicker', 'action-button'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches filter options on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.get[0].url).toMatch(/\/get-activity-filters/)
    })

    it('is hidden when show prop is false', () => {
        const hiddenWrapper = mount(ActivityFilter, {
            props: { show: false, baseUrl: '' },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['SelectField', 'DatePicker', 'action-button'],
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

    it('calls errorHandler on fetch failure', async () => {
        global.mockHttp.onGet(/\/get-activity-filters/).reply(500)
        const w = mount(ActivityFilter, {
            props: { show: true, baseUrl: '' },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['SelectField', 'DatePicker', 'action-button'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })
})
