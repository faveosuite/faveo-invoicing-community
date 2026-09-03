jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: {}, query: {} }),
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import OrderTableActions from '@/pages/admin/orders/components/OrderTableActions.vue'

describe('OrderTableActions.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(OrderTableActions, {
            props: { orderId: 7, baseUrl: '', showDelete: false },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['DeleteModal', 'RouterLink'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the view router-link', () => {
        expect(wrapper.find('a.btn-light').exists()).toBe(true)
    })

    it('does not render delete button when showDelete is false', () => {
        expect(wrapper.find('button').exists()).toBe(false)
    })

    it('renders delete button when showDelete is true', async () => {
        await wrapper.setProps({ showDelete: true })
        expect(wrapper.find('button').exists()).toBe(true)
    })

    it('showModal starts as false', () => {
        expect(wrapper.vm.showModal).toBe(false)
    })

    it('clicking the delete button sets showModal to true', async () => {
        await wrapper.setProps({ showDelete: true })
        await wrapper.find('button').trigger('click')
        expect(wrapper.vm.showModal).toBe(true)
    })

    it('renders DeleteModal when showModal is true', async () => {
        await wrapper.setProps({ showDelete: true })
        wrapper.vm.showModal = true
        await wrapper.vm.$nextTick()
        expect(wrapper.find('delete-modal-stub').exists()).toBe(true)
    })

    it('does not render DeleteModal when showModal is false', async () => {
        await wrapper.setProps({ showDelete: true })
        expect(wrapper.find('delete-modal-stub').exists()).toBe(false)
    })

    it('router-link points to /orders/:orderId', () => {
        const link = wrapper.find('a.btn-light')
        expect(link.attributes('to')).toBe('/orders/7')
    })
})
