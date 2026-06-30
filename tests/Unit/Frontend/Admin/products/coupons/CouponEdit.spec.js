jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { id: '0' }, query: {} }),
}))
jest.mock('@/validations/admin/couponValidations', () => ({ couponSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import CouponEdit from '@/pages/admin/products/coupons/CouponEdit.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const couponResponse = {
    code: 'SAVE10',
    type: 'percentage',
    value: '10',
    uses: '100',
    start: '2024-01-01T00:00:00.000Z',
    expiry: '2025-01-01T00:00:00.000Z',
    products: { id: 5, name: 'Product A' },
}

describe('CouponEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/promotion\/0/).reply(200, couponResponse)
        globalThis.mockHttp.onGet(/\/dependency\/promotion-types/).reply(200, { data: { promotion_types: [] } })
        globalThis.mockHttp.onGet(/\/getPromotionCode/).reply(200, { data: 'NEWCODE' })
        globalThis.mockHttp.onPatch(/\/updatePromotion\/0/).reply(200, { data: { message: 'Updated' } })
        wrapper = mount(CouponEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'TextField', 'StaticSelect', 'DynamicSelect',
                    'DatePicker', 'action-button', 'inline-loader', 'loader',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches coupon data on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/promotion\/0/.test(r.url))).toBe(true)
    })

    it('fetches promotion types on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/dependency\/promotion-types/.test(r.url))).toBe(true)
    })

    it('populates form fields after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.form.code).toBe('SAVE10')
        expect(wrapper.vm.form.type).toBe('percentage')
    })

    it('sets loading to false after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('calls errorHandler when fetch fails', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/promotion\/0/).reply(500)
        globalThis.mockHttp.onGet(/\/dependency\/promotion-types/).reply(200, { data: { promotion_types: [] } })
        wrapper = mount(CouponEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'TextField', 'StaticSelect', 'DynamicSelect',
                    'DatePicker', 'action-button', 'inline-loader', 'loader',
                ],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('calls PATCH /updatePromotion/0 on submit', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.patch.some(r => /\/updatePromotion\/0/.test(r.url))).toBe(true)
    })

    it('calls successHandler on successful update', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on submit failure', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPatch(/\/updatePromotion\/0/).reply(500)
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('does not submit when validateForm returns false', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPatch(/\/updatePromotion\/0/).reply(200, { data: {} })
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.patch.length).toBe(0)
    })
})
