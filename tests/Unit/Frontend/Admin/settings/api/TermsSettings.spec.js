jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('@/validations/admin/termsValidations', () => ({
    termsSchema: { validate: jest.fn(() => Promise.resolve(true)) },
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import TermsSettings from '@/pages/admin/settings/api/TermsSettings.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'SelectField', 'AppModal', 'ImageUpload', 'PhoneField',
    'RecaptchaProvider', 'RecaptchaCheckbox', 'RecaptchaV2Invisible', 'RecaptchaV3',
    'ZohoCard', 'spinner-loader',
]

describe('TermsSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/settings\/terms/).reply(200, {
            data: { terms_url: 'https://example.com/terms' },
        })
        global.mockHttp.onPost(/\/updateTermsDetails/).reply(200, { data: { message: 'Saved' } })
        wrapper = mount(TermsSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches settings on mount via GET /settings/terms', async () => {
        await flushPromises()
        const getCalls = global.mockHttp.history.get.filter(r => /\/settings\/terms/.test(r.url))
        expect(getCalls.length).toBeGreaterThan(0)
    })

    it('populates form.terms_url from API response', async () => {
        await flushPromises()
        expect(wrapper.vm.form.terms_url).toBe('https://example.com/terms')
    })

    it('sends POST /updateTermsDetails on save()', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        const postCalls = global.mockHttp.history.post.filter(r => /\/updateTermsDetails/.test(r.url))
        expect(postCalls.length).toBeGreaterThan(0)
    })

    it('POST payload includes status: 1', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        const body = JSON.parse(global.mockHttp.history.post[0].data)
        expect(body.status).toBe(1)
    })

    it('calls successHandler after successful save', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on save failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/settings\/terms/).reply(200, { data: {} })
        global.mockHttp.onPost(/\/updateTermsDetails/).reply(500)
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('loading is false after mount completes', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })
})
