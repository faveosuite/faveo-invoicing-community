jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import GatewayCard from '@/pages/admin/settings/settings/paymentGateway/GatewayCard.vue'

const pluginStub = { name: 'Stripe', status: true }

describe('GatewayCard.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(GatewayCard, {
            props: {
                plugin: pluginStub,
                logoSrc: null,
                iconClass: 'fab fa-stripe-s',
                toggling: false,
                description: 'A payment gateway.',
            },
            global: {
                plugins: [createTestingPinia()],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the plugin name', () => {
        expect(wrapper.text()).toContain('Stripe')
    })

    it('renders the description', () => {
        expect(wrapper.text()).toContain('A payment gateway.')
    })

    it('emits settings event when settings button is clicked', async () => {
        await wrapper.find('button[title]').trigger('click')
        expect(wrapper.emitted('settings')).toBeTruthy()
    })

    it('emits toggle event when toggle button is clicked', async () => {
        const buttons = wrapper.findAll('button')
        await buttons[1].trigger('click')
        expect(wrapper.emitted('toggle')).toBeTruthy()
    })

    it('shows active status when plugin.status is true', () => {
        expect(wrapper.find('.text-success').exists()).toBeTruthy()
    })

    it('shows inactive status when plugin.status is false', async () => {
        await wrapper.setProps({ plugin: { name: 'Stripe', status: false } })
        expect(wrapper.find('.text-danger').exists()).toBeTruthy()
    })

    it('renders icon class when no logoSrc provided', () => {
        expect(wrapper.find('i.fab.fa-stripe-s').exists()).toBeTruthy()
    })

    it('renders img when logoSrc is provided', async () => {
        await wrapper.setProps({ logoSrc: '/img/stripe.png' })
        expect(wrapper.find('img').exists()).toBeTruthy()
    })
})
