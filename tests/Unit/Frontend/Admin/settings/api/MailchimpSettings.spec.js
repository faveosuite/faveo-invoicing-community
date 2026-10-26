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

    const SETTINGS = { has_key: false, key_hint: null, data_center: null, account_name: null, list_id: null, subscribe_status: 'subscribed', lists: [] }

    function mountWith(settings = {}) {
        globalThis.mockHttp.onGet(/\/settings\/mailchimp/).reply(200, { data: { ...SETTINGS, ...settings } })
        return mount(MailchimpSettings, { global: { plugins: [createTestingPinia()], stubs: STUBS } })
    }

    beforeEach(() => {
        jest.clearAllMocks()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/updateMailchimpDetails/).reply(200, { message: 'Connected' })
        globalThis.mockHttp.onPatch(/\/mailchimp$/).reply(200, { message: 'Saved' })
        globalThis.mockHttp.onGet(/\/mailchimp\/mapping-data/).reply(200, { data: {} })
        wrapper = mountWith()
    })

    afterEach(() => wrapper.unmount())

    const fields = [
        { tag: 'FNAME', name: 'First Name', type: 'text', required: false },
        { tag: 'COMPANY', name: 'Company', type: 'text', required: true },
    ]

    it('fetches settings on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/settings\/mailchimp/.test(r.url))).toBe(true)
    })

    // ── Connection ────────────────────────────────────────────────────────────
    it('is not connected without a saved key and only shows the Connection tab', async () => {
        await flushPromises()
        expect(wrapper.vm.connected).toBe(false)
        expect(wrapper.vm.tabs.map(t => t.key)).toEqual(['connection'])
    })

    it('never holds the saved key, only that one exists, and shows the account', async () => {
        wrapper.unmount()
        wrapper = mountWith({ has_key: true, key_hint: 'wxyz', data_center: 'us7', account_name: 'Faveo', list_id: 'l1' })
        await flushPromises()
        expect(wrapper.vm.connected).toBe(true)
        expect(wrapper.vm.form.apiKey).toBe('')
        expect(wrapper.vm.connectionDetail).toBe('Faveo · us7')
        expect(wrapper.vm.tabs.map(t => t.key)).toEqual(['connection', 'field-mapping', 'interest-groups'])
    })

    it('falls back to the key ending when the account name is unknown', async () => {
        wrapper.unmount()
        wrapper = mountWith({ has_key: true, key_hint: 'wxyz', data_center: 'us7' })
        await flushPromises()
        expect(wrapper.vm.connectionDetail).toBe('•••• wxyz · us7')
        expect(wrapper.vm.tabs.map(t => t.key)).toEqual(['connection', 'field-mapping'])
    })

    it('connect posts the key and reloads the settings', async () => {
        await flushPromises()
        wrapper.vm.form.apiKey = 'test-key-us1'
        await wrapper.vm.connect()
        await flushPromises()
        const post = globalThis.mockHttp.history.post.find(r => /\/updateMailchimpDetails/.test(r.url))
        expect(JSON.parse(post.data)).toEqual({ mailchimp_auth_key: 'test-key-us1', status: 1 })
        expect(globalThis.mockHttp.history.get.filter(r => /\/settings\/mailchimp/.test(r.url)).length).toBe(2)
        expect(successHandler).toHaveBeenCalled()
    })

    it('connect with a saved key sends blank to keep it', async () => {
        wrapper.unmount()
        wrapper = mountWith({ has_key: true })
        await flushPromises()
        await wrapper.vm.connect()
        const post = globalThis.mockHttp.history.post.find(r => /\/updateMailchimpDetails/.test(r.url))
        expect(JSON.parse(post.data).mailchimp_auth_key).toBeNull()
    })

    it('connect calls errorHandler when the key is rejected', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/updateMailchimpDetails/).reply(400)
        wrapper.vm.form.apiKey = 'bad-key-us1'
        await wrapper.vm.connect()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('after disconnect only the Connection tab is left', async () => {
        wrapper.unmount()
        wrapper = mountWith({ has_key: true, list_id: 'l1' })
        await flushPromises()
        wrapper.vm.activeTab = 'interest-groups'
        wrapper.vm.onDisconnected()
        expect(wrapper.vm.tabs.map(t => t.key)).toEqual(['connection'])
        expect(wrapper.vm.activeTab).toBe('connection')
    })

    // ── Audience ──────────────────────────────────────────────────────────────
    it('saveList saves the audience and makes it the saved one', async () => {
        globalThis.mockHttp.onPost(/\/mailchimp\/sync-fields/).reply(200, { data: { fields } })
        await flushPromises()
        wrapper.vm.form.listId = 'list-123'
        await wrapper.vm.saveList()
        await flushPromises()
        const patch = globalThis.mockHttp.history.patch.find(r => /\/mailchimp$/.test(r.url))
        expect(JSON.parse(patch.data)).toEqual({ list_id: 'list-123', subscribe_status: 'subscribed' })
        expect(wrapper.vm.savedListId).toBe('list-123')
        // a different audience has its own fields
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('sync-fields'))).toBe(true)
    })

    // ── Field mapping ─────────────────────────────────────────────────────────
    it('loads the mapping and blanks a tag that is not in this audience', async () => {
        globalThis.mockHttp.onGet(/\/mailchimp\/mapping-data/).reply(200, {
            data: { fields, relation: { first_name: 'FNAME', company: 'MMERGE5' } },
        })
        await wrapper.vm.loadMappingData()
        expect(wrapper.vm.fieldRows.map(r => [r.faveoFieldId, r.tag])).toEqual([['first_name', 'FNAME'], ['company', null]])
    })

    it('marks required fields and lists the unmapped ones', async () => {
        await flushPromises()
        wrapper.vm.mergeTags = fields
        wrapper.vm.fieldRows = [{ key: 1, tag: 'FNAME', faveoFieldId: 'first_name' }]
        const company = wrapper.vm.mergeTagOptions.find(o => o.id === 'COMPANY')
        expect(company.name).toBe('Company (COMPANY)')
        expect(company.markRequired).toBe(true)
        expect(wrapper.vm.missingRequired).toEqual(['Company'])
    })

    it('hides fields other rows already use', async () => {
        await flushPromises()
        wrapper.vm.mergeTags = fields
        wrapper.vm.fieldRows = [{ key: 1, tag: 'FNAME', faveoFieldId: 'first_name' }, { key: 2, tag: null, faveoFieldId: null }]
        expect(wrapper.vm.tagOptionsFor(wrapper.vm.fieldRows[1]).map(o => o.id)).toEqual(['COMPANY'])
        expect(wrapper.vm.userFieldOptionsFor(wrapper.vm.fieldRows[1]).some(f => f.id === 'first_name')).toBe(false)
    })

    it('removing the last row leaves one empty row', async () => {
        await flushPromises()
        wrapper.vm.fieldRows = [{ key: 1, tag: 'FNAME', faveoFieldId: 'first_name' }]
        wrapper.vm.removeFieldRow(0)
        expect(wrapper.vm.fieldRows).toHaveLength(1)
        expect(wrapper.vm.fieldRows[0].tag).toBeNull()
    })

    it('saveMapping refuses half-filled rows and missing required fields', async () => {
        await flushPromises()
        wrapper.vm.mergeTags = fields
        wrapper.vm.fieldRows = [{ key: 1, tag: 'FNAME', faveoFieldId: null }]
        await wrapper.vm.saveMapping()
        wrapper.vm.fieldRows = [{ key: 1, tag: 'FNAME', faveoFieldId: 'first_name' }]
        await wrapper.vm.saveMapping()
        expect(globalThis.mockHttp.history.patch.some(r => r.url.includes('mail-chimp/mapping'))).toBe(false)
    })

    it('saveMapping sends every Faveo field, mapped or cleared', async () => {
        globalThis.mockHttp.onPatch(/\/mail-chimp\/mapping/).reply(200, { message: 'ok' })
        await flushPromises()
        wrapper.vm.mergeTags = fields
        wrapper.vm.fieldRows = [{ key: 1, tag: 'COMPANY', faveoFieldId: 'company' }, { key: 2, tag: 'FNAME', faveoFieldId: 'country' }]
        await wrapper.vm.saveMapping()
        const body = JSON.parse(globalThis.mockHttp.history.patch.find(r => r.url.includes('mail-chimp/mapping')).data)
        expect(body.company).toBe('COMPANY')
        expect(body.country).toBe('FNAME')
        expect(body.first_name).toBeNull()
        expect(Object.keys(body)).toHaveLength(12)
    })

    it('syncFields loads the fields with their required flag, then reloads the mapping', async () => {
        globalThis.mockHttp.onPost(/\/mailchimp\/sync-fields/).reply(200, { data: { fields } })
        // After a sync the server's mapping data reflects the cleaned-up mapping and the synced fields.
        globalThis.mockHttp.onGet(/\/mailchimp\/mapping-data/).reply(200, { data: { fields } })
        await wrapper.vm.syncFields()
        expect(wrapper.vm.mergeTags.find(t => t.tag === 'COMPANY').required).toBe(true)
        expect(globalThis.mockHttp.history.get.filter(r => r.url.includes('/mailchimp/mapping-data')).length).toBeGreaterThan(0)
    })

    it('syncFields calls errorHandler on failure', async () => {
        globalThis.mockHttp.onPost(/\/mailchimp\/sync-fields/).reply(500)
        await wrapper.vm.syncFields()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('sendTest posts to /mailchimp/test', async () => {
        globalThis.mockHttp.onPost(/\/mailchimp\/test/).reply(200, { message: 'ok' })
        await wrapper.vm.sendTest()
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('/mailchimp/test'))).toBe(true)
        expect(successHandler).toHaveBeenCalled()
    })

    // ── Interest groups ───────────────────────────────────────────────────────
    it('syncGroups calls POST /mailchimp/sync-groups', async () => {
        const groups = [{ category_id: 'c', category_option_id: 'o', category_name: 'Pro' }]
        globalThis.mockHttp.onPost(/\/mailchimp\/sync-groups/).reply(200, { data: { groups, categories: [] } })
        globalThis.mockHttp.onGet(/\/mailchimp\/mapping-data/).reply(200, { data: { groups } })
        await wrapper.vm.syncGroups()
        expect(wrapper.vm.interestGroups).toHaveLength(1)
        expect(successHandler).toHaveBeenCalled()
    })

    describe('interest group pickers', () => {
        const setGroups = () => {
            wrapper.vm.interestCategories = [{ id: 'h', title: 'Helpdesk' }, { id: 's', title: 'Servicedesk' }, { id: 'p', title: 'Products' }]
            wrapper.vm.interestGroups = [
                { category_id: 'h', category_option_id: 'h1', category_name: 'YES' },
                { category_id: 'h', category_option_id: 'h2', category_name: 'NO' },
                { category_id: 's', category_option_id: 's1', category_name: 'YES' },
                { category_id: 's', category_option_id: 's2', category_name: 'NO' },
                { category_id: 'p', category_option_id: 'p1', category_name: 'Faveo Helpdesk Pro' },
            ]
        }

        it('builds the product option tree: one branch per group, its options as leaves', async () => {
            await flushPromises()
            setGroups()
            wrapper.vm.isPaidStatus = false
            const tree = wrapper.vm.interestGroupTree
            expect(tree.map(g => g.name)).toEqual(['Helpdesk', 'Servicedesk', 'Products'])
            expect(tree[0].children).toEqual([{ id: 'h1', name: 'YES' }, { id: 'h2', name: 'NO' }])
        })

        it('reserves the is-paid group only while that switch is on', async () => {
            await flushPromises()
            setGroups()
            wrapper.vm.isPaidCategoryId = 's'
            wrapper.vm.isPaidStatus = false
            expect(wrapper.vm.interestGroupTree.some(g => g.name === 'Servicedesk')).toBe(true)
            wrapper.vm.isPaidStatus = true
            expect(wrapper.vm.interestGroupTree.some(g => g.name === 'Servicedesk')).toBe(false)
        })

        it('marks groups without Yes/No as unusable for is-paid', async () => {
            await flushPromises()
            setGroups()
            const byName = Object.fromEntries(wrapper.vm.paidGroupOptions.map(o => [o.name, o]))
            expect(byName.Helpdesk).toMatchObject({ valid: true, yes: 'YES', no: 'NO', hint: 'YES / NO' })
            expect(byName.Products.valid).toBe(false)
        })
    })

    it('saveIsPaid needs a category when switched on', async () => {
        await flushPromises()
        wrapper.vm.isPaidStatus = true
        wrapper.vm.isPaidCategoryId = null
        await wrapper.vm.saveIsPaid()
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('mailchimp-paid-status'))).toBe(false)
    })

    it('saveIsPaid saves the switch and the category', async () => {
        globalThis.mockHttp.onPost(/\/mailchimp-paid-status/).reply(200, {})
        globalThis.mockHttp.onPatch(/\/mailchimp-ispaid\/mapping/).reply(200, { message: 'ok' })
        await flushPromises()
        wrapper.vm.isPaidStatus = true
        wrapper.vm.isPaidCategoryId = 'cat1'
        await wrapper.vm.saveIsPaid()
        expect(JSON.parse(globalThis.mockHttp.history.patch.find(r => r.url.includes('mailchimp-ispaid')).data)).toEqual({ group: 'cat1' })
    })

    it('saveProductGroups refuses half-filled rows', async () => {
        await flushPromises()
        wrapper.vm.productStatus = true
        wrapper.vm.productRows = [{ key: 1, productId: '7', groupId: null }]
        await wrapper.vm.saveProductGroups()
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('mailchimp-prod-status'))).toBe(false)
    })

    it('saveProductGroups sends the rows even when empty, so removing all clears them', async () => {
        globalThis.mockHttp.onPost(/\/mailchimp-prod-status/).reply(200, {})
        globalThis.mockHttp.onPatch(/\/mailchimp-group\/mapping/).reply(200, { message: 'ok' })
        await flushPromises()
        wrapper.vm.productStatus = true
        wrapper.vm.productRows = [{ key: 1, productId: null, groupId: null }]
        await wrapper.vm.saveProductGroups()
        expect(JSON.parse(globalThis.mockHttp.history.patch.find(r => r.url.includes('mailchimp-group')).data)).toEqual({ row: [] })
    })

    it('one group per product: a used product is hidden from other rows', async () => {
        await flushPromises()
        wrapper.vm.products = [{ id: '7', name: 'A' }, { id: '8', name: 'B' }]
        wrapper.vm.productRows = [{ key: 1, productId: '7', groupId: 'g' }, { key: 2, productId: null, groupId: null }]
        expect(wrapper.vm.productOptionsFor(wrapper.vm.productRows[1]).map(o => o.id)).toEqual(['8'])
    })

    // ── loadMoreLists ─────────────────────────────────────────────────────────
    it('loadMoreLists fetches next page of lists when listsHasMore is true', async () => {
        globalThis.mockHttp.onGet(/\/mailchimp\/lists/).reply(200, { data: { lists: [{ id: 'x', name: 'X' }], has_more: false } })
        wrapper.vm.listsHasMore = true
        await wrapper.vm.loadMoreLists()
        expect(wrapper.vm.lists.map(l => l.id)).toContain('x')
    })

    it('loadMoreLists returns early when listsHasMore is false', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        wrapper.vm.listsHasMore = false
        await wrapper.vm.loadMoreLists()
        expect(globalThis.mockHttp.history.get.length).toBe(0)
    })
})
