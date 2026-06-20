jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import InvoiceTableActions from '@/pages/admin/invoices/components/InvoiceTableActions.vue'

describe('InvoiceTableActions.vue', () => {
    let wrapper

    const mountComponent = (props = {}) => mount(InvoiceTableActions, {
        props: { invoiceId: 42, ...props },
        global: {
            plugins: [createTestingPinia()],
            stubs: ['DeleteModal', 'delete-modal', 'router-link', 'AppAlert'],
        },
    })

    beforeEach(() => {
        wrapper = mountComponent()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders view router-link pointing to /invoices/:id', () => {
        const links = wrapper.findAll('router-link-stub')
        const viewLink = links.find(l => l.attributes('to') === '/invoices/42')
        expect(viewLink).toBeTruthy()
    })

    it('renders edit router-link pointing to /invoices/:id/edit', () => {
        const links = wrapper.findAll('router-link-stub')
        const editLink = links.find(l => l.attributes('to') === '/invoices/42/edit')
        expect(editLink).toBeTruthy()
    })

    it('does not show delete button when showDelete is false (default)', () => {
        expect(wrapper.find('button.btn').exists()).toBe(false)
    })

    it('shows delete button when showDelete is true', async () => {
        const w = mountComponent({ showDelete: true })
        expect(w.find('button.btn').exists()).toBe(true)
    })

    it('does not render DeleteModal by default even with showDelete=true', async () => {
        const w = mountComponent({ showDelete: true })
        // showModal starts false so the modal should not be rendered
        expect(w.find('delete-modal-stub').exists()).toBe(false)
    })

    it('sets showModal to true when delete button is clicked', async () => {
        const w = mountComponent({ showDelete: true })
        await w.find('button.btn').trigger('click')
        expect(w.vm.showModal).toBe(true)
    })

    it('renders DeleteModal when showModal is true', async () => {
        const w = mountComponent({ showDelete: true })
        w.vm.showModal = true
        await w.vm.$nextTick()
        expect(w.find('delete-modal-stub').exists()).toBe(true)
    })

    it('accepts invoiceId as a string', () => {
        const w = mountComponent({ invoiceId: '99' })
        const links = w.findAll('router-link-stub')
        const viewLink = links.find(l => l.attributes('to') === '/invoices/99')
        expect(viewLink).toBeTruthy()
    })
})
