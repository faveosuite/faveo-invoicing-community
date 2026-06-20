jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { id: '1' }, query: {} }),
    RouterLink: { template: '<a><slot /></a>' },
}))
jest.mock('@/core/composables/useDateTime', () => ({
    useDateTime: () => ({ formatDate: (v) => v ?? '' }),
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { errorHandler } from '@/helpers/responseHandler'
import OrderShow from '@/pages/client/orders/OrderShow.vue'

const orderFixture = {
    id: 1,
    number: 'ORD-001',
    order_date: '2025-01-01',
    status: 'Active',
    update_ends_at: '2026-01-01',
    license_ends_at: '2026-01-01',
    serial_key: 'ABC-DEF-GHI',
    is_cloud: false,
    is_terminated: false,
    whatsapp_enabled: false,
    autorenewal_enabled: false,
    is_subscribed: false,
    user: {
        name: 'John Doe',
        email: 'john@example.com',
        mobile: '1234567890',
        address: '123 Main St',
    },
}

describe('OrderShow.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('/get-my-orders').reply(200, { data: orderFixture })

        wrapper = mount(OrderShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-card',
                    'app-alert',
                    'app-modal',
                    'data-table',
                    'action-button',
                    'alert',
                    'router-link',
                    'loader',
                    'renew-modal',
                    'whatsapp-panel',
                    'modal',
                    'select-field',
                    'client-field',
                ],
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

    it('calls GET /get-my-orders on mount', async () => {
        await flushPromises()
        expect(axiosMock.history.get.some(r => r.url.includes('/get-my-orders'))).toBe(true)
    })

    it('sets order data after successful API call', async () => {
        await flushPromises()
        expect(wrapper.vm.order).toEqual(orderFixture)
        expect(wrapper.vm.loading).toBe(false)
    })

    it('sets loading to false when API returns 500', async () => {
        axiosMock.onGet('/get-my-orders').reply(500, { message: 'Server error' })

        const w = mount(OrderShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-card', 'app-alert', 'app-modal', 'data-table',
                    'action-button', 'alert', 'router-link', 'loader',
                    'renew-modal', 'whatsapp-panel', 'modal',
                    'select-field', 'client-field',
                ],
            },
        })
        await flushPromises()
        expect(w.vm.loading).toBe(false)
        w.unmount()
    })

    it('calls errorHandler when API returns 500', async () => {
        axiosMock.onGet('/get-my-orders').reply(500, { message: 'Server error' })

        const w = mount(OrderShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-card', 'app-alert', 'app-modal', 'data-table',
                    'action-button', 'alert', 'router-link', 'loader',
                    'renew-modal', 'whatsapp-panel', 'modal',
                    'select-field', 'client-field',
                ],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })

    it('defaults to the license tab', async () => {
        await flushPromises()
        expect(wrapper.vm.activeTab).toBe('license')
    })

    it('showCloudTab is false when order.is_cloud is false', async () => {
        await flushPromises()
        expect(wrapper.vm.showCloudTab).toBe(false)
    })

    it('showAutoRenewTab is false when autorenewal_enabled is false', async () => {
        await flushPromises()
        expect(wrapper.vm.showAutoRenewTab).toBe(false)
    })

    it('invoiceBadge returns bg-success for paid status', async () => {
        await flushPromises()
        expect(wrapper.vm.invoiceBadge('paid')).toBe('bg-success')
    })

    it('invoiceBadge returns bg-danger for cancelled status', async () => {
        await flushPromises()
        expect(wrapper.vm.invoiceBadge('cancelled')).toBe('bg-danger')
    })

    it('paymentBadge returns badge bg-success for success status', async () => {
        await flushPromises()
        expect(wrapper.vm.paymentBadge('success')).toBe('badge bg-success')
    })

    it('showRenewModal defaults to false', () => {
        expect(wrapper.vm.showRenewModal).toBe(false)
    })

    it('shows alert warning when order data is null', async () => {
        axiosMock.onGet('/get-my-orders').reply(200, { data: null })

        const w = mount(OrderShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-card', 'app-alert', 'app-modal', 'data-table',
                    'action-button', 'alert', 'router-link', 'loader',
                    'renew-modal', 'whatsapp-panel', 'modal',
                    'select-field', 'client-field',
                ],
            },
        })
        await flushPromises()
        expect(w.vm.order).toBeNull()
        w.unmount()
    })
})
