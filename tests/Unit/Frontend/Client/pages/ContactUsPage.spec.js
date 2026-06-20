jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/client/contactUsValidations', () => ({ contactUsSchema: {} }))
jest.mock('@recaptcha', () => ({
    RecaptchaField: {
        template: '<div />',
        props: ['action'],
        methods: {
            getPayload: () => Promise.resolve({ 'g-recaptcha-response': 'token' }),
            reset: jest.fn(),
            triggerFallback: jest.fn(),
        },
    },
}), { virtual: true })

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { validateForm } from '@/helpers/formUtils.js'
import ContactUsPage from '@/pages/client/pages/ContactUsPage.vue'

const contactInfoFixture = {
    address: '123 Main St',
    city: 'Springfield',
    state: 'IL',
    country: 'USA',
    zip: '62701',
    phone_code: '1',
    phone: '5555555',
    company_email: 'info@example.com',
}

describe('ContactUsPage.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('contact-us-info').reply(200, { data: contactInfoFixture })
        axiosMock.onGet('honeypot').reply(200, { data: { pot: 'hp_name', time: 'hp_time', token: 'tok' } })

        wrapper = mount(ContactUsPage, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'alert',
                    'phone-field',
                    'recaptcha-field',
                    'loader',
                    'router-link',
                ],
            },
        })
    })

    afterEach(() => {
        axiosMock.restore()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('calls GET contact-us-info on mount', async () => {
        await flushPromises()
        expect(axiosMock.history.get.some(r => r.url.includes('contact-us-info'))).toBe(true)
    })

    it('sets loadingInfo to false after mount resolves', async () => {
        await flushPromises()
        expect(wrapper.vm.loadingInfo).toBe(false)
    })

    it('populates contact info after successful load', async () => {
        await flushPromises()
        expect(wrapper.vm.info).toEqual(contactInfoFixture)
    })

    it('renders the contact form after loading', async () => {
        await flushPromises()
        expect(wrapper.find('form').exists()).toBeTruthy()
    })

    it('renders a submit button', async () => {
        await flushPromises()
        const btn = wrapper.find('button[type="submit"]')
        expect(btn.exists()).toBeTruthy()
    })

    it('handles 500 error from contact-us-info gracefully', async () => {
        axiosMock.onGet('contact-us-info').reply(500)
        axiosMock.onGet('honeypot').reply(500)

        const w = mount(ContactUsPage, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['alert', 'phone-field', 'recaptcha-field', 'loader', 'router-link'],
            },
        })
        await flushPromises()
        expect(w.vm.loadingInfo).toBe(false)
        expect(w.vm.info).toBeNull()
        w.unmount()
    })

    it('does not call POST /contact-us when validation fails', async () => {
        validateForm.mockResolvedValueOnce(false)
        await flushPromises()

        let postCalled = false
        axiosMock.onPost('contact-us').reply(() => {
            postCalled = true
            return [200, {}]
        })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(postCalled).toBe(false)
    })

    it('calls POST /contact-us when validation passes and captcha resolves token', async () => {
        validateForm.mockResolvedValueOnce(true)
        axiosMock.onPost('contact-us').reply(200, { message: 'Sent!' })
        await flushPromises()

        // The RecaptchaField stub getPayload returns { 'g-recaptcha-response': 'token' }
        // which satisfies the captcha check — POST should be called
        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(axiosMock.history.post.some(r => r.url.includes('contact-us'))).toBe(true)
    })

    it('onMobileInput strips non-digit characters', () => {
        wrapper.vm.onMobileInput('+1 (555) 555-5555')
        expect(wrapper.vm.form.mobile).toMatch(/^\d+$/)
    })

    it('onCountryChange sets the country_code with leading plus', () => {
        wrapper.vm.onCountryChange({ dialCode: '44' })
        expect(wrapper.vm.form.country_code).toBe('+44')
    })
})
