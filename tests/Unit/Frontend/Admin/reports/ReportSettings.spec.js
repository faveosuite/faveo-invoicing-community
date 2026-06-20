jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ReportSettings from '@/pages/admin/reports/ReportSettings.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'SelectField', 'AppModal', 'ImageUpload', 'PhoneField',
    'RecaptchaProvider', 'RecaptchaCheckbox', 'RecaptchaV2Invisible', 'RecaptchaV3',
    'ZohoCard', 'spinner-loader',
]

describe('ReportSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/reports\/setting/).reply(200, { data: { records: 3000 } })
        global.mockHttp.onPatch(/\/reports\/setting/).reply(200, { data: { message: 'Saved' } })
        wrapper = mount(ReportSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches report settings on mount', async () => {
        await flushPromises()
        const getCalls = global.mockHttp.history.get.filter(r => /\/reports\/setting/.test(r.url))
        expect(getCalls.length).toBeGreaterThan(0)
    })

    it('populates form.records from API response', async () => {
        await flushPromises()
        expect(wrapper.vm.form.records).toBe(3000)
    })

    it('sends PATCH /reports/setting on submit', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        const patchCalls = global.mockHttp.history.patch.filter(r => /\/reports\/setting/.test(r.url))
        expect(patchCalls.length).toBeGreaterThan(0)
    })

    it('calls successHandler after successful submit', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on submit failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/reports\/setting/).reply(200, { data: { records: 3000 } })
        global.mockHttp.onPatch(/\/reports\/setting/).reply(500)
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('saving flag is false by default', () => {
        expect(wrapper.vm.saving).toBe(false)
    })
})
