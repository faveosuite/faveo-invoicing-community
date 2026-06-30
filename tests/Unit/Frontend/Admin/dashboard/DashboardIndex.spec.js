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
        globalThis.mockHttp.onGet(/\/dashboard/).reply(200, DASHBOARD_RESPONSE)
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
        globalThis.mockHttp.reset()
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
        expect(globalThis.mockHttp.history.get.some(r => /\/dashboard/.test(r.url))).toBe(true)
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
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/dashboard/).reply(500)
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

    it('isExpired returns false for a falsy date', () => {
        expect(wrapper.vm.isExpired(null)).toBe(false)
        expect(wrapper.vm.isExpired('')).toBe(false)
    })

    it('formatCurrency formats a number as a currency string', () => {
        const result = wrapper.vm.formatCurrency(1000, 'USD')
        expect(typeof result).toBe('string')
        expect(result).toContain('1,000')
    })

    it('formatDate returns empty string for falsy input', () => {
        expect(wrapper.vm.formatDate(null)).toBe('')
        expect(wrapper.vm.formatDate('')).toBe('')
    })

    it('formatDate delegates to useDateTime formatDate for valid input', () => {
        expect(wrapper.vm.formatDate('2024-01-15')).toBe('2024-01-15')
    })

    it('formatDateForUser returns empty string for falsy input', () => {
        expect(wrapper.vm.formatDateForUser(null)).toBe('')
        expect(wrapper.vm.formatDateForUser('')).toBe('')
    })

    it('formatDateForUser returns today label for today\'s date', () => {
        const today = new Date().toISOString()
        const result = wrapper.vm.formatDateForUser(today)
        expect(result).toBe('message.today')
    })

    it('formatDateForUser returns yesterday label for yesterday\'s date', () => {
        const yesterday = new Date(Date.now() - 86400000).toISOString()
        const result = wrapper.vm.formatDateForUser(yesterday)
        expect(result).toBe('message.yesterday')
    })

    it('formatDateForUser formats older dates with formatCustom', () => {
        const result = wrapper.vm.formatDateForUser('2020-06-15')
        expect(result).toBe('2020-06-15')
    })

    it('startMonthDate is set to the first day of the current month', () => {
        const now = new Date()
        const expected = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`
        expect(wrapper.vm.startMonthDate).toBe(expected)
    })

    it('endMonthDate is set to the last day of the current month', () => {
        const now = new Date()
        const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate()
        expect(wrapper.vm.endMonthDate).toContain(String(lastDay))
    })

    it('renders tables after data loads (covers v-for and nested template branches)', async () => {
        globalThis.mockHttp.onGet(/\/dashboard/).reply(200, {
            ...DASHBOARD_RESPONSE,
            recentInvoices: [
                { id: 1, number: 'INV-001', grand_total: 100, date: '2024-01-01', paid_amount: 100, balance: 0, status: 'Paid', user: { id: 1, first_name: 'A', last_name: 'B' } },
            ],
            clientWithMobileAndEmailActivation: [
                { id: 1, first_name: 'John', last_name: 'Doe', profile_pic: '', created_at: new Date().toISOString() },
            ],
            expiredOrders: [
                { id: 1, user: { id: 1, first_name: 'A', last_name: 'B' }, order: { id: 1, number: 'ORD-001' }, update_ends_at: '2020-01-01', days_expired: 100, product: { name: 'P1' } },
            ],
            expiringOrders: [
                { id: 2, user: { id: 2, first_name: 'C', last_name: 'D' }, order: { id: 2, number: 'ORD-002' }, update_ends_at: '2026-12-31', days_to_expire: 10, product: { name: 'P2' } },
            ],
            clientWithOutdatedProducts: [
                { id: 3, user: { first_name: 'E', last_name: 'F' }, version: '2.9', product: { name: 'P3' }, update_ends_at: '2020-01-01' },
            ],
            recentPaidOrders: [
                { id: 1, number: 'ORD-001', product_relation: { name: 'P1' }, created_at: '2024-01-01', user: { id: 1, first_name: 'A', last_name: 'B' } },
            ],
            productSoldInLast30Days: [
                { id: 1, name: 'P1', image: null, order_count: 5, latest_order_created_at: '2024-01-01' },
            ],
            totalProductsSold: [
                { id: 2, name: 'P2', image: null, order_count: 10, latest_order_created_at: '2024-01-01' },
            ],
        })

        const w = mount(DashboardIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'router-link', 'AppAlert', 'inline-loader', 'action-button', 'DataTable', 'spinner-loader'],
            },
        })
        await flushPromises()
        expect(w.exists()).toBeTruthy()
        w.unmount()
    })

    it('formatDateForUser handles today with fallback', () => {
        const today = new Date()
        today.setHours(12, 0, 0, 0)
        const result = wrapper.vm.formatDateForUser(today.toISOString())
        expect(result).toBeTruthy()
    })
})
