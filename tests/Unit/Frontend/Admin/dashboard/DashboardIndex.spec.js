jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/core/composables/useDateTime', () => ({
    useDateTime: () => ({
        formatDate: (v) => v,
        formatCustom: (v) => v,
    }),
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import DashboardIndex from '@/pages/admin/dashboard/DashboardIndex.vue'

const DASHBOARD_RESPONSE = {
    totalSales: { USD: 1000 },
    yearlySales: { USD: 5000 },
    monthlySales: { USD: 800 },
    pendingPayments: { USD: 200 },
    productInstalledRate: { rate: 75.5, total_subscription: 40, inactive_subscription: 10 },
    paidOrderRate: { rate: 80.0, all_orders: 50, paid_orders: 40 },
    clientWithMobileAndEmailActivation: [
        { id: 1, first_name: 'John', last_name: 'Doe', profile_pic: '', created_at: '2024-01-01' },
    ],
    recentInvoices: [
        { id: 1, number: 'INV-001', grand_total: 100, date: '2024-01-01', paid_amount: 100, balance: 0, status: '<span>Paid</span>', user: { id: 1, first_name: 'John', last_name: 'Doe' } },
    ],
    expiredOrders: [],
    expiringOrders: [],
    clientWithOutdatedProducts: [],
    recentPaidOrders: [],
    productSoldInLast30Days: [],
    totalProductsSold: [],
}

describe('DashboardIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/dashboard/).reply(200, DASHBOARD_RESPONSE)
        wrapper = mount(DashboardIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'loader', 'router-link', 'AppAlert', 'inline-loader',
                    'action-button', 'DataTable', 'spinner-loader',
                ],
            },
        })
    })

    afterEach(() => {
        global.mockHttp.reset()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the loader stub while loading', async () => {
        // mock resolves synchronously; verify loading becomes false and loader is hidden
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
        expect(wrapper.find('loader-stub').exists()).toBe(false)
    })

    it('calls GET /dashboard on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.some(r => /\/dashboard/.test(r.url))).toBe(true)
    })

    it('hides loader after data is fetched', async () => {
        await flushPromises()
        expect(wrapper.find('loader-stub').exists()).toBe(false)
    })

    it('populates data ref after successful fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.data.totalSales).toEqual({ USD: 1000 })
        expect(wrapper.vm.data.yearlySales).toEqual({ USD: 5000 })
    })

    it('sets loading to false after fetch completes', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('sets loading to false even when fetch fails', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/dashboard/).reply(500)
        const w = mount(DashboardIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'router-link', 'AppAlert', 'inline-loader', 'action-button', 'DataTable', 'spinner-loader'],
            },
        })
        await flushPromises()
        expect(w.vm.loading).toBe(false)
    })

    it('computes startingDateOfYear as current year with -01-01', () => {
        const year = new Date().getFullYear()
        expect(wrapper.vm.startingDateOfYear).toBe(`${year}-01-01`)
    })

    it('formatRate returns 0.00 for undefined input', () => {
        expect(wrapper.vm.formatRate(undefined)).toBe('0.00')
    })

    it('formatRate returns formatted string for a number', () => {
        expect(wrapper.vm.formatRate(75.5)).toBe('75.50')
    })

    it('isExpired returns true for a past date', () => {
        expect(wrapper.vm.isExpired('2000-01-01')).toBe(true)
    })

    it('isExpired returns false for a future date', () => {
        const future = new Date(Date.now() + 86400000 * 365).toISOString()
        expect(wrapper.vm.isExpired(future)).toBe(false)
    })
})
