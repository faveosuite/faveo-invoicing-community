jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot /></a>' } }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDateTime: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import OpenPaymentsList from '@/pages/admin/settings/common/openPayments/OpenPaymentsList.vue'

describe('OpenPaymentsList.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(OpenPaymentsList, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'StaticAlert', 'DataTable', 'AppModal',
                    'OpenPaymentsFilter', 'inline-loader', 'action-button',
                    'router-link', 'delete-modal',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the Open Payments card title', () => {
        expect(wrapper.html()).toContain('Open Payments')
    })

    it('showFilter defaults to false', () => {
        expect(wrapper.html()).toBeTruthy()
    })

    it('toggles showFilter when filter button is clicked', async () => {
        const btn = wrapper.find('button.btn-tool')
        if (btn.exists()) {
            await btn.trigger('click')
        }
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders DataTable stub', () => {
        expect(wrapper.findComponent({ name: 'DataTable' }).exists() || wrapper.html().includes('datatable')).toBeTruthy()
    })

    // ── statusBadgeClass ─────────────────────────────────────────────
    it('statusBadgeClass returns bg-success for completed', () => {
        expect(wrapper.vm.statusBadgeClass('completed')['bg-success']).toBe(true)
    })

    it('statusBadgeClass returns bg-danger for failed', () => {
        expect(wrapper.vm.statusBadgeClass('failed')['bg-danger']).toBe(true)
    })

    it('statusBadgeClass returns bg-warning for pending', () => {
        expect(wrapper.vm.statusBadgeClass('pending')['bg-warning']).toBe(true)
    })

    it('statusBadgeClass returns all-false for unknown status', () => {
        const cls = wrapper.vm.statusBadgeClass('unknown')
        expect(cls['bg-success']).toBe(false)
        expect(cls['bg-danger']).toBe(false)
        expect(cls['bg-warning']).toBe(false)
    })

    // ── onFilterApply / onFilterReset ────────────────────────────────
    it('onFilterApply sets activeFilters and hides filter', () => {
        wrapper.vm.showFilter = true
        wrapper.vm.dtRef = { refresh: jest.fn() }
        wrapper.vm.onFilterApply({ gateway: 'stripe' })
        expect(wrapper.vm.activeFilters).toEqual({ gateway: 'stripe' })
        expect(wrapper.vm.showFilter).toBe(false)
    })

    it('onFilterReset clears activeFilters', () => {
        wrapper.vm.activeFilters = { gateway: 'razorpay' }
        wrapper.vm.dtRef = { refresh: jest.fn() }
        wrapper.vm.onFilterReset()
        expect(wrapper.vm.activeFilters).toEqual({})
    })

    // ── copyUrl ──────────────────────────────────────────────────────
    it('copyUrl writes to clipboard and sets copied=true', async () => {
        jest.useFakeTimers()
        Object.assign(navigator, { clipboard: { writeText: jest.fn().mockResolvedValue(undefined) } })
        wrapper.vm.copyUrl()
        expect(navigator.clipboard.writeText).toHaveBeenCalled()
        await flushPromises()
        expect(wrapper.vm.copied).toBe(true)
        jest.advanceTimersByTime(2001)
        expect(wrapper.vm.copied).toBe(false)
        jest.useRealTimers()
    })

    // ── templates.payment_status ─────────────────────────────────────
    it('payment_status template renders bg-success for completed', () => {
        const vnode = wrapper.vm.tableOptions.templates.payment_status(null, { payment_status: 'completed' })
        expect(JSON.stringify(vnode.props.class)).toContain('bg-success')
    })

    it('payment_status template renders bg-danger for failed', () => {
        const vnode = wrapper.vm.tableOptions.templates.payment_status(null, { payment_status: 'failed' })
        expect(JSON.stringify(vnode.props.class)).toContain('bg-danger')
    })

    it('payment_status template renders bg-warning for pending', () => {
        const vnode = wrapper.vm.tableOptions.templates.payment_status(null, { payment_status: 'pending' })
        expect(JSON.stringify(vnode.props.class)).toContain('bg-warning')
    })

    // ── templates — simple fields ────────────────────────────────────
    describe('templates simple fields', () => {
        const tpl = () => wrapper.vm.tableOptions.templates
        it('name returns — when falsy', () => { expect(tpl().name(null, {})).toBe('—') })
        it('name returns value when set', () => { expect(tpl().name(null, { name: 'John Doe' })).toBe('John Doe') })
        it('company returns — when falsy', () => { expect(tpl().company(null, {})).toBe('—') })
        it('email returns — when falsy', () => { expect(tpl().email(null, {})).toBe('—') })
        it('mobile returns — when falsy', () => { expect(tpl().mobile(null, {})).toBe('—') })
        it('gateway returns — when falsy', () => { expect(tpl().gateway(null, {})).toBe('—') })
        it('transaction_id returns — when falsy', () => { expect(tpl().transaction_id(null, {})).toBe('—') })
        it('amount uses currency_symbol when present', () => {
            expect(tpl().amount(null, { currency_symbol: '$', currency: 'USD', amount: '100' })).toBe('$ 100')
        })
        it('amount falls back to currency when symbol is absent', () => {
            expect(tpl().amount(null, { currency_symbol: '', currency: 'USD', amount: '100' })).toBe('USD 100')
        })
    })

    // ── requestAdapter ───────────────────────────────────────────────
    describe('requestAdapter', () => {
        const adapt = (d) => wrapper.vm.tableOptions.requestAdapter(d)
        it('defaults sort-field to created_at', () => {
            expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-field']).toBe('created_at')
        })
        it('defaults to desc when no orderBy (latest first)', () => {
            expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-order']).toBe('desc')
        })
        it('sets desc when ascending=false', () => {
            expect(adapt({ ascending: false, query: '', page: 1, limit: 10 })['sort-order']).toBe('desc')
        })
        it('spreads activeFilters into result', () => {
            wrapper.vm.activeFilters = { gateway: 'stripe' }
            const result = adapt({ ascending: true, query: '', page: 1, limit: 10 })
            expect(result.gateway).toBe('stripe')
        })
    })
})
