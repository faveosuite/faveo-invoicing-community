jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { id: '3' }, query: {} }),
}))
jest.mock('@/plugins/i18n', () => ({ __: (key) => key }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import UserPaymentCreate from '@/pages/admin/users/UserPaymentCreate.vue'

const paymentDataResponse = {
    data: {
        currencies: [
            { code: 'USD', name: 'US Dollar', symbol: '$' },
        ],
        invoices: [
            { id: 10, date: '2024-01-01', number: 'INV-001', grand_total: 100, pending: 100, currency: 'USD' },
        ],
    },
}

describe('UserPaymentCreate.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/newPayment\/receive/).reply(200, { data: paymentDataResponse.data })
        global.mockHttp.onPost(/\/newMultiplePayment\/receive\/3/).reply(200, { data: { message: 'Saved' } })
        wrapper = mount(UserPaymentCreate, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'AppBreadcrumb', 'AppButton', 'DatePicker',
                    'SelectField', 'TextField', 'inline-loader', 'loader',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches payment data on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.some(r => /\/newPayment\/receive/.test(r.url))).toBe(true)
    })

    it('populates currencies after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.currencies.length).toBeGreaterThan(0)
        expect(wrapper.vm.currencies[0].code).toBe('USD')
    })

    it('populates invoices after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.invoices.length).toBeGreaterThan(0)
    })

    it('sets loading to false after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('calls POST /newMultiplePayment/receive on submit', async () => {
        await flushPromises()
        wrapper.vm.form.payment_date   = '2024-01-15'
        wrapper.vm.form.payment_method = 'cash'
        wrapper.vm.form.amount         = '100'
        wrapper.vm.form.currency       = 'USD'
        await wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.post.some(r => /\/newMultiplePayment\/receive\/3/.test(r.url))).toBe(true)
    })

    it('does not submit when validation fails', async () => {
        await flushPromises()
        // form is empty — validate() should fail
        global.mockHttp.reset()
        global.mockHttp.onPost(/\/newMultiplePayment\/receive\/3/).reply(200, { data: {} })
        await wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.post.length).toBe(0)
    })

    it('onCurrencyChange updates form currency', async () => {
        await flushPromises()
        wrapper.vm.onCurrencyChange({ value: 'EUR', name: 'Euro' })
        expect(wrapper.vm.form.currency).toBe('EUR')
    })

    it('distributeAmount allocates amount across checked invoices', async () => {
        await flushPromises()
        wrapper.vm.form.amount = '50'
        wrapper.vm.invoices[0].checked = true
        wrapper.vm.distributeAmount()
        expect(wrapper.vm.invoices[0].payAmount).toBeTruthy()
    })

    it('amountToCredit is 0 when nothing is allocated', async () => {
        await flushPromises()
        wrapper.vm.form.amount = '0'
        expect(parseFloat(wrapper.vm.amountToCredit)).toBe(0)
    })

    it('renders the invoices table', async () => {
        await flushPromises()
        expect(wrapper.find('table').exists()).toBe(true)
    })

    it('onCheck clears payAmount when unchecking', async () => {
        await flushPromises()
        const inv = { checked: false, payAmount: '10', pending: '10' }
        wrapper.vm.onCheck(inv)
        expect(inv.payAmount).toBe('')
    })

    it('onCurrencyChange sets form.currency from val.value', async () => {
        await flushPromises()
        wrapper.vm.onCurrencyChange({ value: 'USD' })
        expect(wrapper.vm.form.currency).toBe('USD')
    })

    it('onCurrencyChange clears currency when val is null', async () => {
        await flushPromises()
        wrapper.vm.onCurrencyChange(null)
        expect(wrapper.vm.form.currency).toBe('')
    })

    it('submit calls POST /newMultiplePayment/receive on success', async () => {
        global.mockHttp.onPost(/\/newMultiplePayment\/receive/).reply(200, { message: 'ok' })
        await flushPromises()
        wrapper.vm.form.currency       = 'USD'
        wrapper.vm.form.payment_date   = '2025-01-01'
        wrapper.vm.form.payment_method = 'cash'
        wrapper.vm.form.amount         = '100'
        await wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.post.some(r => r.url.includes('newMultiplePayment'))).toBe(true)
    })

    it('submit handles API validation error', async () => {
        global.mockHttp.onPost(/\/newMultiplePayment\/receive/).reply(422, {
            errors: { currency: ['Required'] }
        })
        await flushPromises()
        wrapper.vm.form.currency       = 'USD'
        wrapper.vm.form.payment_date   = '2025-01-01'
        wrapper.vm.form.payment_method = 'cash'
        wrapper.vm.form.amount         = '100'
        await wrapper.vm.submit()
        await flushPromises()
        expect(wrapper.vm.submitting).toBe(false)
    })

    it('submit returns early when validate() fails', async () => {
        await flushPromises()
        wrapper.vm.form.currency       = ''
        wrapper.vm.form.payment_date   = ''
        wrapper.vm.form.payment_method = ''
        wrapper.vm.form.amount         = ''
        const before = global.mockHttp.history.post.length
        await wrapper.vm.submit()
        expect(global.mockHttp.history.post.length).toBe(before)
    })
})
