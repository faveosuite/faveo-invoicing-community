jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import UserFilter from '@/pages/admin/users/components/UserFilter.vue'

describe('UserFilter.vue', () => {
    let wrapper

    const mountComponent = (props = {}) =>
        mount(UserFilter, {
            props: {
                show: true,
                baseUrl: '',
                ...props,
            },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['TextField', 'DynamicSelect', 'DatePicker', 'action-button'],
            },
        })

    beforeEach(() => {
        wrapper = mountComponent()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders when show is true', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
    })

    it('does not render when show is false', () => {
        wrapper = mountComponent({ show: false })
        expect(wrapper.find('.card').exists()).toBe(false)
    })

    it('emits apply event with filter params when apply is called', async () => {
        wrapper.vm.apply()
        expect(wrapper.emitted('apply')).toBeTruthy()
        expect(wrapper.emitted('apply')[0]).toHaveLength(1)
    })

    it('emits reset event when reset is called', () => {
        wrapper.vm.reset()
        expect(wrapper.emitted('reset')).toBeTruthy()
    })

    it('emits close event when cancel is clicked', async () => {
        wrapper.vm.$emit('close')
        expect(wrapper.emitted('close')).toBeTruthy()
    })

    it('applies only non-null non-empty form values in apply params', () => {
        wrapper.vm.form.company = 'Acme'
        wrapper.vm.apply()
        const emitted = wrapper.emitted('apply')[0][0]
        expect(emitted.company).toBe('Acme')
    })

    it('resets form fields when reset is called', () => {
        wrapper.vm.form.company = 'Test Co'
        wrapper.vm.reset()
        expect(wrapper.vm.form.company).toBe('')
    })

    it('extracts id from object values in apply', () => {
        wrapper.vm.form.role = { id: 'admin', name: 'Admin' }
        wrapper.vm.apply()
        const emitted = wrapper.emitted('apply')[0][0]
        expect(emitted.role).toBe('admin')
    })
})
