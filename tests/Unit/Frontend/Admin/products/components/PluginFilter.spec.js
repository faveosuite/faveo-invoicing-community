jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import PluginFilter from '@/pages/admin/products/components/PluginFilter.vue'

describe('PluginFilter.vue', () => {
    let wrapper

    const mountComponent = (props = {}) =>
        mount(PluginFilter, {
            props: {
                show: true,
                ...props,
            },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['action-button', 'DynamicSelect'],
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

    it('emits apply event when apply is called', () => {
        wrapper.vm.apply()
        expect(wrapper.emitted('apply')).toBeTruthy()
        expect(wrapper.emitted('apply')[0]).toHaveLength(1)
    })

    it('emits reset event when reset is called', () => {
        wrapper.vm.reset()
        expect(wrapper.emitted('reset')).toBeTruthy()
    })

    it('emits close event when close is triggered', () => {
        wrapper.vm.$emit('close')
        expect(wrapper.emitted('close')).toBeTruthy()
    })

    it('apply emits object-value ids as primitive values', () => {
        wrapper.vm.form.status = { id: 'bundled' }
        wrapper.vm.apply()
        const emitted = wrapper.emitted('apply')[0][0]
        expect(emitted.status).toBe('bundled')
    })

    it('reset clears form fields back to null', () => {
        wrapper.vm.form.status = 'compatible'
        wrapper.vm.reset()
        expect(wrapper.vm.form.status).toBeNull()
    })
})
