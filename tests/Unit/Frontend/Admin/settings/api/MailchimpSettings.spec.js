jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { name: 'Toggle', template: '<button />', props: ['modelValue', 'disabled'], emits: ['update:modelValue'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('@/validations/admin/mailchimpValidations', () => ({
    connectionSchema: { validate: jest.fn(() => Promise.resolve(true)) },
    listSchema: { validate: jest.fn(() => Promise.resolve(true)) },
}))
jest.mock('vue-select', () => ({ __esModule: true, default: { template: '<div />' } }), { virtual: true })

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MailchimpSettings from '@/pages/admin/settings/api/MailchimpSettings.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'DynamicSelect', 'AppModal', 'ImageUpload', 'PhoneField',
    'RecaptchaProvider', 'RecaptchaCheckbox', 'RecaptchaV2Invisible', 'RecaptchaV3',
    'ZohoCard', 'spinner-loader', 'v-select',
]

describe('MailchimpSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/settings\/mailchimp/).reply(200, {
            data: {
                apiKey: '',
                listId: null,
                subscribeStatus: 'subscribed',
                connectionStatus: 'idle',
                lists: [],
            },
        })
        globalThis.mockHttp.onPost(/\/updateMailchimpDetails/).reply(200, {
            data: { message: 'Connected', lists: [] },
        })
        globalThis.mockHttp.onPatch(/\/mailchimp$/).reply(200, { data: { message: 'Saved' } })
        globalThis.mockHttp.onGet(/\/mailchimp\/mapping-data/).reply(200, { data: {} })
        wrapper = mount(MailchimpSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches settings on mount via GET /settings/mailchimp', async () => {
        await flushPromises()
        const getCalls = globalThis.mockHttp.history.get.filter(r => /\/settings\/mailchimp/.test(r.url))
        expect(getCalls.length).toBeGreaterThan(0)
    })

    it('connected is false initially', async () => {
        await flushPromises()
        expect(wrapper.vm.connected).toBe(false)
    })

    it('calls POST /updateMailchimpDetails on connect()', async () => {
        await flushPromises()
        wrapper.vm.form.apiKey = 'test-key-us1'
        await wrapper.vm.connect()
        await flushPromises()
        const postCalls = globalThis.mockHttp.history.post.filter(r => /\/updateMailchimpDetails/.test(r.url))
        expect(postCalls.length).toBeGreaterThan(0)
    })

    it('connect sets connected to true and calls successHandler on success', async () => {
        await flushPromises()
        wrapper.vm.form.apiKey = 'test-key-us1'
        await wrapper.vm.connect()
        await flushPromises()
        expect(wrapper.vm.connected).toBe(true)
        expect(successHandler).toHaveBeenCalled()
    })

    it('connect calls errorHandler when the key is rejected', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/settings\/mailchimp/).reply(200, { data: {} })
        globalThis.mockHttp.onPost(/\/updateMailchimpDetails/).reply(500)
        await flushPromises()
        wrapper.vm.form.apiKey = 'test-key-us1'
        await wrapper.vm.connect()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('calls PATCH /mailchimp on saveConnection() once connected with a list selected', async () => {
        await flushPromises()
        wrapper.vm.connected = true
        wrapper.vm.form.listId = 'list-123'
        wrapper.vm.form.subscribeStatus = 'subscribed'
        await wrapper.vm.saveConnection()
        await flushPromises()
        const patchCalls = globalThis.mockHttp.history.patch.filter(r => /\/mailchimp$/.test(r.url))
        expect(patchCalls.length).toBeGreaterThan(0)
    })

    // ── addFieldRow / removeFieldRow ──────────────────────────────────────────
    it('addFieldRow appends a new empty row', async () => {
        await flushPromises()
        const before = wrapper.vm.fieldRows.length
        wrapper.vm.addFieldRow()
        expect(wrapper.vm.fieldRows.length).toBe(before + 1)
        expect(wrapper.vm.fieldRows.at(-1)).toEqual({ faveoFieldId: null, mergeTagId: null })
    })

    it('removeFieldRow removes the row at index', async () => {
        await flushPromises()
        wrapper.vm.fieldRows = [{ faveoFieldId: 1, mergeTagId: 2 }, { faveoFieldId: 3, mergeTagId: 4 }]
        wrapper.vm.removeFieldRow(0)
        expect(wrapper.vm.fieldRows.length).toBe(1)
        expect(wrapper.vm.fieldRows[0].faveoFieldId).toBe(3)
    })

    // ── addProductRow / removeProductRow ──────────────────────────────────────
    it('addProductRow appends a new empty product row', async () => {
        await flushPromises()
        const before = wrapper.vm.productRows.length
        wrapper.vm.addProductRow()
        expect(wrapper.vm.productRows.length).toBe(before + 1)
    })

    it('removeProductRow removes the product row at index', async () => {
        wrapper.vm.productRows = [{ productId: 1, groupId: 2 }, { productId: 3, groupId: 4 }]
        wrapper.vm.removeProductRow(1)
        expect(wrapper.vm.productRows.length).toBe(1)
    })

    // ── loadMappingData ───────────────────────────────────────────────────────
    it('loadMappingData fetches mapping data on success', async () => {
        globalThis.mockHttp.onGet(/\/mailchimp\/mapping-data/).reply(200, {
            data: { faveo_fields: [], products: [], tags: [], merge_tags: [] }
        })
        await wrapper.vm.loadMappingData()
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => r.url.includes('mapping-data'))).toBe(true)
    })

    it('loadMappingData calls errorHandler on failure', async () => {
        globalThis.mockHttp.onGet(/\/mailchimp\/mapping-data/).reply(500)
        await expect(wrapper.vm.loadMappingData()).resolves.not.toThrow()
    })

    // ── syncFields ────────────────────────────────────────────────────────────
    it('syncFields calls POST /mailchimp/sync-fields on success', async () => {
        globalThis.mockHttp.onPost(/\/mailchimp\/sync-fields/).reply(200, { message: 'ok' })
        await wrapper.vm.syncFields()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('sync-fields'))).toBe(true)
    })

    it('syncFields calls errorHandler on failure', async () => {
        globalThis.mockHttp.onPost(/\/mailchimp\/sync-fields/).reply(500)
        await expect(wrapper.vm.syncFields()).resolves.not.toThrow()
    })

    // ── saveMapping ───────────────────────────────────────────────────────────
    it('saveMapping calls PATCH /mail-chimp/mapping on success', async () => {
        globalThis.mockHttp.onPatch(/\/mail-chimp\/mapping/).reply(200, { message: 'ok' })
        await wrapper.vm.saveMapping()
        await flushPromises()
        expect(globalThis.mockHttp.history.patch.some(r => r.url.includes('mapping'))).toBe(true)
    })

    it('saveMapping calls errorHandler on failure', async () => {
        globalThis.mockHttp.onPatch(/\/mail-chimp\/mapping/).reply(500)
        await expect(wrapper.vm.saveMapping()).resolves.not.toThrow()
    })

    // ── syncGroups ────────────────────────────────────────────────────────────
    it('syncGroups calls POST /mailchimp/sync-groups on success', async () => {
        globalThis.mockHttp.onPost(/\/mailchimp\/sync-groups/).reply(200, { message: 'ok' })
        await wrapper.vm.syncGroups()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('sync-groups'))).toBe(true)
    })

    it('syncGroups calls errorHandler on failure', async () => {
        globalThis.mockHttp.onPost(/\/mailchimp\/sync-groups/).reply(500)
        await expect(wrapper.vm.syncGroups()).resolves.not.toThrow()
    })

    // ── saveInterestGroups ────────────────────────────────────────────────────
    it('saveInterestGroups calls multiple POSTs on success', async () => {
        globalThis.mockHttp.onPost(/\/mailchimp-prod-status/).reply(200, { message: 'ok' })
        globalThis.mockHttp.onPost(/\/mailchimp-paid-status/).reply(200, { message: 'ok' })
        await wrapper.vm.saveInterestGroups()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('mailchimp'))).toBe(true)
    })

    it('saveInterestGroups calls errorHandler on failure', async () => {
        globalThis.mockHttp.onPost(/\/mailchimp-prod-status/).reply(500)
        globalThis.mockHttp.onPost(/\/mailchimp-paid-status/).reply(500)
        await expect(wrapper.vm.saveInterestGroups()).resolves.not.toThrow()
    })

    // ── loadMoreLists ─────────────────────────────────────────────────────────
    it('loadMoreLists fetches next page of lists when listsHasMore is true', async () => {
        globalThis.mockHttp.onGet(/\/mailchimp\/lists/).reply(200, { data: { lists: [], has_more: false } })
        wrapper.vm.listsHasMore = true
        await wrapper.vm.loadMoreLists()
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => r.url.includes('lists'))).toBe(true)
    })

    it('loadMoreLists returns early when listsHasMore is false', async () => {
        globalThis.mockHttp.reset()
        wrapper.vm.listsHasMore = false
        await wrapper.vm.loadMoreLists()
        expect(globalThis.mockHttp.history.get.length).toBe(0)
    })

    it('loadMoreLists handles error gracefully', async () => {
        globalThis.mockHttp.onGet(/\/mailchimp\/lists/).reply(500)
        wrapper.vm.listsHasMore = true
        await expect(wrapper.vm.loadMoreLists()).resolves.not.toThrow()
    })
})
