jest.mock('@/helpers/responseHandler.js', () => ({ errorHandler: jest.fn() }))

import { mount, flushPromises } from '@vue/test-utils'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import SocialButtons from '@/pages/client/auth/partials/SocialButtons.vue'

describe('SocialButtons.vue', () => {
    it('swaps the Google logo for a spinner while redirecting, never both', async () => {
        const axiosMock = new MockAdapter(http)
        axiosMock.onGet('/auth/redirect/google').reply(() => new Promise(() => {})) // stay busy

        const wrapper = mount(SocialButtons, { props: { social: { google: 1 } } })
        // Google shows its multicolour SVG logo, then only the spinner while redirecting.
        expect(wrapper.find('button svg').exists()).toBe(true)
        expect(wrapper.find('button i').exists()).toBe(false)

        await wrapper.find('button').trigger('click')
        await flushPromises()

        expect(wrapper.find('button i').classes()).toEqual(expect.arrayContaining(['fas', 'fa-circle-notch', 'fa-spin']))
        expect(wrapper.find('button svg').exists()).toBe(false)
        axiosMock.restore()
    })
})
