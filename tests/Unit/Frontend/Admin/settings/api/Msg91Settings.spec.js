jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('@/validations/admin/msg91Validations', () => ({
    msg91Schema: { validate: jest.fn(() => Promise.resolve(true)) },
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import Msg91Settings from '@/pages/admin/settings/api/Msg91Settings.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'SelectField', 'AppModal', 'ImageUpload', 'PhoneField',
    'RecaptchaProvider', 'RecaptchaCheckbox', 'RecaptchaV2Invisible', 'RecaptchaV3',
    'ZohoCard', 'spinner-loader',
]

describe('Msg91Settings.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/settings\/msg91/).reply(200, {
            data: {
                msg91_auth_key: 'authkey',
                msg91_sender: 'SENDER',
                msg91_template_id: 'tmpl',
                third_party_id: null,
                apps: [],
            },
        })
        global.mockHttp.onPost(/\/updatemobileDetails/).reply(200, { data: { message: 'Saved' } })
        wrapper = mount(Msg91Settings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches settings on mount via GET /settings/msg91', async () => {
        await flushPromises()
        const getCalls = global.mockHttp.history.get.filter(r => /\/settings\/msg91/.test(r.url))
        expect(getCalls.length).toBeGreaterThan(0)
    })

    it('populates form from API response', async () => {
        await flushPromises()
        expect(wrapper.vm.form.msg91_auth_key).toBe('authkey')
        expect(wrapper.vm.form.msg91_sender).toBe('SENDER')
    })

    it('sends POST /updatemobileDetails on save()', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        const postCalls = global.mockHttp.history.post.filter(r => /\/updatemobileDetails/.test(r.url))
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
        global.mockHttp.onGet(/\/settings\/msg91/).reply(200, { data: {} })
        global.mockHttp.onPost(/\/updatemobileDetails/).reply(500)
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
