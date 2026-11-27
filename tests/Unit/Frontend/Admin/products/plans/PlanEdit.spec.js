jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
const mockLeaveGuards = []
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn(), replace: jest.fn() }),
    useRoute: () => ({ params: { id: 0 }, query: {} }),
    onBeforeRouteLeave: (guard) => mockLeaveGuards.push(guard),
}))
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
        globalThis.mockHttp.onGet(/\/dependency\/periods/).reply(200, PERIODS_RESPONSE)
        globalThis.mockHttp.onGet(/\/dependency\/currencies/).reply(200, CURRENCIES_RESPONSE)
        globalThis.mockHttp.onGet(/\/plan\/0/).reply(200, PLAN_RESPONSE)
        globalThis.mockHttp.onPatch(/\/plan\/0/).reply(200, { data: { message: 'Plan updated' } })
        globalThis.mockHttp.onPost(/\/plan\/0\/entitlements/).reply(200, { data: { message: 'Saved' } })

        wrapper = mount(PlanEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
                    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
                    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
                    'Tooltip', 'ImageField', 'DynamicSelect', 'VersionTableActions',
                    'ProductPluginMapping', 'spinner-loader', 'router-link',
                ],
            },
        })
    })

    afterEach(() => {
        globalThis.mockHttp.reset()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the form', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
    })

    it('links the live license count to the orders on this plan', async () => {
        await flushPromises()
        expect(wrapper.find('router-link-stub').attributes('to')).toBe('/orders?product_id=1&plan_id=0')
    })

    it('warns before leaving with unsaved changes, and not after saving them', async () => {
        await flushPromises()
        const guard = mockLeaveGuards.at(-1)
        const confirmSpy = jest.spyOn(window, 'confirm').mockReturnValue(false)
        expect(guard()).toBe(true) // nothing changed

        wrapper.vm.form.name = 'Renamed'
        expect(guard()).toBe(false) // asked, and stayed
        expect(confirmSpy).toHaveBeenCalledWith('message.unsaved_changes_confirm')
        confirmSpy.mockRestore()
    })

    it('fetches periods, currencies, and plan data on mount', async () => {
        await flushPromises()
        const urls = globalThis.mockHttp.history.get.map(r => r.url)
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

    it('shows the status in the header switch and the summary, and the switch changes it', async () => {
        await flushPromises()
        const sw = wrapper.findComponent({ name: 'Switch' })
        expect(sw.props('value')).toBe(true)
        expect(wrapper.find('#plan-status-label').text()).toBe('message.active')
        sw.props('onChange')(false)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.form.status).toBe(0)
        expect(wrapper.find('#plan-status-label').text()).toBe('message.inactive')
        expect(wrapper.find('.list-group .badge.bg-secondary').text()).toBe('message.inactive')
    })

    it('submits PATCH /plan/0 on valid form', async () => {
        await flushPromises()
        wrapper.vm.form.prices = [{ currency: 1, add_price: '19.99', offer_price: '10', renew_price: '19.99' }]
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.patch.some(r => /\/plan\/0/.test(r.url))).toBe(true)
    })

    it('calls successHandler on success', async () => {
        await flushPromises()
        wrapper.vm.form.prices = [{ currency: 1, add_price: '19.99', offer_price: '10', renew_price: '19.99' }]
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('saves the plan, then its features', async () => {
        await flushPromises()
        wrapper.vm.form.prices = [{ currency: 1, add_price: '19.99', offer_price: '10', renew_price: '19.99' }]
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => /\/plan\/0\/entitlements/.test(r.url))).toBe(true)
    })

    it('calls errorHandler on 500 error', async () => {
        globalThis.mockHttp.onPatch(/\/plan\/0/).reply(500)
        await flushPromises()
        wrapper.vm.form.prices = [{ currency: 1, add_price: '19.99', offer_price: '10', renew_price: '19.99' }]
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })
})
