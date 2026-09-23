jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import InvoiceFilter from '@/pages/admin/invoices/components/InvoiceFilter.vue'

describe('InvoiceFilter.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(InvoiceFilter, {
            props: { show: true, baseUrl: '' },
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'TextField', 'text-field', 'DynamicSelect', 'dynamic-select',
                    'DatePicker', 'date-picker', 'action-button', 'AppAlert',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the card when show is true', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
    })

    it('does not render the card when show is false', async () => {
        await wrapper.setProps({ show: false })
        expect(wrapper.find('.card').exists()).toBe(false)
    })

    it('initialises form with empty values', () => {
        expect(wrapper.vm.form.name).toBe('')
        expect(wrapper.vm.form.invoice_no).toBe('')
        expect(wrapper.vm.form.status).toBeNull()
        expect(wrapper.vm.form.currency).toBeNull()
        expect(wrapper.vm.form.from_date).toBeNull()
        expect(wrapper.vm.form.to_date).toBeNull()
    })

    it('emits apply event with non-empty params when apply() is called', async () => {
        wrapper.vm.form.name = 'John'
        wrapper.vm.form.invoice_no = 'INV-001'
        wrapper.vm.apply()
        expect(wrapper.emitted('apply')).toBeTruthy()
        const payload = wrapper.emitted('apply')[0][0]
        expect(payload.name).toBe('John')
        expect(payload.invoice_no).toBe('INV-001')
    })

    it('omits null and empty string values from apply payload', async () => {
        wrapper.vm.form.name = ''
        wrapper.vm.form.status = null
        wrapper.vm.form.invoice_no = 'INV-002'
        wrapper.vm.apply()
        const payload = wrapper.emitted('apply')[0][0]
        expect(Object.keys(payload)).not.toContain('name')
        expect(Object.keys(payload)).not.toContain('status')
        expect(payload.invoice_no).toBe('INV-002')
    })

    it('unwraps object values to their id in apply payload', () => {
        wrapper.vm.form.status = { id: 'success', name: 'Paid' }
        wrapper.vm.apply()
        const payload = wrapper.emitted('apply')[0][0]
        expect(payload.status).toBe('success')
    })

    it('emits reset event and clears form when reset() is called', () => {
        wrapper.vm.form.name = 'Test'
        wrapper.vm.form.invoice_no = 'INV-999'
        wrapper.vm.reset()
        expect(wrapper.emitted('reset')).toBeTruthy()
        expect(wrapper.vm.form.name).toBe('')
        expect(wrapper.vm.form.invoice_no).toBe('')
        expect(wrapper.vm.form.status).toBeNull()
    })

    it('emits close event when cancel action-button is clicked', async () => {
        const buttons = wrapper.findAll('action-button-stub')
        const cancelBtn = buttons.find(b => b.attributes('action') === 'cancel')
        expect(cancelBtn).toBeTruthy()
        await cancelBtn.trigger('click')
        expect(wrapper.emitted('close')).toBeTruthy()
    })

    it('exposes three statusOptions (pending, Partially paid, success)', () => {
        expect(wrapper.vm.statusOptions).toHaveLength(3)
        const ids = wrapper.vm.statusOptions.map(o => o.id)
        expect(ids).toContain('pending')
        expect(ids).toContain('Partially paid')
        expect(ids).toContain('success')
    })
})
