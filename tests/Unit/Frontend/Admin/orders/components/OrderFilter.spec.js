jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import OrderFilter from '@/pages/admin/orders/components/OrderFilter.vue'

describe('OrderFilter.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(OrderFilter, {
            props: { show: true, baseUrl: '' },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['TextField', 'DynamicSelect', 'DatePicker', 'action-button'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the filter card when show is true', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
    })

    it('does not render the filter card when show is false', async () => {
        await wrapper.setProps({ show: false })
        expect(wrapper.find('.card').exists()).toBe(false)
    })

    it('initialises form fields as empty/null', () => {
        expect(wrapper.vm.form.order_no).toBe('')
        expect(wrapper.vm.form.product_id).toBeNull()
        expect(wrapper.vm.form.from).toBeNull()
        expect(wrapper.vm.form.till).toBeNull()
        expect(wrapper.vm.form.domain).toBe('')
        expect(wrapper.vm.form.act_ins).toBeNull()
        expect(wrapper.vm.form.renewal).toBeNull()
        expect(wrapper.vm.form.version).toBeNull()
    })

    it('emits "apply" with non-empty params when apply() is called', () => {
        wrapper.vm.form.order_no = 'ORD-001'
        wrapper.vm.apply()
        expect(wrapper.emitted('apply')).toBeTruthy()
        expect(wrapper.emitted('apply')[0][0]).toMatchObject({ order_no: 'ORD-001' })
    })

    it('strips empty/null values from apply() payload', () => {
        wrapper.vm.form.order_no = 'ORD-002'
        wrapper.vm.form.domain = ''
        wrapper.vm.form.product_id = null
        wrapper.vm.apply()
        const payload = wrapper.emitted('apply')[0][0]
        expect(payload).not.toHaveProperty('domain')
        expect(payload).not.toHaveProperty('product_id')
    })

    it('reduces object values to their .id in apply() payload', () => {
        wrapper.vm.form.act_ins = { id: 'installed', name: 'Installed' }
        wrapper.vm.apply()
        const payload = wrapper.emitted('apply')[0][0]
        expect(payload.act_ins).toBe('installed')
    })

    it('resets form fields and emits "reset" when reset() is called', () => {
        wrapper.vm.form.order_no = 'ORD-003'
        wrapper.vm.reset()
        expect(wrapper.vm.form.order_no).toBe('')
        expect(wrapper.emitted('reset')).toBeTruthy()
    })

    it('emits "close" when cancel action-button is clicked', async () => {
        const buttons = wrapper.findAll('action-button-stub')
        const cancelBtn = buttons.find(b => b.attributes('action') === 'cancel')
        await cancelBtn.trigger('click')
        expect(wrapper.emitted('close')).toBeTruthy()
    })
})
