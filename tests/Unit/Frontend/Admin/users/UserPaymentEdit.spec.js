jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { id: '3', paymentId: '7' }, query: {} }),
}))
jest.mock('@/plugins/i18n', () => ({ __: (key) => key }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import UserPaymentEdit from '@/pages/admin/users/UserPaymentEdit.vue'

const editDataResponse = {
    data: {
        symbol: '$',
        available_credit: 200,
        invoices: [
            { id: 11, date: '2024-01-01', number: 'INV-002', grand_total: 150, pending: 80 },
        ],
    },
}

describe('UserPaymentEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/payments\/7\/edit/).reply(200, { data: editDataResponse.data })
        global.mockHttp.onPost(/\/newMultiplePayment\/update\/3/).reply(200, { data: { message: 'Updated' } })
        wrapper = mount(UserPaymentEdit, {
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

    it('fetches payment edit data on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.some(r => /\/payments\/7\/edit/.test(r.url))).toBe(true)
    })

    it('sets symbol after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.symbol).toBe('$')
    })

    it('sets availableCredit after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.availableCredit).toBe(200)
    })

    it('populates invoices after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.invoices.length).toBe(1)
    })

    it('sets loading to false after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('calls POST /newMultiplePayment/update on submit', async () => {
        await flushPromises()
        wrapper.vm.form.payment_date   = '2024-01-20'
        wrapper.vm.form.payment_method = 'cash'
        wrapper.vm.invoices[0].checked  = true
        wrapper.vm.invoices[0].payAmount = 50
        await wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.post.some(r => /\/newMultiplePayment\/update\/3/.test(r.url))).toBe(true)
    })

    it('does not submit when validation fails (empty form)', async () => {
        await flushPromises()
        global.mockHttp.reset()
        global.mockHttp.onPost(/\/newMultiplePayment\/update\/3/).reply(200, { data: {} })
        await wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.post.length).toBe(0)
    })

    it('canSubmit is false when no invoices are allocated', async () => {
        await flushPromises()
        expect(wrapper.vm.canSubmit).toBe(false)
    })

    it('canSubmit is true when credit is allocated within available credit', async () => {
        await flushPromises()
        wrapper.vm.invoices[0].checked   = true
        wrapper.vm.invoices[0].payAmount = 50
        expect(wrapper.vm.canSubmit).toBe(true)
    })

    it('totalApplied sums checked invoice pay amounts', async () => {
        await flushPromises()
        wrapper.vm.invoices[0].checked   = true
        wrapper.vm.invoices[0].payAmount = 75
        expect(parseFloat(wrapper.vm.totalApplied)).toBe(75)
    })

    it('remainingCredit decreases as credit is applied', async () => {
        await flushPromises()
        wrapper.vm.invoices[0].checked   = true
        wrapper.vm.invoices[0].payAmount = 100
        expect(parseFloat(wrapper.vm.remainingCredit)).toBe(100)
    })

    it('onCheck clears payAmount when unchecked', async () => {
        await flushPromises()
        const inv = wrapper.vm.invoices[0]
        inv.payAmount = 50
        inv.checked   = false
        wrapper.vm.onCheck(inv)
        expect(inv.payAmount).toBe('')
    })

    it('renders the invoices table', async () => {
        await flushPromises()
        expect(wrapper.find('table').exists()).toBe(true)
    })
})
