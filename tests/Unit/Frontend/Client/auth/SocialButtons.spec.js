jest.mock('@/helpers/responseHandler.js', () => ({ errorHandler: jest.fn() }))

import { mount, flushPromises } from '@vue/test-utils'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import SocialButtons from '@/pages/client/auth/partials/SocialButtons.vue'

describe('SocialButtons.vue', () => {
    it('swaps the brand icon for a spinner while redirecting, never both', async () => {
        const axiosMock = new MockAdapter(http)
        axiosMock.onGet('/auth/redirect/google').reply(() => new Promise(() => {})) // stay busy

        const wrapper = mount(SocialButtons, { props: { social: { google: 1 } } })
        const icon = () => wrapper.find('button i').classes()

        expect(icon()).toEqual(expect.arrayContaining(['fab', 'fa-google']))

        await wrapper.find('button').trigger('click')
        await flushPromises()

        expect(icon()).toEqual(expect.arrayContaining(['fas', 'fa-circle-notch', 'fa-spin']))
        expect(icon()).not.toContain('fab')
        expect(icon()).not.toContain('fa-google')
        axiosMock.restore()
    })
})
