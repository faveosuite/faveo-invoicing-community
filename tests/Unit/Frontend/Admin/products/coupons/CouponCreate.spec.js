jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/couponValidations', () => ({ couponSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import CouponCreate from '@/pages/admin/products/coupons/CouponCreate.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

describe('CouponCreate.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/dependency\/promotion-types/).reply(200, { data: { promotion_types: [] } })
        globalThis.mockHttp.onGet(/\/getPromotionCode/).reply(200, { data: 'ABC123' })
        globalThis.mockHttp.onPut(/\/promotionCreate/).reply(200, { data: { message: 'Created' } })
        wrapper = mount(CouponCreate, {
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

    it('renders AppAlert', () => {
        expect(wrapper.find('app-alert-stub').exists()).toBe(true)
    })

    it('fetches promotion types on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/dependency\/promotion-types/.test(r.url))).toBe(true)
    })

    it('calls PUT /promotionCreate on submit', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.put.some(r => /\/promotionCreate/.test(r.url))).toBe(true)
    })

    it('calls successHandler on successful create', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on submit failure', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPut(/\/promotionCreate/).reply(500)
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('generateCode fetches /getPromotionCode and populates form.code', async () => {
        await flushPromises()
        await wrapper.vm.generateCode()
        await flushPromises()
        expect(wrapper.vm.form.code).toBe('ABC123')
    })

    it('does not submit when validateForm returns false', async () => {
        await flushPromises()
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.put.length).toBe(0)
    })
})
