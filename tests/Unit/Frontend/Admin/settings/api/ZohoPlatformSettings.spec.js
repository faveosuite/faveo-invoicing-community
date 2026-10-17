jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { platform: 'crm' }, query: {} }),
}))
jest.mock('vee-validate', () => ({
    useForm: () => ({ errors: {}, setErrors: jest.fn(), setFieldError: jest.fn() }),
}))
jest.mock('@/validations/admin/zohoValidations.js', () => ({ zohoCredentialsSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ZohoPlatformSettings from '@/pages/admin/settings/api/ZohoPlatformSettings.vue'
import { successHandler } from '@/helpers/responseHandler'

const INTEGRATIONS_RESPONSE = {
    data: [{ id: 5, platform: 'crm' }],
}

const KEYS_RESPONSE = {
    data: { client_id: 'cid123', client_secret: 'csecret456', region: 'us' },
}

const FIELDS_RESPONSE = {
    data: [{ id: 10, field_name: 'First Name' }],
}

describe('ZohoPlatformSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/zoho\/integrations/).reply(200, INTEGRATIONS_RESPONSE)
        globalThis.mockHttp.onGet(/\/zoho\/getKeys\//).reply(200, KEYS_RESPONSE)
        globalThis.mockHttp.onGet(/\/zoho\//).reply(200, FIELDS_RESPONSE)
        globalThis.mockHttp.onPost(/\/zoho\/saveKeys/).reply(200, { data: {} })
        globalThis.mockHttp.onPost(/\/zoho\/mapping\/save/).reply(200, { data: {} })
        wrapper = mount(ZohoPlatformSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
                    'DynamicSelect', 'TextField', 'StaticSelect', 'loader', 'ColumnSelector',
                    'Switch', 'DynamicSelect', 'ZohoCard', 'spinner-loader', 'CurrencyTableActions',
                ],
            },
        })
    })

    afterEach(() => {
        wrapper.unmount()
        globalThis.mockHttp.reset()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('calls GET /zoho/integrations on mount', async () => {
        await flushPromises()
        expect(
            globalThis.mockHttp.history.get.some(r => r.url.includes('/zoho/integrations'))
        ).toBe(true)
    })

    it('calls GET /zoho/getKeys/:id on mount when integrationId is set', async () => {
        await flushPromises()
        expect(
            globalThis.mockHttp.history.get.some(r => /\/zoho\/getKeys\//.test(r.url))
        ).toBe(true)
    })

    it('sets integrationId and populates form from fetched keys', async () => {
        await flushPromises()
        expect(wrapper.vm.integrationId).toBe(5)
        expect(wrapper.vm.form.client_id).toBe('cid123')
        expect(wrapper.vm.form.client_secret).toBe('csecret456')
        expect(wrapper.vm.form.region).toBe('us')
    })

    it('calls POST /zoho/saveKeys when saveConnection is invoked', async () => {
        await flushPromises()
        await wrapper.vm.saveConnection()
        await flushPromises()
        expect(
            globalThis.mockHttp.history.post.some(r => r.url.includes('/zoho/saveKeys'))
        ).toBe(true)
    })

    it('calls POST /zoho/mapping/save when saveMapping is invoked', async () => {
        await flushPromises()
        await wrapper.vm.saveMapping()
        await flushPromises()
        expect(
            globalThis.mockHttp.history.post.some(r => r.url.includes('/zoho/mapping/save'))
        ).toBe(true)
    })

    it('switches activeTab between connection and field-mapping', async () => {
        await flushPromises()
        expect(wrapper.vm.activeTab).toBe('connection')
        wrapper.vm.activeTab = 'field-mapping'
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.activeTab).toBe('field-mapping')
        wrapper.vm.activeTab = 'connection'
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.activeTab).toBe('connection')
    })

    it('does not call GET getKeys when integrationId is null', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/zoho\/integrations/).reply(200, { data: [] })
        globalThis.mockHttp.onGet(/\/zoho\//).reply(200, FIELDS_RESPONSE)

        const w = mount(ZohoPlatformSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
                    'DynamicSelect', 'TextField', 'StaticSelect', 'loader', 'ColumnSelector',
                    'Switch', 'DynamicSelect', 'ZohoCard', 'spinner-loader', 'CurrencyTableActions',
                ],
            },
        })
        await flushPromises()
        expect(
            globalThis.mockHttp.history.get.some(r => /\/zoho\/getKeys\//.test(r.url))
        ).toBe(false)
        w.unmount()
    })

    it('calls successHandler after saveConnection succeeds', async () => {
        await flushPromises()
        await wrapper.vm.saveConnection()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    // ── saveConnection error path ──────────────────────────────────────────
    it('saveConnection handles 500 error without throwing', async () => {
        await flushPromises()
        globalThis.mockHttp.onPost(/\/zoho\/saveKeys/).reply(500)
        await expect(wrapper.vm.saveConnection()).resolves.not.toThrow()
    })

    it('saveConnection redirects when redirect_url is present in response', async () => {
        await flushPromises()
        const fakeUrl = 'https://accounts.zoho.com/oauth/v2/auth?client_id=x'
        globalThis.mockHttp.onPost(/\/zoho\/saveKeys/).reply(200, { data: { redirect_url: fakeUrl } })
        // location.href is set via assignment — ensure the call completes cleanly
        await expect(wrapper.vm.saveConnection()).resolves.not.toThrow()
    })

    // ── validateForm returns false guard ───────────────────────────────────
    it('saveConnection does not POST when validateForm returns false', async () => {
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        await flushPromises()
        globalThis.mockHttp.reset()
        await wrapper.vm.saveConnection()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.length).toBe(0)
    })

    // ── saveMapping ────────────────────────────────────────────────────────
    it('saveMapping handles 500 error without throwing', async () => {
        await flushPromises()
        globalThis.mockHttp.onPost(/\/zoho\/mapping\/save/).reply(500)
        await expect(wrapper.vm.saveMapping()).resolves.not.toThrow()
    })

    it('saveMapping calls successHandler on success', async () => {
        await flushPromises()
        const { successHandler } = require('@/helpers/responseHandler')
        successHandler.mockClear()
        await wrapper.vm.saveMapping()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    // ── syncFields ─────────────────────────────────────────────────────────
    it('syncFields calls GET /zoho/:platform/sync', async () => {
        await flushPromises()
        globalThis.mockHttp.onGet(/\/zoho\/crm\/sync/).reply(200, { data: {} })
        globalThis.mockHttp.onGet(/\/zoho\//).reply(200, FIELDS_RESPONSE)
        await wrapper.vm.syncFields()
        await flushPromises()
        expect(
            globalThis.mockHttp.history.get.some(r => r.url.includes('/sync'))
        ).toBe(true)
    })

    it('syncFields handles 500 error without throwing', async () => {
        await flushPromises()
        globalThis.mockHttp.onGet(/\/zoho\/crm\/sync/).reply(500)
        await expect(wrapper.vm.syncFields()).resolves.not.toThrow()
    })

    it('syncFields sets syncing to false after completion', async () => {
        await flushPromises()
        globalThis.mockHttp.onGet(/\/zoho\/crm\/sync/).reply(200, { data: {} })
        globalThis.mockHttp.onGet(/\/zoho\//).reply(200, FIELDS_RESPONSE)
        const promise = wrapper.vm.syncFields()
        await promise
        await flushPromises()
        expect(wrapper.vm.syncing).toBe(false)
    })

    // ── switchModule ───────────────────────────────────────────────────────
    it('switchModule does nothing when same tab is selected (early return)', async () => {
        await flushPromises()
        const before = wrapper.vm.activeModule
        globalThis.mockHttp.reset()
        await wrapper.vm.switchModule(wrapper.vm.activeModule)
        await flushPromises()
        expect(wrapper.vm.activeModule).toBe(before)
        expect(globalThis.mockHttp.history.get.length).toBe(0)
    })

    it('switchModule changes activeModule and loads new mappings', async () => {
        await flushPromises()
        globalThis.mockHttp.onGet(/\/zoho\//).reply(200, FIELDS_RESPONSE)
        await wrapper.vm.switchModule('accounts')
        await flushPromises()
        expect(wrapper.vm.activeModule).toBe('accounts')
    })

    it('switchModule handles error and resets loadingModule', async () => {
        await flushPromises()
        globalThis.mockHttp.onGet(/\/zoho\//).reply(500)
        await expect(wrapper.vm.switchModule('accounts')).resolves.not.toThrow()
        expect(wrapper.vm.loadingModule).toBe(false)
    })

    // ── addRow / removeRow ─────────────────────────────────────────────────
    it('addRow appends a new empty row', async () => {
        await flushPromises()
        const before = wrapper.vm.rows.length
        wrapper.vm.addRow()
        expect(wrapper.vm.rows.length).toBe(before + 1)
        const last = wrapper.vm.rows[wrapper.vm.rows.length - 1]
        expect(last.zohoId).toBeNull()
        expect(last.targetValue).toBeNull()
    })

    it('removeRow removes the row at the given index', async () => {
        await flushPromises()
        wrapper.vm.addRow()
        wrapper.vm.addRow()
        const before = wrapper.vm.rows.length
        wrapper.vm.removeRow(before - 1)
        expect(wrapper.vm.rows.length).toBe(before - 1)
    })

    // ── onTargetChange ─────────────────────────────────────────────────────
    it('onTargetChange updates targetValue and targetType', async () => {
        await flushPromises()
        wrapper.vm.addRow()
        const row = wrapper.vm.rows[wrapper.vm.rows.length - 1]
        wrapper.vm.onTargetChange(row, { id: 'f1', type: 'string' })
        expect(row.targetValue).toBe('f1')
        expect(row.targetType).toBe('string')
    })

    it('onTargetChange clears when val is null', async () => {
        await flushPromises()
        wrapper.vm.addRow()
        const row = wrapper.vm.rows[wrapper.vm.rows.length - 1]
        wrapper.vm.onTargetChange(row, null)
        expect(row.targetValue).toBeNull()
        expect(row.targetType).toBe('')
    })

    // ── onZohoFieldChange ──────────────────────────────────────────────────
    it('onZohoFieldChange fetches options when zohoId is set', async () => {
        await flushPromises()
        // Reset and re-register so the options route is checked before the generic /zoho/ catch-all
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/zoho\/options\//).reply(200, [{ value: 'v1', label: 'Option 1', type: 'string' }])
        globalThis.mockHttp.onGet(/\/zoho\//).reply(200, FIELDS_RESPONSE)
        wrapper.vm.addRow()
        const row = wrapper.vm.rows[wrapper.vm.rows.length - 1]
        await wrapper.vm.onZohoFieldChange(row, { id: 42 })
        await flushPromises()
        expect(row.zohoId).toBe(42)
        expect(row.targetOptions.length).toBeGreaterThan(0)
    })

    it('onZohoFieldChange clears targetOptions when val is null', async () => {
        await flushPromises()
        wrapper.vm.addRow()
        const row = wrapper.vm.rows[wrapper.vm.rows.length - 1]
        row.targetOptions = [{ id: 'x', name: 'X' }]
        await wrapper.vm.onZohoFieldChange(row, null)
        expect(row.zohoId).toBeNull()
        expect(row.targetOptions).toEqual([])
    })

    // ── mount error path ───────────────────────────────────────────────────
    it('handles GET /zoho/integrations error on mount without throwing', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/zoho\/integrations/).reply(500)
        const w = mount(ZohoPlatformSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
                    'DynamicSelect', 'TextField', 'StaticSelect', 'loader', 'ColumnSelector',
                    'Switch', 'DynamicSelect', 'ZohoCard', 'spinner-loader', 'CurrencyTableActions',
                ],
            },
        })
        await flushPromises()
        expect(w.vm.loading).toBe(false)
        w.unmount()
    })
})
