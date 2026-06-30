jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
const mockPush = jest.fn()
jest.mock('vue-router', () => ({ useRouter: () => ({ push: mockPush }), useRoute: () => ({ params: {}, query: {} }) }))
jest.mock('@/validations/admin/planValidations', () => ({ planSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import PlanCreate from '@/pages/admin/products/plans/PlanCreate'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const PERIODS_RESPONSE = {
    data: { periods: [{ id: 30, name: '1 Month' }, { id: 365, name: '1 Year' }] },
}

const CURRENCIES_RESPONSE = {
    data: { currencies: [{ id: 1, name: 'USD' }, { id: 2, name: 'EUR' }] },
}

describe('PlanCreate.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.useFakeTimers()

        globalThis.mockHttp.onGet(/\/dependency\/periods/).reply(200, PERIODS_RESPONSE)
        globalThis.mockHttp.onGet(/\/dependency\/currencies/).reply(200, CURRENCIES_RESPONSE)
        globalThis.mockHttp.onPut(/\/plans/).reply(200, { data: { message: 'Plan created' } })

        wrapper = mount(PlanCreate, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
                    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
                    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
                    'Tooltip', 'ImageField', 'SelectField', 'VersionTableActions',
                    'ProductPluginMapping', 'spinner-loader',
                ],
            },
        })
    })

    afterEach(() => {
        globalThis.mockHttp.reset()
        jest.clearAllMocks()
        jest.useRealTimers()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the form', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
    })

    it('fetches periods and currencies on mount', async () => {
        await flushPromises()
        const urls = globalThis.mockHttp.history.get.map(r => r.url)
        expect(urls.some(u => u.includes('/dependency/periods'))).toBe(true)
        expect(urls.some(u => u.includes('/dependency/currencies'))).toBe(true)
    })

    it('submits PUT /plans on valid form', async () => {
        await flushPromises()
        wrapper.vm.form.name = 'Basic Plan'
        wrapper.vm.form.product = 1
        wrapper.vm.form.prices = [{ currency: 1, add_price: '10', offer_price: '', renew_price: '10' }]
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.put.some(r => /\/plans/.test(r.url))).toBe(true)
    })

    it('calls successHandler on success', async () => {
        await flushPromises()
        wrapper.vm.form.name = 'Basic Plan'
        wrapper.vm.form.product = 1
        wrapper.vm.form.prices = [{ currency: 1, add_price: '10', offer_price: '', renew_price: '10' }]
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on 500 error', async () => {
        globalThis.mockHttp.onPut(/\/plans/).reply(500)
        await flushPromises()
        wrapper.vm.form.name = 'Basic Plan'
        wrapper.vm.form.product = 1
        wrapper.vm.form.prices = [{ currency: 1, add_price: '10', offer_price: '', renew_price: '10' }]
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('redirects to /products/plans after successful submit', async () => {
        await flushPromises()
        wrapper.vm.form.name = 'Basic Plan'
        wrapper.vm.form.product = 1
        wrapper.vm.form.prices = [{ currency: 1, add_price: '10', offer_price: '', renew_price: '10' }]
        await wrapper.vm.submit()
        await flushPromises()
        // The component uses setTimeout(..., 2000) before push; advance timers
        jest.runAllTimers()
        expect(mockPush).toHaveBeenCalledWith('/products/plans')
    })
})
