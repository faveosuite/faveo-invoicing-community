jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import CouponIndex from '@/pages/admin/products/coupons/CouponIndex.vue'

describe('CouponIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/promotions/).reply(200, { data: [] })
        global.mockHttp.onDelete(/\/promotions/).reply(200, { data: { message: 'Deleted' } })
        wrapper = mount(CouponIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button',
                    'DeleteModal', 'router-link',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders a DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('renders AppAlert', () => {
        expect(wrapper.find('app-alert-stub').exists()).toBe(true)
    })

    it('does not show DeleteModal by default', () => {
        expect(wrapper.find('delete-modal-stub').exists()).toBe(false)
    })

    it('confirmBulkDelete sets pendingBulkDelete when coupons are selected', () => {
        wrapper.vm.selectedCoupons = [1, 2]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).not.toBeNull()
        expect(wrapper.vm.pendingBulkDelete).toHaveProperty('select')
    })

    it('confirmBulkDelete does nothing when no coupons are selected', () => {
        wrapper.vm.selectedCoupons = []
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })

    it('pendingBulkDelete contains selected coupon ids', () => {
        wrapper.vm.selectedCoupons = [3, 7]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete.select).toEqual([3, 7])
    })

    it('shows DeleteModal when pendingBulkDelete is set', async () => {
        wrapper.vm.selectedCoupons = [1]
        wrapper.vm.confirmBulkDelete()
        await wrapper.vm.$nextTick()
        expect(wrapper.find('delete-modal-stub').exists()).toBe(true)
    })
})
