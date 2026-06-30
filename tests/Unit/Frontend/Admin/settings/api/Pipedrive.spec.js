jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('@/validations/admin/pipedriveValidations', () => ({
    apiKeySchema: { validate: jest.fn(() => Promise.resolve(true)) },
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import Pipedrive from '@/pages/admin/settings/api/Pipedrive.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'SelectField', 'AppModal', 'ImageUpload', 'PhoneField',
    'RecaptchaProvider', 'RecaptchaCheckbox', 'RecaptchaV2Invisible', 'RecaptchaV3',
    'ZohoCard', 'spinner-loader',
]

describe('Pipedrive.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/settings\/pipedrive/).reply(200, {
            data: {
                pipedrive_key: 'pd-key',
                require_pipedrive_user_verification: false,
                groups: {},
            },
        })
        globalThis.mockHttp.onPost(/\/updatepipedriveDetails/).reply(200, { data: { message: 'Connected' } })
        wrapper = mount(Pipedrive, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches settings on mount via GET /settings/pipedrive', async () => {
        await flushPromises()
        const getCalls = globalThis.mockHttp.history.get.filter(r => /\/settings\/pipedrive/.test(r.url))
        expect(getCalls.length).toBeGreaterThan(0)
    })

    it('populates form from API response', async () => {
        await flushPromises()
        expect(wrapper.vm.form.apiKey).toBe('pd-key')
    })

    it('connectionStatus is connected after mount when a key is present', async () => {
        await flushPromises()
        expect(wrapper.vm.connectionStatus).toBe('connected')
    })

    it('sends POST /updatepipedriveDetails on connect()', async () => {
        await flushPromises()
        await wrapper.vm.connect()
        await flushPromises()
        const postCalls = globalThis.mockHttp.history.post.filter(r => /\/updatepipedriveDetails/.test(r.url))
        expect(postCalls.length).toBeGreaterThan(0)
    })

    it('calls successHandler after successful connect', async () => {
        await flushPromises()
        await wrapper.vm.connect()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on connect failure', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/settings\/pipedrive/).reply(200, { data: {} })
        globalThis.mockHttp.onPost(/\/updatepipedriveDetails/).reply(500)
        await flushPromises()
        await wrapper.vm.connect()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('loading is false after mount completes', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    // ── mount error path ───────────────────────────────────────────────────
    it('handles GET /settings/pipedrive error on mount without throwing', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/settings\/pipedrive/).reply(500)
        const w = mount(Pipedrive, {
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        await flushPromises()
        expect(w.vm.loading).toBe(false)
        w.unmount()
    })

    // ── saveSettings ────────────────────────────────────────────────────────
    it('saveSettings sends PATCH /settings/pipedrive', async () => {
        await flushPromises()
        globalThis.mockHttp.onPatch(/\/settings\/pipedrive/).reply(200, { data: { message: 'Saved' } })
        await wrapper.vm.saveSettings()
        await flushPromises()
        const patchCalls = globalThis.mockHttp.history.patch.filter(r => /\/settings\/pipedrive/.test(r.url))
        expect(patchCalls.length).toBeGreaterThan(0)
    })

    it('saveSettings calls successHandler on success', async () => {
        await flushPromises()
        globalThis.mockHttp.onPatch(/\/settings\/pipedrive/).reply(200, { data: {} })
        successHandler.mockClear()
        await wrapper.vm.saveSettings()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('saveSettings handles 500 error without throwing', async () => {
        await flushPromises()
        globalThis.mockHttp.onPatch(/\/settings\/pipedrive/).reply(500)
        await expect(wrapper.vm.saveSettings()).resolves.not.toThrow()
    })

    it('saveSettings sets savingSettings to false after completion', async () => {
        await flushPromises()
        globalThis.mockHttp.onPatch(/\/settings\/pipedrive/).reply(200, { data: {} })
        await wrapper.vm.saveSettings()
        await flushPromises()
        expect(wrapper.vm.savingSettings).toBe(false)
    })

    // ── loadMappingForGroup early-return guard ─────────────────────────────
    it('loadMappingForGroup does nothing when groupId is null/falsy', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        await wrapper.vm.loadMappingForGroup(null)
        await flushPromises()
        expect(globalThis.mockHttp.history.get.length).toBe(0)
    })

    it('loadMappingForGroup fetches mapping for a given groupId', async () => {
        await flushPromises()
        globalThis.mockHttp.onGet(/\/pipedrive\/mapping\/42/).reply(200, {
            data: { pipedriveData: { pipedrive_fields: [], local_fields: [] } },
        })
        await wrapper.vm.loadMappingForGroup(42)
        await flushPromises()
        expect(
            globalThis.mockHttp.history.get.some(r => r.url.includes('/pipedrive/mapping/42'))
        ).toBe(true)
    })

    it('loadMappingForGroup handles 500 error without throwing', async () => {
        await flushPromises()
        globalThis.mockHttp.onGet(/\/pipedrive\/mapping\/99/).reply(500)
        await expect(wrapper.vm.loadMappingForGroup(99)).resolves.not.toThrow()
    })

    // ── switchGroup ────────────────────────────────────────────────────────
    it('switchGroup does nothing when same group is already active (early return)', async () => {
        await flushPromises()
        const current = wrapper.vm.activeGroupId
        globalThis.mockHttp.reset()
        await wrapper.vm.switchGroup(current)
        await flushPromises()
        expect(globalThis.mockHttp.history.get.length).toBe(0)
    })

    it('switchGroup changes activeGroupId and loads mapping for new group', async () => {
        await flushPromises()
        globalThis.mockHttp.onGet(/\/pipedrive\/mapping\/55/).reply(200, {
            data: { pipedriveData: { pipedrive_fields: [], local_fields: [] } },
        })
        wrapper.vm.activeGroupId = 10 // set a different starting value
        await wrapper.vm.switchGroup(55)
        await flushPromises()
        expect(wrapper.vm.activeGroupId).toBe(55)
    })

    // ── syncFields ─────────────────────────────────────────────────────────
    it('syncFields calls GET /syncing/pipedriveFields', async () => {
        await flushPromises()
        globalThis.mockHttp.onGet(/\/syncing\/pipedriveFields/).reply(200, { data: {} })
        globalThis.mockHttp.onGet(/\/pipedrive\/mapping\//).reply(200, {
            data: { pipedriveData: { pipedrive_fields: [], local_fields: [] } },
        })
        await wrapper.vm.syncFields()
        await flushPromises()
        expect(
            globalThis.mockHttp.history.get.some(r => r.url.includes('/syncing/pipedriveFields'))
        ).toBe(true)
    })

    it('syncFields handles 500 error without throwing', async () => {
        await flushPromises()
        globalThis.mockHttp.onGet(/\/syncing\/pipedriveFields/).reply(500)
        await expect(wrapper.vm.syncFields()).resolves.not.toThrow()
    })

    it('syncFields sets syncing to false after completion', async () => {
        await flushPromises()
        globalThis.mockHttp.onGet(/\/syncing\/pipedriveFields/).reply(200, { data: {} })
        globalThis.mockHttp.onGet(/\/pipedrive\/mapping\//).reply(200, {
            data: { pipedriveData: { pipedrive_fields: [], local_fields: [] } },
        })
        await wrapper.vm.syncFields()
        await flushPromises()
        expect(wrapper.vm.syncing).toBe(false)
    })

    // ── saveMapping ────────────────────────────────────────────────────────
    it('saveMapping calls errorHandler when rows have empty fields', async () => {
        await flushPromises()
        // rows default to [{pipedriveField: null, faveoField: null, ...}]
        errorHandler.mockClear()
        await wrapper.vm.saveMapping()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('saveMapping sends POST /sync/pipedrive when rows are complete', async () => {
        await flushPromises()
        // populate rows with valid data
        wrapper.vm.rows = [{
            pipedriveField: { id: 1 },
            faveoField: { id: 2 },
            isFaveoField: true,
        }]
        globalThis.mockHttp.onPost(/\/sync\/pipedrive/).reply(200, { data: {} })
        await wrapper.vm.saveMapping()
        await flushPromises()
        expect(
            globalThis.mockHttp.history.post.some(r => r.url.includes('/sync/pipedrive'))
        ).toBe(true)
    })

    it('saveMapping handles 500 error without throwing', async () => {
        await flushPromises()
        wrapper.vm.rows = [{
            pipedriveField: { id: 1 },
            faveoField: { id: 2 },
            isFaveoField: false,
        }]
        globalThis.mockHttp.onPost(/\/sync\/pipedrive/).reply(500)
        await expect(wrapper.vm.saveMapping()).resolves.not.toThrow()
    })

    // ── addRow / deleteRow ─────────────────────────────────────────────────
    it('addRow appends a new row when under allPipedriveOptions limit', async () => {
        await flushPromises()
        wrapper.vm.allPipedriveOptions = [{ id: 1 }, { id: 2 }, { id: 3 }]
        const before = wrapper.vm.rows.length
        wrapper.vm.addRow()
        expect(wrapper.vm.rows.length).toBe(before + 1)
    })

    it('addRow does nothing when rows count equals allPipedriveOptions count', async () => {
        await flushPromises()
        wrapper.vm.allPipedriveOptions = [{ id: 1 }]
        wrapper.vm.rows = [{ pipedriveField: { id: 1 }, faveoField: null, faveoOptions: [], isFaveoField: true }]
        const before = wrapper.vm.rows.length
        wrapper.vm.addRow()
        expect(wrapper.vm.rows.length).toBe(before)
    })

    it('deleteRow removes the row at index', async () => {
        await flushPromises()
        wrapper.vm.rows = [
            { pipedriveField: { id: 1 }, faveoField: null, faveoOptions: [], isFaveoField: true },
            { pipedriveField: { id: 2 }, faveoField: null, faveoOptions: [], isFaveoField: true },
        ]
        wrapper.vm.deleteRow(0)
        expect(wrapper.vm.rows.length).toBe(1)
        expect(wrapper.vm.rows[0].pipedriveField.id).toBe(2)
    })

    // ── onPipedriveChange ──────────────────────────────────────────────────
    it('onPipedriveChange clears faveoField and resets to localOptions when val is null', async () => {
        await flushPromises()
        wrapper.vm.localOptions = [{ id: 'local1', name: 'Local 1' }]
        wrapper.vm.rows = [{
            pipedriveField: { id: 1 },
            faveoField: { id: 'f' },
            faveoOptions: [],
            isFaveoField: false,
        }]
        await wrapper.vm.onPipedriveChange(0, null)
        expect(wrapper.vm.rows[0].faveoField).toBeNull()
        expect(wrapper.vm.rows[0].isFaveoField).toBe(true)
    })

    it('onPipedriveChange fetches dropdown options when val is set', async () => {
        await flushPromises()
        globalThis.mockHttp.onPost(/\/pipedrive\/get-dropdown/).reply(200, {
            data: { options: [{ id: 'opt1', value: 'Option 1' }], is_faveo_options: false },
        })
        wrapper.vm.rows = [{
            pipedriveField: null,
            faveoField: null,
            faveoOptions: [],
            isFaveoField: true,
        }]
        await wrapper.vm.onPipedriveChange(0, { id: 99 })
        await flushPromises()
        expect(
            globalThis.mockHttp.history.post.some(r => r.url.includes('/pipedrive/get-dropdown'))
        ).toBe(true)
    })

    it('onPipedriveChange falls back to localOptions on dropdown fetch error', async () => {
        await flushPromises()
        globalThis.mockHttp.onPost(/\/pipedrive\/get-dropdown/).reply(500)
        wrapper.vm.localOptions = [{ id: 'fallback', name: 'Fallback' }]
        wrapper.vm.rows = [{
            pipedriveField: null,
            faveoField: null,
            faveoOptions: [],
            isFaveoField: true,
        }]
        await wrapper.vm.onPipedriveChange(0, { id: 55 })
        await flushPromises()
        expect(wrapper.vm.rows[0].isFaveoField).toBe(true)
    })

    // ── connect validation guard ───────────────────────────────────────────
    it('connect does not POST when apiKey validation fails', async () => {
        const { apiKeySchema } = require('@/validations/admin/pipedriveValidations')
        apiKeySchema.validate.mockRejectedValueOnce(new Error('API key required'))
        await flushPromises()
        globalThis.mockHttp.reset()
        await wrapper.vm.connect()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.length).toBe(0)
    })

    // ── connect sets connectionStatus to failed on error ───────────────────
    it('connect sets connectionStatus to failed on 500 error', async () => {
        await flushPromises()
        globalThis.mockHttp.onPost(/\/updatepipedriveDetails/).reply(500)
        await wrapper.vm.connect()
        await flushPromises()
        expect(wrapper.vm.connectionStatus).toBe('failed')
    })
})
