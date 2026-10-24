jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { errorHandler } from '@/helpers/responseHandler'
import MobileChangeModal from '@/pages/client/profile/components/MobileChangeModal.vue'

describe('MobileChangeModal.vue', () => {
    let wrapper
    let axiosMock

    function createWrapper(propsData = {}) {
        return mount(MobileChangeModal, {
            props: {
                show: true,
                currentEmail: 'john@example.com',
                currentCode: '1',
                currentIso: 'US',
                ...propsData,
            },
            global: {
                plugins: [createTestingPinia({
                    initialState: {
                        auth: { user: { id: 1, first_name: 'John', email: 'john@example.com' }, isAuthenticated: true },
                    },
                })],
                stubs: ['modal', 'app-alert', 'client-field', 'action-button', 'phone-field'],
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

    it('receives currentCode prop', () => {
        expect(wrapper.props('currentCode')).toBe('1')
    })

    it('receives currentIso prop', () => {
        expect(wrapper.props('currentIso')).toBe('US')
    })

    it('renders with show=false without error', () => {
        const w = createWrapper({ show: false })
        expect(w.exists()).toBeTruthy()
    })

    it('sets mobileError when submitting with empty mobile', async () => {
        wrapper.vm.newMobile = ''
        await wrapper.vm.submitMobile()
        await flushPromises()

        expect(wrapper.vm.mobileError).toBeTruthy()
    })

    it('calls POST /profile/mobile/send-otp on submitMobile with valid mobile', async () => {
        axiosMock.onPost('/profile/mobile/send-otp').reply(200, {
            data: { mobile_updated: false },
        })

        wrapper.vm.newMobile = '5551234567'
        wrapper.vm.dialCode = '1'
        await wrapper.vm.submitMobile()
        await flushPromises()

        const reqs = axiosMock.history.post.filter(r => r.url === '/profile/mobile/send-otp')
        expect(reqs.length).toBeGreaterThan(0)
    })

    it('transitions to verify_mobile step after send-otp when verification required', async () => {
        axiosMock.onPost('/profile/mobile/send-otp').reply(200, {
            data: { mobile_updated: false },
        })

        wrapper.vm.newMobile = '5551234567'
        await wrapper.vm.submitMobile()
        await flushPromises()

        expect(wrapper.vm.step).toBe('verify_mobile')
    })

    it('emits updated event when mobile_updated=true on send-otp', async () => {
        axiosMock.onPost('/profile/mobile/send-otp').reply(200, {
            data: { mobile_updated: true, mobile: '5551234567', mobile_code: '1' },
        })

        wrapper.vm.newMobile = '5551234567'
        await wrapper.vm.submitMobile()
        await flushPromises()

        expect(wrapper.emitted('updated')).toBeTruthy()
    })

    it('calls errorHandler on send-otp server error', async () => {
        axiosMock.onPost('/profile/mobile/send-otp').reply(500, { message: 'Error' })

        wrapper.vm.newMobile = '5551234567'
        await wrapper.vm.submitMobile()
        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
    })

    it('calls POST /profile/mobile/verify-otp on submitOtp in verify_mobile step', async () => {
        axiosMock.onPost('/profile/mobile/verify-otp').reply(200, {
            data: { mobile_updated: true, mobile: '5551234567', mobile_code: '1' },
        })

        wrapper.vm.step = 'verify_mobile'
        wrapper.vm.otp = '123456'
        wrapper.vm.newMobile = '5551234567'
        await wrapper.vm.submitOtp()
        await flushPromises()

        const reqs = axiosMock.history.post.filter(r => r.url === '/profile/mobile/verify-otp')
        expect(reqs.length).toBeGreaterThan(0)
    })

    it('calls POST /profile/email/verify-otp on submitOtp in verify_mobile_email step', async () => {
        axiosMock.onPost('/profile/email/verify-otp').reply(200, {
            data: { mobile: '5551234567', mobile_code: '1' },
        })

        wrapper.vm.step = 'verify_mobile_email'
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

        wrapper.vm.step = 'verify_mobile'
        wrapper.vm.cooldown = 0
        wrapper.vm.newMobile = '5551234567'
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

    it('onMobileInput strips non-digit characters', () => {
        wrapper.vm.onMobileInput('555-123-4567')
        expect(wrapper.vm.newMobile).toBe('5551234567')
    })

    it('onCountryChange updates countryIso and dialCode', () => {
        wrapper.vm.onCountryChange({ iso: 'GB', dialCode: '44' })
        expect(wrapper.vm.countryIso).toBe('GB')
        expect(wrapper.vm.dialCode).toBe('44')
    })
})
