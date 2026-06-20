jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { errorHandler } from '@/helpers/responseHandler'
import EmailChangeModal from '@/pages/client/profile/components/EmailChangeModal.vue'

describe('EmailChangeModal.vue', () => {
    let wrapper
    let axiosMock

    function createWrapper(propsData = {}) {
        return mount(EmailChangeModal, {
            props: {
                show: true,
                currentEmail: 'john@example.com',
                ...propsData,
            },
            global: {
                plugins: [createTestingPinia({
                    initialState: {
                        auth: { user: { id: 1, first_name: 'John', email: 'john@example.com' }, isAuthenticated: true },
                    },
                })],
                stubs: ['modal', 'app-alert', 'client-field', 'action-button'],
            },
        })
    }

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        wrapper = createWrapper()
    })

    afterEach(() => {
        axiosMock.restore()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('receives show prop', () => {
        expect(wrapper.props('show')).toBe(true)
    })

    it('receives currentEmail prop', () => {
        expect(wrapper.props('currentEmail')).toBe('john@example.com')
    })

    it('renders with show=false without error', () => {
        const w = createWrapper({ show: false })
        expect(w.exists()).toBeTruthy()
    })

    it('calls POST /profile/email/send-otp on submitEmail with valid new email', async () => {
        axiosMock.onPost('/profile/email/send-otp').reply(200, {
            data: { email_updated: false },
        })

        // Access the component's internal state via vm
        wrapper.vm.newEmail = 'new@example.com'
        await wrapper.vm.submitEmail()
        await flushPromises()

        const reqs = axiosMock.history.post.filter(r => r.url === '/profile/email/send-otp')
        expect(reqs.length).toBeGreaterThan(0)
    })

    it('sets emailError when submitting same email', async () => {
        wrapper.vm.newEmail = 'john@example.com'
        await wrapper.vm.submitEmail()
        await flushPromises()

        expect(wrapper.vm.emailError).toBeTruthy()
    })

    it('sets emailError when email format is invalid', async () => {
        wrapper.vm.newEmail = 'not-an-email'
        await wrapper.vm.submitEmail()
        await flushPromises()

        expect(wrapper.vm.emailError).toBeTruthy()
    })

    it('transitions to verify_old step after send-otp when verification required', async () => {
        axiosMock.onPost('/profile/email/send-otp').reply(200, {
            data: { email_updated: false },
        })

        wrapper.vm.newEmail = 'new@example.com'
        await wrapper.vm.submitEmail()
        await flushPromises()

        expect(wrapper.vm.step).toBe('verify_old')
    })

    it('emits updated event when email_updated=true on send-otp', async () => {
        axiosMock.onPost('/profile/email/send-otp').reply(200, {
            data: { email_updated: true, email: 'new@example.com' },
        })

        wrapper.vm.newEmail = 'new@example.com'
        await wrapper.vm.submitEmail()
        await flushPromises()

        expect(wrapper.emitted('updated')).toBeTruthy()
        expect(wrapper.emitted('updated')[0][0]).toBe('new@example.com')
    })

    it('calls errorHandler on send-otp server error', async () => {
        axiosMock.onPost('/profile/email/send-otp').reply(500, { message: 'Error' })

        wrapper.vm.newEmail = 'new@example.com'
        await wrapper.vm.submitEmail()
        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
    })

    it('calls POST /profile/email/verify-otp on submitOtp in verify_old step', async () => {
        axiosMock.onPost('/profile/email/verify-otp').reply(200, { data: {} })
        axiosMock.onPost('/profile/email/send-otp').reply(200, { data: {} })

        wrapper.vm.step = 'verify_old'
        wrapper.vm.otp = '123456'
        await wrapper.vm.submitOtp()
        await flushPromises()

        const reqs = axiosMock.history.post.filter(r => r.url === '/profile/email/verify-otp')
        expect(reqs.length).toBeGreaterThan(0)
    })

    it('emits update:show false when close() is called', async () => {
        wrapper.vm.close()
        await flushPromises()

        expect(wrapper.emitted('update:show')).toBeTruthy()
        expect(wrapper.emitted('update:show')[0][0]).toBe(false)
    })

    it('calls POST /profile/resend-otp on resend when cooldown is 0', async () => {
        axiosMock.onPost('/profile/resend-otp').reply(200, { data: {} })

        wrapper.vm.step = 'verify_old'
        wrapper.vm.cooldown = 0
        await wrapper.vm.resend()
        await flushPromises()

        const reqs = axiosMock.history.post.filter(r => r.url === '/profile/resend-otp')
        expect(reqs.length).toBeGreaterThan(0)
    })

    it('does not call resend API when cooldown is active', async () => {
        let postCalled = false
        axiosMock.onPost('/profile/resend-otp').reply(() => {
            postCalled = true
            return [200, { data: {} }]
        })

        wrapper.vm.cooldown = 60
        await wrapper.vm.resend()
        await flushPromises()

        expect(postCalled).toBe(false)
    })
})
