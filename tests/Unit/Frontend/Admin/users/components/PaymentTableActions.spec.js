jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import PaymentTableActions from '@/pages/admin/users/components/PaymentTableActions.vue'

describe('PaymentTableActions.vue', () => {
    let wrapper

    const mountComponent = (props = {}) =>
        mount(PaymentTableActions, {
            props: {
                paymentId: 10,
                userId: 1,
                ...props,
            },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['router-link', 'DeleteModal'],
            },
        })

    beforeEach(() => {
        wrapper = mountComponent()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders a delete button', () => {
        const buttons = wrapper.findAll('button')
        expect(buttons.length).toBeGreaterThan(0)
    })

    it('hides edit link when unapplied is 0 (default)', () => {
        // unapplied defaults to 0 — edit router-link should not be rendered
        expect(wrapper.find('router-link-stub').exists()).toBe(false)
    })

    it('shows edit link when unapplied is provided', () => {
        wrapper = mountComponent({ unapplied: 5 })
        // v-if="unapplied > 0" means router-link-stub renders when unapplied is truthy
        expect(wrapper.find('router-link-stub').exists()).toBe(true)
    })

    it('shows DeleteModal when delete button is clicked', async () => {
        const btn = wrapper.find('button')
        await btn.trigger('click')
        expect(wrapper.find('delete-modal-stub').exists()).toBe(true)
    })

    it('accepts required props paymentId and userId', () => {
        wrapper = mountComponent({ paymentId: 99, userId: 42 })
        expect(wrapper.exists()).toBeTruthy()
    })

    it('accepts unapplied prop to show edit link', () => {
        wrapper = mountComponent({ paymentId: 10, userId: 1, unapplied: 3 })
        expect(wrapper.find('router-link-stub').exists()).toBe(true)
    })
})
