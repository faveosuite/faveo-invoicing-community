jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import PaymentFilter from '@/pages/admin/settings/logs/PaymentFilter.vue'

describe('PaymentFilter.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(PaymentFilter, {
            props: { show: true, baseUrl: '' },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['SelectField', 'DatePicker', 'action-button'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('is hidden when show prop is false', () => {
        const hiddenWrapper = mount(PaymentFilter, {
            props: { show: false, baseUrl: '' },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['SelectField', 'DatePicker', 'action-button'],
            },
        })
        expect(hiddenWrapper.find('.card').exists()).toBeFalsy()
        hiddenWrapper.unmount()
    })

    it('shows filter card when show prop is true', () => {
        expect(wrapper.find('.card').exists()).toBeTruthy()
    })

    it('emits apply event when apply is triggered', () => {
        wrapper.vm.apply()
        expect(wrapper.emitted('apply')).toBeTruthy()
    })

    it('emits apply event with empty params when form is blank', () => {
        wrapper.vm.apply()
        const emitted = wrapper.emitted('apply')
        expect(emitted).toBeTruthy()
        expect(emitted[0][0]).toEqual({})
    })

    it('emits apply with status when status is set', () => {
        wrapper.vm.form.status = { id: 'success', name: 'Success' }
        wrapper.vm.apply()
        const payload = wrapper.emitted('apply')[0][0]
        expect(payload.status).toBe('success')
    })

    it('emits apply with date params when dates are set', () => {
        wrapper.vm.form.dateFrom = '2026-01-01'
        wrapper.vm.form.dateTill = '2026-06-01'
        wrapper.vm.apply()
        const payload = wrapper.emitted('apply')[0][0]
        expect(payload.date_from).toBe('2026-01-01')
        expect(payload.date_till).toBe('2026-06-01')
    })

    it('emits reset event when reset is triggered', () => {
        wrapper.vm.form.status = { id: 'failed', name: 'Failed' }
        wrapper.vm.reset()
        expect(wrapper.emitted('reset')).toBeTruthy()
        expect(wrapper.vm.form.status).toBeNull()
    })
})
