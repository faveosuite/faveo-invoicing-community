jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/client/profile.js', () => ({ passwordChangeSchema: {}, profileSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { validateForm } from '@/helpers/formUtils.js'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import ProfileIndex from '@/pages/client/profile/ProfileIndex.vue'

const profileFixture = {
    user: {
        id: 1,
        first_name: 'John',
        last_name: 'Doe',
        user_name: 'johndoe',
        email: 'john@example.com',
        company: 'ACME',
        mobile: '5551234567',
        mobile_code: '1',
        mobile_country_iso: 'US',
        address: '123 Main St',
        town: 'Springfield',
        country: 'US',
        state: 'IL',
        timezone_id: 1,
        timezone: { id: 1, name: 'UTC', timezone_name: 'UTC' },
        zipcode: '62701',
    },
}

const countriesFixture = [
    { code: 'US', name: 'United States' },
]

describe('ProfileIndex.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        validateForm.mockResolvedValue(true)

        axiosMock.onGet('/get-my-profile').reply(200, { data: profileFixture })
        axiosMock.onGet('/dependency/countries').reply(200, { data: { countries: countriesFixture } })
        axiosMock.onGet('/dependency/states').reply(200, { data: { states: [] } })

        wrapper = mount(ProfileIndex, {
            global: {
                plugins: [createTestingPinia({
                    initialState: {
                        auth: { user: { id: 1, first_name: 'John', email: 'john@example.com' }, isAuthenticated: true },
                    },
                })],
                stubs: [
                    'app-card',
                    'loader',
                    'client-field',
                    'select-field',
                    'dynamic-select',
                    'profile-image-upload',
                    'email-change-modal',
                    'mobile-change-modal',
                    'app-alert',
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

    it('calls GET /get-my-profile on mount', async () => {
        await flushPromises()

        const reqs = axiosMock.history.get.filter(r => r.url === '/get-my-profile')
        expect(reqs.length).toBeGreaterThan(0)
    })

    it('calls GET /dependency/countries on mount', async () => {
        await flushPromises()

        const reqs = axiosMock.history.get.filter(r => r.url === '/dependency/countries')
        expect(reqs.length).toBeGreaterThan(0)
    })

    it('populates form data after mount', async () => {
        await flushPromises()

        expect(wrapper.vm.form.first_name).toBe('John')
        expect(wrapper.vm.form.last_name).toBe('Doe')
        expect(wrapper.vm.form.email).toBe('john@example.com')
    })

    it('sets hasDataPopulated to true after mount', async () => {
        await flushPromises()

        expect(wrapper.vm.hasDataPopulated).toBe(true)
    })

    it('calls errorHandler when profile fetch fails', async () => {
        axiosMock.reset()
        axiosMock.onGet('/get-my-profile').reply(500, { message: 'Server error' })
        axiosMock.onGet('/dependency/countries').reply(200, { data: { countries: [] } })

        mount(ProfileIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-card', 'loader', 'client-field', 'select-field',
                    'dynamic-select', 'profile-image-upload', 'email-change-modal',
                    'mobile-change-modal', 'app-alert',
                ],
            },
        })
        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
    })

    it('calls validateForm on profile form submit', async () => {
        await flushPromises()
        axiosMock.onPost('/my-profile').reply(200, { data: {} })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(validateForm).toHaveBeenCalled()
    })

    it('does not call API when validation fails', async () => {
        await flushPromises()
        validateForm.mockResolvedValueOnce(false)

        let postCalled = false
        axiosMock.onPost('/my-profile').reply(() => {
            postCalled = true
            return [200, { data: {} }]
        })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(postCalled).toBe(false)
    })

    it('calls POST /my-profile on valid form submit', async () => {
        await flushPromises()
        axiosMock.onPost('/my-profile').reply(200, { data: {} })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        const reqs = axiosMock.history.post.filter(r => r.url === '/my-profile')
        expect(reqs.length).toBeGreaterThan(0)
    })

    it('calls successHandler on successful profile save', async () => {
        await flushPromises()
        axiosMock.onPost('/my-profile').reply(200, { data: {} })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on profile save server error', async () => {
        await flushPromises()
        axiosMock.onPost('/my-profile').reply(500, { message: 'Server error' })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
    })

    it('onEmailUpdated updates form.email', async () => {
        await flushPromises()

        wrapper.vm.onEmailUpdated('updated@example.com')

        expect(wrapper.vm.form.email).toBe('updated@example.com')
    })

    it('onMobileUpdated updates mobile fields', async () => {
        await flushPromises()

        wrapper.vm.onMobileUpdated({ mobile: '9876543210', mobile_code: '44', mobile_country_iso: 'GB' })

        expect(wrapper.vm.form.mobile).toBe('9876543210')
        expect(wrapper.vm.form.mobile_code).toBe('44')
        expect(wrapper.vm.form.mobile_country_iso).toBe('GB')
    })

    it('computed initials returns uppercase first letters', async () => {
        await flushPromises()

        expect(wrapper.vm.initials).toBe('JD')
    })

    it('onTimezoneChange sets form.timezone_id from val.id', async () => {
        await flushPromises()
        wrapper.vm.onTimezoneChange({ id: 'Asia/Kolkata' })
        expect(wrapper.vm.form.timezone_id).toBe('Asia/Kolkata')
    })

    it('onTimezoneChange sets empty string when val is null', async () => {
        await flushPromises()
        wrapper.vm.onTimezoneChange(null)
        expect(wrapper.vm.form.timezone_id).toBe('')
    })

    it('loadStates resolves without throwing for a given country', async () => {
        globalThis.mockHttp.onGet(/\/dependency\/states/).replyOnce(200, {
            data: { states: [{ iso2: 'MH', name: 'Maharashtra' }] }
        })
        await flushPromises()
        await expect(wrapper.vm.loadStates('IN')).resolves.not.toThrow()
    })

    it('loadStates returns early and clears states when code is empty', async () => {
        await flushPromises()
        await wrapper.vm.loadStates('')
        expect(wrapper.vm.states).toEqual([])
    })

    it('loadStates handles error gracefully', async () => {
        globalThis.mockHttp.onGet(/\/dependency\/states/).reply(500)
        await flushPromises()
        await expect(wrapper.vm.loadStates('IN')).resolves.not.toThrow()
    })

    it('onImageChange updates selectedImage and avatarPreview', async () => {
        await flushPromises()
        const mockFile = new File(['f'], 'photo.png', { type: 'image/png' })
        wrapper.vm.onImageChange({ file: mockFile, previewUrl: 'data:image/png;base64,abc' })
        expect(wrapper.vm.selectedImage).toBe(mockFile)
        expect(wrapper.vm.avatarPreview).toBe('data:image/png;base64,abc')
    })

    it('submitProfile handles 422 validation error', async () => {
        globalThis.mockHttp.onPost(/\/my-profile/).reply(422, {
            errors: { first_name: ['Required'] }
        })
        await flushPromises()
        await wrapper.vm.submitProfile()
        await flushPromises()
        expect(wrapper.vm.savingProfile).toBe(false)
    })
})
