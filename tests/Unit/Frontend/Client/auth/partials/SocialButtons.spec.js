jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { errorHandler } from '@/helpers/responseHandler'
import SocialButtons from '@/pages/client/auth/partials/SocialButtons.vue'

describe('SocialButtons.vue', () => {
    let wrapper
    let axiosMock

    const defaultProps = {
        social: { google: 0, github: 0, twitter: 0, linkedin: 0, facebook: 0 },
        baseUrl: '',
    }

    beforeEach(() => {
        axiosMock = new MockAdapter(http)

        wrapper = mount(SocialButtons, {
            global: {
                plugins: [createTestingPinia()],
            },
            props: defaultProps,
        })
    })

    afterEach(() => {
        axiosMock.restore()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders no buttons when all social providers are disabled', () => {
        expect(wrapper.find('a').exists()).toBeFalsy()
    })

    it('renders a button when google is enabled', async () => {
        await wrapper.setProps({ social: { google: 1, github: 0, twitter: 0, linkedin: 0, facebook: 0 } })
        expect(wrapper.find('a').exists()).toBeTruthy()
    })

    it('renders the correct number of buttons for enabled providers', async () => {
        await wrapper.setProps({ social: { google: 1, github: 1, twitter: 0, linkedin: 0, facebook: 0 } })
        expect(wrapper.findAll('a').length).toBe(2)
    })

    it('renders the "or" divider when providers are enabled', async () => {
        await wrapper.setProps({ social: { google: 1, github: 0, twitter: 0, linkedin: 0, facebook: 0 } })
        expect(wrapper.find('.divider').exists()).toBeTruthy()
    })

    it('does not render the divider when no providers are enabled', () => {
        expect(wrapper.find('.divider').exists()).toBeFalsy()
    })

    it('calls GET /auth/redirect/google when google button is clicked', async () => {
        axiosMock.onGet('/auth/redirect/google').reply(200, {
            data: { url: 'https://accounts.google.com/oauth' },
        })

        await wrapper.setProps({ social: { google: 1, github: 0, twitter: 0, linkedin: 0, facebook: 0 } })
        await wrapper.find('a').trigger('click')
        await flushPromises()

        const reqs = axiosMock.history.get.filter(r => r.url === '/auth/redirect/google')
        expect(reqs.length).toBe(1)
    })

    it('calls errorHandler when the redirect API returns an error', async () => {
        axiosMock.onGet('/auth/redirect/github').reply(500, { message: 'Server error' })

        await wrapper.setProps({ social: { google: 0, github: 1, twitter: 0, linkedin: 0, facebook: 0 } })
        await wrapper.find('a').trigger('click')
        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
    })

    it('calls errorHandler when redirect URL is missing in response', async () => {
        axiosMock.onGet('/auth/redirect/google').reply(200, { data: {} })

        await wrapper.setProps({ social: { google: 1, github: 0, twitter: 0, linkedin: 0, facebook: 0 } })
        await wrapper.find('a').trigger('click')
        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
    })

    it('ignores subsequent clicks while a provider is busy', async () => {
        let callCount = 0
        axiosMock.onGet('/auth/redirect/google').reply(() => {
            callCount++
            return [200, { data: { url: 'https://accounts.google.com/oauth' } }]
        })

        await wrapper.setProps({ social: { google: 1, github: 0, twitter: 0, linkedin: 0, facebook: 0 } })
        const btn = wrapper.find('a')
        await btn.trigger('click')
        await btn.trigger('click')
        await flushPromises()

        expect(callCount).toBe(1)
    })
})
