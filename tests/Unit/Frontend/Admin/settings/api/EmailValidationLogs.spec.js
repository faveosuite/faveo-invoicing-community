jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import EmailValidationLogs from '@/pages/admin/settings/api/EmailValidationLogs.vue'
import { errorHandler } from '@/helpers/responseHandler'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'SelectField', 'AppModal', 'ImageUpload', 'PhoneField',
    'RecaptchaProvider', 'RecaptchaCheckbox', 'RecaptchaV2Invisible', 'RecaptchaV3',
    'ZohoCard', 'spinner-loader',
]

describe('EmailValidationLogs.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/settings\/email-validation-logs/).reply(200, { data: [] })
        globalThis.mockHttp.onGet(/\/get-email-validation-results/).reply(200, {
            data: {
                email: 'test@example.com',
                method: 'reoon',
                status: 'safe',
                details: {},
            },
        })
        wrapper = mount(EmailValidationLogs, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders a DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('showDetailModal is false by default', () => {
        expect(wrapper.vm.showDetailModal).toBe(false)
    })

    it('openDetail fetches GET /get-email-validation-results with id', async () => {
        await wrapper.vm.openDetail(99)
        await flushPromises()
        const getCalls = globalThis.mockHttp.history.get.filter(r => /get-email-validation-results/.test(r.url))
        expect(getCalls.length).toBeGreaterThan(0)
        expect(getCalls[0].params?.id).toBe(99)
    })

    it('openDetail sets showDetailModal to true', async () => {
        await wrapper.vm.openDetail(1)
        await flushPromises()
        expect(wrapper.vm.showDetailModal).toBe(true)
    })

    it('openDetail populates detailData from response', async () => {
        await wrapper.vm.openDetail(1)
        await flushPromises()
        expect(wrapper.vm.detailData).not.toBeNull()
        expect(wrapper.vm.detailData.email).toBe('test@example.com')
    })

    it('calls errorHandler when detail fetch fails', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/get-email-validation-results/).reply(500)
        await wrapper.vm.openDetail(1)
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('loadingDetail starts false', () => {
        expect(wrapper.vm.loadingDetail).toBe(false)
    })
})
