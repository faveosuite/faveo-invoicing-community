jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { platform: 'crm', module: 'contacts' }, query: {} }),
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ZohoMapping from '@/pages/admin/settings/api/ZohoMapping.vue'
const INTEGRATIONS_RESPONSE = {
    data: [{ id: 1, platform: 'crm' }],
}

const FIELDS_RESPONSE = {
    data: [{ id: 10, field_name: 'First Name' }, { id: 11, field_name: 'Last Name' }],
}

describe('ZohoMapping.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/zoho\/integrations/).reply(200, INTEGRATIONS_RESPONSE)
        globalThis.mockHttp.onGet(/\/zoho\//).reply(200, FIELDS_RESPONSE)
        globalThis.mockHttp.onPost(/\/zoho\/mapping\/save/).reply(200, { data: {} })
        wrapper = mount(ZohoMapping, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
                    'DynamicSelect', 'TextField', 'StaticSelect', 'loader', 'ColumnSelector',
                    'Switch', 'SelectField', 'ZohoCard', 'spinner-loader', 'CurrencyTableActions',
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

    it('calls GET fields and mapping endpoints on mount', async () => {
        await flushPromises()
        const zohoUrls = globalThis.mockHttp.history.get.filter(r => /\/zoho\//.test(r.url))
        expect(zohoUrls.length).toBeGreaterThanOrEqual(2)
        expect(zohoUrls.some(r => r.url.includes('/fields'))).toBe(true)
        expect(zohoUrls.some(r => r.url.includes('/mapping/data'))).toBe(true)
    })

    it('sets integrationId after fetching integrations', async () => {
        await flushPromises()
        expect(wrapper.vm.integrationId).toBe(1)
    })

    it('calls POST /zoho/mapping/save when save is invoked', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(
            globalThis.mockHttp.history.post.some(r => r.url.includes('/zoho/mapping/save'))
        ).toBe(true)
    })

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

    it('switchTab calls loadMappings for the new tab', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/zoho\//).reply(200, FIELDS_RESPONSE)
        await wrapper.vm.switchTab('accounts')
        await flushPromises()
        expect(wrapper.vm.activeModule).toBe('accounts')
        const zohoUrls = globalThis.mockHttp.history.get.filter(r => /\/zoho\//.test(r.url))
        expect(zohoUrls.length).toBeGreaterThanOrEqual(2)
    })

    // ── switchTab early-return guard ───────────────────────────────────────
    it('switchTab does nothing when same tab is already active', async () => {
        await flushPromises()
        const before = wrapper.vm.activeModule
        globalThis.mockHttp.reset()
        await wrapper.vm.switchTab(before)
        await flushPromises()
        expect(wrapper.vm.activeModule).toBe(before)
        expect(globalThis.mockHttp.history.get.length).toBe(0)
    })

    it('switchTab resets loadingModule to false on error', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/zoho\//).reply(500)
        await expect(wrapper.vm.switchTab('accounts')).resolves.not.toThrow()
        expect(wrapper.vm.loadingModule).toBe(false)
    })

    // ── save error path ────────────────────────────────────────────────────
    it('save handles 500 error without throwing', async () => {
        await flushPromises()
        globalThis.mockHttp.onPost(/\/zoho\/mapping\/save/).reply(500)
        await expect(wrapper.vm.save()).resolves.not.toThrow()
    })

    it('save calls successHandler on success', async () => {
        const { successHandler } = require('@/helpers/responseHandler')
        await flushPromises()
        successHandler.mockClear()
        await wrapper.vm.save()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    // ── syncFields ─────────────────────────────────────────────────────────
    it('syncFields calls GET /zoho/:platform/sync', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
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
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/zoho\/crm\/sync/).reply(500)
        await expect(wrapper.vm.syncFields()).resolves.not.toThrow()
    })

    it('syncFields sets syncing to false after completion', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/zoho\/crm\/sync/).reply(200, { data: {} })
        globalThis.mockHttp.onGet(/\/zoho\//).reply(200, FIELDS_RESPONSE)
        await wrapper.vm.syncFields()
        await flushPromises()
        expect(wrapper.vm.syncing).toBe(false)
    })

    // ── onTargetChange ─────────────────────────────────────────────────────
    it('onTargetChange updates targetValue and targetType', async () => {
        await flushPromises()
        wrapper.vm.addRow()
        const row = wrapper.vm.rows[wrapper.vm.rows.length - 1]
        wrapper.vm.onTargetChange(row, { id: 'field_x', type: 'string' })
        expect(row.targetValue).toBe('field_x')
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
        globalThis.mockHttp.onGet(/\/zoho\/options\//).reply(200, [
            { value: 'v1', label: 'Option 1', type: 'string' },
        ])
        globalThis.mockHttp.onGet(/\/zoho\//).reply(200, FIELDS_RESPONSE)
        wrapper.vm.addRow()
        const row = wrapper.vm.rows[wrapper.vm.rows.length - 1]
        await wrapper.vm.onZohoFieldChange(row, { id: 10 })
        await flushPromises()
        expect(row.zohoId).toBe(10)
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
        const w = mount(ZohoMapping, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
                    'DynamicSelect', 'TextField', 'StaticSelect', 'loader', 'ColumnSelector',
                    'Switch', 'SelectField', 'ZohoCard', 'spinner-loader', 'CurrencyTableActions',
                ],
            },
        })
        await flushPromises()
        expect(w.vm.loading).toBe(false)
        w.unmount()
    })
})
