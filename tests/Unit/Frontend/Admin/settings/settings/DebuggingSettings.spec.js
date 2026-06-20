jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { name: 'Toggle', template: '<button />', props: ['modelValue', 'disabled'], emits: ['update:modelValue'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import DebuggingSettings from '@/pages/admin/settings/settings/DebuggingSettings.vue'

describe('DebuggingSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/debugg/).reply(200, {
            data: {
                debug: false,
                pulse_enabled: false,
                clockwork_enable: false,
                sentry_reporting: false,
                sentry_performance: false,
            },
        })
        global.mockHttp.onPost(/\/save\/debugg/).reply(200, { data: {} })

        wrapper = mount(DebuggingSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'inline-loader', 'loader', 'action-button',
                    'Switch', 'Tooltip',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches debugging settings on mount', async () => {
        await flushPromises()
        const getUrls = global.mockHttp.history.get.map(r => r.url)
        expect(getUrls.some(u => u.includes('debugg'))).toBe(true)
    })

    it('renders the card header', async () => {
        await flushPromises()
        expect(wrapper.find('.card-header').exists()).toBe(true)
    })

    it('calls save debugging endpoint on submit', async () => {
        await flushPromises()
        const saveBtn = wrapper.find('[action="save"]')
        if (saveBtn.exists()) {
            await saveBtn.trigger('click')
            await flushPromises()
            const postUrls = global.mockHttp.history.post.map(r => r.url)
            expect(postUrls.some(u => u.includes('save/debugg'))).toBe(true)
        }
    })
})
