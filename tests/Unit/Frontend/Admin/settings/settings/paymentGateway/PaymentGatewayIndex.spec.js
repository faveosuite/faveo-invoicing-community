jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
const mockPush = jest.fn()
jest.mock('vue-router', () => ({ useRouter: () => ({ push: mockPush }), useRoute: () => ({ params: {}, query: {} }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import PaymentGatewayIndex from '@/pages/admin/settings/settings/paymentGateway/PaymentGatewayIndex.vue'

describe('PaymentGatewayIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/payment-gateway-list/).reply(200, {
            data: [
                { name: 'stripe', status: true, description: 'Stripe gateway' },
                { name: 'razorpay', status: false, description: 'Razorpay gateway' },
            ],
        })
        wrapper = mount(PaymentGatewayIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'loader', 'GatewayCard'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches payment gateway list on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.get[0].url).toMatch(/\/payment-gateway-list/)
    })

    it('calls toggle status endpoint when toggling a plugin', async () => {
        global.mockHttp.onPost(/\/updatePaymentStatus/).reply(200, { data: {} })
        await flushPromises()
        wrapper.vm.toggleStatus({ name: 'stripe', status: true })
        await flushPromises()
        expect(global.mockHttp.history.post.some(r => r.url.includes('updatePaymentStatus'))).toBeTruthy()
    })

    it('navigates to settings when goToSettings is called', async () => {
        await flushPromises()
        wrapper.vm.goToSettings({ name: 'stripe' })
        expect(mockPush).toHaveBeenCalledWith('/settings/payment-gateway/stripe/edit')
    })
})
