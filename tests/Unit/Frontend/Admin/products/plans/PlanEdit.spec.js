jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: { id: 0 }, query: {} }) }))
jest.mock('@/validations/admin/planValidations', () => ({ planSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import PlanEdit from '@/pages/admin/products/plans/PlanEdit'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const PERIODS_RESPONSE = {
    data: { periods: [{ id: 30, name: '1 Month' }, { id: 365, name: '1 Year' }] },
}

const CURRENCIES_RESPONSE = {
    data: { currencies: [{ id: 1, name: 'USD' }, { id: 2, name: 'EUR' }] },
}

const PLAN_RESPONSE = {
    data: {
        id: 0,
        name: 'Starter Plan',
        product: 1,
        days: 30,
        status: 1,
        no_of_agents: 5,
        product_quantity: 1,
        product_relation: { name: 'My Product' },
        plan_price: [
            {
                currency: 1,
                add_price: '19.99',
                offer_price: '10',
                renew_price: '19.99',
                price_description: 'Monthly billing',
            },
        ],
    },
}

describe('PlanEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/dependency\/periods/).reply(200, PERIODS_RESPONSE)
        global.mockHttp.onGet(/\/dependency\/currencies/).reply(200, CURRENCIES_RESPONSE)
        global.mockHttp.onGet(/\/plan\/0/).reply(200, PLAN_RESPONSE)
        global.mockHttp.onPatch(/\/plan\/0/).reply(200, { data: { message: 'Plan updated' } })

        wrapper = mount(PlanEdit, {
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
        global.mockHttp.reset()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the form', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
    })

    it('fetches periods, currencies, and plan data on mount', async () => {
        await flushPromises()
        const urls = global.mockHttp.history.get.map(r => r.url)
        expect(urls.some(u => u.includes('/dependency/periods'))).toBe(true)
        expect(urls.some(u => u.includes('/dependency/currencies'))).toBe(true)
        expect(urls.some(u => u.includes('/plan/0'))).toBe(true)
    })

    it('populates form with fetched data', async () => {
        await flushPromises()
        expect(wrapper.vm.form.name).toBe('Starter Plan')
        expect(wrapper.vm.form.product).toBe(1)
        expect(wrapper.vm.form.days).toBe(30)
        expect(wrapper.vm.form.status).toBe(1)
    })

    it('submits PATCH /plan/0 on valid form', async () => {
        await flushPromises()
        wrapper.vm.form.prices = [{ currency: 1, add_price: '19.99', offer_price: '10', renew_price: '19.99' }]
        await wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.patch.some(r => /\/plan\/0/.test(r.url))).toBe(true)
    })

    it('calls successHandler on success', async () => {
        await flushPromises()
        wrapper.vm.form.prices = [{ currency: 1, add_price: '19.99', offer_price: '10', renew_price: '19.99' }]
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on 500 error', async () => {
        global.mockHttp.onPatch(/\/plan\/0/).reply(500)
        await flushPromises()
        wrapper.vm.form.prices = [{ currency: 1, add_price: '19.99', offer_price: '10', renew_price: '19.99' }]
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })
})
