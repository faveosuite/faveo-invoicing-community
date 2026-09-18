jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import SettingsIndex from '@/pages/admin/settings/SettingsIndex.vue'

describe('SettingsIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/settings\/index-data/).reply(200, {
            data: {
                is_redis_configured: false,
                is_debug_mode: false,
                is_pulse_enabled: false,
                is_clockwork_enabled: false,
                is_mail_sending_enabled: false,
                is_msg91_enabled: false,
                is_pipedrive_enabled: false,
                is_recaptcha_enabled: false,
                is_email_validation_enabled: false,
            },
        })
        wrapper = mount(SettingsIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['router-link', 'router-view'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches index-data on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.get[0].url).toMatch(/\/settings\/index-data/)
    })

    it('renders settings sections', async () => {
        await flushPromises()
        const cards = wrapper.findAll('.card')
        expect(cards.length).toBeGreaterThan(0)
    })

    it('modal is hidden by default', () => {
        expect(wrapper.find('.modal.show').exists()).toBeFalsy()
    })
})
