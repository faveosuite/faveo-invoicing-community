jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import AuthLayout from '@/pages/client/auth/partials/AuthLayout.vue'

describe('AuthLayout.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(AuthLayout, {
            global: {
                plugins: [createTestingPinia()],
            },
            slots: {
                default: '<div class="slot-content">slot content</div>',
            },
        })
    })

    afterEach(() => {
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the outer row container', () => {
        expect(wrapper.find('.row.justify-content-center').exists()).toBeTruthy()
    })

    it('renders the card element', () => {
        expect(wrapper.find('.card').exists()).toBeTruthy()
    })

    it('renders the card-body element', () => {
        expect(wrapper.find('.card-body').exists()).toBeTruthy()
    })

    it('renders slot content inside card-body', () => {
        expect(wrapper.find('.card-body .slot-content').exists()).toBeTruthy()
    })

    it('renders slot text content', () => {
        expect(wrapper.find('.slot-content').text()).toBe('slot content')
    })

    it('has shadow and rounded classes on the card', () => {
        const card = wrapper.find('.card')
        expect(card.classes()).toContain('shadow-md')
        expect(card.classes()).toContain('rounded-3')
    })

    it('has border-0 class on the card', () => {
        expect(wrapper.find('.card.border-0').exists()).toBeTruthy()
    })
})
