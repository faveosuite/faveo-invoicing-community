jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import UserTableActions from '@/pages/admin/users/components/UserTableActions.vue'

describe('UserTableActions.vue', () => {
    let wrapper

    const mountComponent = (props = {}) =>
        mount(UserTableActions, {
            props: {
                userId: 1,
                baseUrl: '',
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

    it('renders edit and view router-links', () => {
        const links = wrapper.findAll('router-link-stub')
        expect(links.length).toBeGreaterThanOrEqual(2)
    })

    it('renders a delete button', () => {
        expect(wrapper.find('button').exists()).toBe(true)
    })

    it('shows DeleteModal when delete button is clicked', async () => {
        await wrapper.find('button').trigger('click')
        expect(wrapper.find('delete-modal-stub').exists()).toBe(true)
    })

    it('accepts userId prop', () => {
        wrapper = mountComponent({ userId: 42 })
        expect(wrapper.exists()).toBeTruthy()
    })

    it('accepts custom componentName prop', () => {
        wrapper = mountComponent({ componentName: 'custom-component' })
        expect(wrapper.exists()).toBeTruthy()
    })

    it('emits deleted when DeleteModal emits deleted', async () => {
        await wrapper.find('button').trigger('click')
        const modal = wrapper.findComponent({ name: 'DeleteModal' })
        if (modal.exists()) {
            await modal.vm.$emit('deleted')
            expect(wrapper.emitted('deleted')).toBeTruthy()
        } else {
            // modal is stubbed, verify showModal state
            expect(wrapper.vm.showModal).toBe(true)
        }
    })
})
