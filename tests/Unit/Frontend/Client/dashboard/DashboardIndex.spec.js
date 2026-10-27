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
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { errorHandler } from '@/helpers/responseHandler'
import DashboardIndex from '@/pages/client/dashboard/DashboardIndex.vue'

const dashboardResponse = {
    data: {
        pending_invoices_count: 3,
        total_orders_count: 10,
        order_renewals_count: 2,
    },
}

describe('DashboardIndex.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('/client-dashboard-details').reply(200, dashboardResponse)

        wrapper = mount(DashboardIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'router-link'],
            },
        })
    })

    afterEach(() => {
        axiosMock.restore()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('hides loader after fetch resolves', async () => {
        await flushPromises()
        expect(wrapper.find('loader-stub').exists()).toBeFalsy()
    })

    it('fetches GET /client-dashboard-details on mount', async () => {
        await flushPromises()
        expect(axiosMock.history.get.some(r => r.url.includes('/client-dashboard-details'))).toBe(true)
    })

    it('renders three stat cards after data loads', async () => {
        await flushPromises()
        const cards = wrapper.findAll('.card')
        expect(cards.length).toBeGreaterThanOrEqual(3)
    })

    it('displays pending_invoices_count after load', async () => {
        await flushPromises()
        expect(wrapper.text()).toContain('3')
    })

    it('displays total_orders_count after load', async () => {
        await flushPromises()
        expect(wrapper.text()).toContain('10')
    })

    it('displays order_renewals_count after load', async () => {
        await flushPromises()
        expect(wrapper.text()).toContain('2')
    })

    it('shows zero counts when API returns empty data object', async () => {
        axiosMock.onGet('/client-dashboard-details').reply(200, { data: {} })

        wrapper = mount(DashboardIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'router-link'],
            },
        })

        await flushPromises()
        // All three ?? 0 fallbacks should render 0
        const counts = wrapper.findAll('.display-6')
        counts.forEach(span => expect(span.text()).toBe('0'))
    })

    it('calls errorHandler when API returns 500', async () => {
        axiosMock.onGet('/client-dashboard-details').reply(500, { message: 'Server error' })

        wrapper = mount(DashboardIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'router-link'],
            },
        })

        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

})
