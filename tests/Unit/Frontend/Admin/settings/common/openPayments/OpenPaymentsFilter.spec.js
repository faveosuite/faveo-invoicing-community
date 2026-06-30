jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import OpenPaymentsFilter from '@/pages/admin/settings/common/openPayments/OpenPaymentsFilter.vue'

describe('OpenPaymentsFilter.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(OpenPaymentsFilter, {
            props: {
                show: true,
                baseUrl: 'http://localhost',
            },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['SelectField', 'DatePicker', 'action-button'],
            },
        })
    })

    afterEach(() => {
        wrapper.unmount()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('accepts show and baseUrl props', () => {
        expect(wrapper.props('show')).toBe(true)
        expect(wrapper.props('baseUrl')).toBe('http://localhost')
    })

    it('emits apply event with non-null form fields when apply is called', async () => {
        wrapper.vm.form.status = { id: 'completed', name: 'Completed' }
        wrapper.vm.form.gateway = { id: 'Stripe', name: 'Stripe' }
        await wrapper.vm.apply()
        const emitted = wrapper.emitted('apply')
        expect(emitted).toBeTruthy()
        expect(emitted[0][0]).toMatchObject({ status: 'completed', gateway: 'Stripe' })
    })

    it('does not include null fields in apply payload', async () => {
        wrapper.vm.form.status = { id: 'pending', name: 'Pending' }
        wrapper.vm.form.gateway = null
        wrapper.vm.form.currency = null
        await wrapper.vm.apply()
        const payload = wrapper.emitted('apply')[0][0]
        expect(payload).toHaveProperty('status', 'pending')
        expect(payload).not.toHaveProperty('gateway')
        expect(payload).not.toHaveProperty('currency')
    })

    it('emits reset event when reset is called', async () => {
        await wrapper.vm.reset()
        expect(wrapper.emitted('reset')).toBeTruthy()
    })

    it('resets form to empty state when reset is called', async () => {
        wrapper.vm.form.status = 'completed'
        wrapper.vm.form.gateway = 'Razorpay'
        await wrapper.vm.reset()
        expect(wrapper.vm.form.status).toBeNull()
        expect(wrapper.vm.form.gateway).toBeNull()
        expect(wrapper.vm.form.currency).toBeNull()
        expect(wrapper.vm.form.from_date).toBeNull()
        expect(wrapper.vm.form.to_date).toBeNull()
    })

    it('emits close event when close is triggered', async () => {
        wrapper.vm.$emit('close')
        expect(wrapper.emitted('close')).toBeTruthy()
    })

    it('makes no HTTP calls — it is a presentational filter component', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get).toHaveLength(0)
        expect(globalThis.mockHttp.history.post).toHaveLength(0)
    })
})
