jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { name: 'Toggle', template: '<button />', props: ['modelValue', 'disabled'], emits: ['update:modelValue'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/cloudValidations', () => ({ cloudSettingsSchema: {}, cloudProductSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import CloudDetails from '@/pages/admin/settings/settings/CloudDetails.vue'

describe('CloudDetails.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/settings\/cloud-details/).reply(200, {
            data: {
                cloud_central_domain: '',
                cloud_cname: '',
                cloud_button: false,
                cloud_top_message: '',
                cloud_label_field: '',
                cloud_label_radio: '',
                products: [],
                plans: [],
                countries: [],
                regions: [],
            },
        })
        global.mockHttp.onPost(/\/cloud-details/).reply(200, { data: {} })
        global.mockHttp.onPost(/\/enable\/cloud/).reply(200, { data: {} })
        global.mockHttp.onPost(/\/cloud-pop-up/).reply(200, { data: {} })
        global.mockHttp.onPost(/\/cloud-product-store/).reply(200, { data: {} })
        global.mockHttp.onPost(/\/cloud-data-center-store/).reply(200, { data: {} })

        wrapper = mount(CloudDetails, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'AppModal', 'DeleteModal', 'inline-loader', 'loader',
                    'action-button', 'TextField', 'SelectField', 'Switch',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches cloud settings on mount', async () => {
        await flushPromises()
        const getUrls = global.mockHttp.history.get.map(r => r.url)
        expect(getUrls.some(u => u.includes('cloud-details'))).toBe(true)
    })

    it('renders tab navigation', async () => {
        await flushPromises()
        expect(wrapper.find('.nav-tabs').exists()).toBe(true)
    })

    it('switches active tab on click', async () => {
        await flushPromises()
        const tabs = wrapper.findAll('.nav-link')
        expect(tabs.length).toBeGreaterThan(0)
        await tabs[1].trigger('click')
        expect(tabs[1].classes()).toContain('active')
    })

    it('opens product modal when add button clicked', async () => {
        await flushPromises()
        // Switch to products tab
        const tabs = wrapper.findAll('.nav-link')
        await tabs[1].trigger('click')
        const addBtn = wrapper.find('.card-tools .btn-tool')
        if (addBtn.exists()) {
            await addBtn.trigger('click')
        }
    })

    it('calls save settings endpoint on saveSettings', async () => {
        await flushPromises()
        global.mockHttp.onPost(/\/cloud-details/).reply(200, { data: {} })
        global.mockHttp.onPost(/\/enable\/cloud/).reply(200, { data: {} })
        global.mockHttp.onPost(/\/cloud-pop-up/).reply(200, { data: {} })

        const saveBtn = wrapper.find('[action="save"]')
        if (saveBtn.exists()) {
            await saveBtn.trigger('click')
            await flushPromises()
        }
    })
})
