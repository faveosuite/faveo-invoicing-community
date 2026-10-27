jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { name: 'Toggle', template: '<button />', props: ['modelValue', 'disabled'], emits: ['update:modelValue'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ContactOptions from '@/pages/admin/settings/settings/ContactOptions.vue'

describe('ContactOptions.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/contact-option/).reply(200, {
            data: {
                emailverification_status: 1,
                msg91_status: 0,
                verification_preference: 'email',
            },
        })
        globalThis.mockHttp.onPost(/\/verificationSettings/).reply(200, { data: {} })

        wrapper = mount(ContactOptions, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'inline-loader', 'loader', 'action-button',
                    'Switch', 'DynamicSelect',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches contact options on mount', async () => {
        await flushPromises()
        const getUrls = globalThis.mockHttp.history.get.map(r => r.url)
        expect(getUrls.some(u => u.includes('contact-option'))).toBe(true)
    })

    it('renders the card header', async () => {
        await flushPromises()
        expect(wrapper.find('.card-header').exists()).toBe(true)
    })

    it('calls verification settings endpoint on submit', async () => {
        await flushPromises()
        const saveBtn = wrapper.find('[action="save"]')
        if (saveBtn.exists()) {
            await saveBtn.trigger('click')
            await flushPromises()
            const postUrls = globalThis.mockHttp.history.post.map(r => r.url)
            expect(postUrls.some(u => u.includes('verificationSettings'))).toBe(true)
        }
    })

    it('greys Mobile out and keeps its saved value while MSG91 is off', async () => {
        await flushPromises()   // fixture: msg91_status 0
        const mobile = wrapper.findAll('switch-stub').find(s => s.attributes('name') === 'mobile_enabled')
        expect(mobile.attributes('disabled')).toBe('true')

        await wrapper.vm.submit()
        await flushPromises()
        const body = JSON.parse(globalThis.mockHttp.history.post.at(-1).data)
        expect(body).not.toHaveProperty('mobile_enabled')
    })

    it('lets Mobile be switched and sends it while MSG91 is active', async () => {
        globalThis.mockHttp.onGet(/\/contact-option/).reply(200, {
            data: { emailverification_status: 1, msg91_status: 1, mobileverification_status: 1, verification_preference: 'email' },
        })
        const w = mount(ContactOptions, {
            global: { plugins: [createTestingPinia()], stubs: ['AppAlert', 'inline-loader', 'loader', 'action-button', 'Switch', 'DynamicSelect'] },
        })
        await flushPromises()
        const mobile = w.findAll('switch-stub').find(s => s.attributes('name') === 'mobile_enabled')
        expect(mobile.attributes('disabled')).toBe('false')

        await w.vm.submit()
        await flushPromises()
        const body = JSON.parse(globalThis.mockHttp.history.post.at(-1).data)
        expect(body.mobile_enabled).toBe(1)
    })
})
