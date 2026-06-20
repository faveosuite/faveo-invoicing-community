jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { name: 'Toggle', template: '<button />', props: ['modelValue', 'disabled'], emits: ['update:modelValue'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/systemSettingsValidations', () => ({
    systemSettingsSchema: {},
    buildFileStorageSchema: jest.fn(() => ({ validateSync: jest.fn() })),
    pdfSettingsSchema: { validateSync: jest.fn() },
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import CompanySettings from '@/pages/admin/settings/settings/CompanySettings.vue'

describe('CompanySettings.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/settings\/system-data/).reply(200, {
            data: {
                settings: {
                    company: 'Test Co',
                    company_email: 'test@test.com',
                    website: 'https://test.com',
                    phone: '1234567890',
                    address: '123 Main St',
                    country: 'US',
                    state: 'CA',
                    default_currency: 'USD',
                    language: 'en',
                    autorenewal_status: false,
                    fav_icon: '',
                    admin_logo: '',
                    logo: '',
                },
                countries: [],
                states: [],
                currencies: [],
                languages: [],
            },
        })
        global.mockHttp.onPost(/\/settings\/system-data/).reply(200, { data: {} })

        wrapper = mount(CompanySettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'inline-loader', 'loader', 'action-button',
                    'TextField', 'SelectField', 'PhoneField', 'Switch', 'ImageUpload',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches system data on mount', async () => {
        await flushPromises()
        const getUrls = global.mockHttp.history.get.map(r => r.url)
        expect(getUrls.some(u => u.includes('system-data'))).toBe(true)
    })

    it('renders the card header', async () => {
        await flushPromises()
        expect(wrapper.find('.card-header').exists()).toBe(true)
    })

    it('calls save endpoint on form submit', async () => {
        await flushPromises()
        const saveBtn = wrapper.find('[action="save"]')
        if (saveBtn.exists()) {
            await saveBtn.trigger('click')
            await flushPromises()
            const postUrls = global.mockHttp.history.post.map(r => r.url)
            expect(postUrls.some(u => u.includes('system-data'))).toBe(true)
        }
    })
})
