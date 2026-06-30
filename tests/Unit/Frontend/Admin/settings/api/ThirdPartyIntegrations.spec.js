jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ThirdPartyIntegrations from '@/pages/admin/settings/api/ThirdPartyIntegrations.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'SelectField', 'AppModal', 'ImageUpload', 'PhoneField',
    'RecaptchaProvider', 'RecaptchaCheckbox', 'RecaptchaV2Invisible', 'RecaptchaV3',
    'ZohoCard', 'spinner-loader', 'router-link',
]

describe('ThirdPartyIntegrations.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/module-settings/).reply(200, { data: [] })
        globalThis.mockHttp.onPost(/\/licenseStatus/).reply(200, { data: { message: 'Updated' } })
        wrapper = mount(ThirdPartyIntegrations, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
        wrapper.vm.dtRef = { refresh: jest.fn() }
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders a DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('DataTable is bound to /module-settings URL', () => {
        const dt = wrapper.find('data-table-stub')
        const urlAttr = dt.attributes('url') || dt.attributes(':url') || ''
        expect(urlAttr).toMatch(/module-settings/)
    })

    it('calls POST /licenseStatus on toggle(row)', async () => {
        await wrapper.vm.toggle({ key: 'mailchimp', is_active: false })
        await flushPromises()
        const postCalls = globalThis.mockHttp.history.post.filter(r => /\/licenseStatus/.test(r.url))
        expect(postCalls.length).toBeGreaterThan(0)
    })

    it('POST /licenseStatus payload contains module key', async () => {
        await wrapper.vm.toggle({ key: 'mailchimp', is_active: false })
        await flushPromises()
        const body = JSON.parse(globalThis.mockHttp.history.post[0].data)
        expect(body).toHaveProperty('mailchimp')
    })

    it('calls successHandler after successful toggle', async () => {
        await wrapper.vm.toggle({ key: 'mailchimp', is_active: false })
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on toggle failure', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/module-settings/).reply(200, { data: [] })
        globalThis.mockHttp.onPost(/\/licenseStatus/).reply(500)
        await wrapper.vm.toggle({ key: 'mailchimp', is_active: false })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('renders AppAlert stub', () => {
        expect(wrapper.find('app-alert-stub').exists()).toBe(true)
    })
})
