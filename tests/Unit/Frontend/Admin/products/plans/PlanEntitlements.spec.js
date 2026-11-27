jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
const mockReplace = jest.fn()
jest.mock('vue-router', () => ({ useRouter: () => ({ replace: mockReplace }), useRoute: () => ({ query: {} }) }))

import { mount } from '@vue/test-utils'
import PlanEntitlements from '@/pages/admin/products/plans/PlanEntitlements.vue'
import ActionButton from '@/components/Reusable/ActionButton.vue'

const item = (over) => ({ category: null, type: 'boolean', mode: 'included', value: '1', default_value: null, is_active: true, sellable: true, prices: {}, ...over })
const ENTITLEMENTS = [
    item({ id: 1, code: 'agents', label: 'Agents', type: 'number', value: '5', prices: { USD: { unit_price: '9', renew_price: '', offer_percent: '' } } }),
    item({ id: 2, code: 'sla', label: 'SLA' }),
    item({ id: 3, code: 'storage', label: 'Storage', type: 'number', value: '10', prices: { USD: { unit_price: '2', renew_price: '', offer_percent: '' } } }),
    item({ id: 4, code: 'updates', label: 'Updates', type: 'date', value: { ends: 'subscription' }, sellable: false }),
    item({ id: 5, code: 'chat', label: 'Chat', mode: 'optional', value: null }),
    item({ id: 6, code: 'sso', label: 'SSO', mode: 'none', value: null }),
    ...Array.from({ length: 9 }, (_, i) => item({ id: 10 + i, code: `plugin_${i}`, label: `Plugin ${i}`, category: 'Plugins' })),
]

async function make() {
    globalThis.mockHttp.onGet('/plan/7/entitlements').reply(200, { data: { entitlements: ENTITLEMENTS, live_licenses: 3 } })
    const wrapper = mount(PlanEntitlements, {
        props: { planId: 7, currencies: ['USD'], period: '1 Year' },
        global: { stubs: ['AppModal', 'loader', 'ToolTip', 'Tooltip'], components: { 'action-button': ActionButton } },
    })
    await flushPromises()
    return wrapper
}

describe('PlanEntitlements.vue', () => {
    afterEach(() => globalThis.mockHttp.reset())

    it('lists the plan\'s features (agents among them), 10 a page; unused features are left out', async () => {
        const wrapper = await make()
        expect(wrapper.vm.rows.map(r => r.code).slice(0, 5)).toEqual(['agents', 'sla', 'storage', 'updates', 'chat'])
        expect(wrapper.vm.rows.some(r => r.code === 'sso')).toBe(false)
        expect(wrapper.findAll('tbody tr')).toHaveLength(10)
        expect(wrapper.vm.categories).toEqual(['message.order_features', 'message.order_dates', 'Plugins'])
    })

    it('says what each row gives and costs, in words', async () => {
        const wrapper = await make()
        const byCode = code => wrapper.vm.rows.find(r => r.code === code)
        expect(wrapper.vm.givesText(byCode('agents'))).toBe('5')
        expect(wrapper.vm.priceText(byCode('agents'))).toBe('USD 9')
        expect(wrapper.vm.givesText(byCode('updates'))).toBe('message.plan_date_ends_subscription')
        expect(wrapper.vm.givesText(byCode('chat'))).toBe('message.plan_gives_nothing')
        expect(wrapper.vm.priceText(byCode('sla'))).toBe('') // nothing to price
    })

    it('unlimited is ticked, never a blank box, and has nothing more to sell, for agents as for any limit', async () => {
        const wrapper = await make()
        for (const code of ['agents', 'storage']) {
            const row = wrapper.vm.rows.find(r => r.code === code)
            wrapper.vm.openEditor(row)
            wrapper.vm.editing.value = ''
            wrapper.vm.applyEditor()
            expect(wrapper.vm.editing).not.toBeNull() // a blank limit is refused
            expect(wrapper.vm.givesText(row)).not.toBe('message.unlimited')

            wrapper.vm.editing.value = '-1'
            wrapper.vm.applyEditor()
            expect(wrapper.vm.editing).not.toBeNull()

            wrapper.vm.editing.unlimited = true
            wrapper.vm.editing.value = null
            wrapper.vm.applyEditor()
            expect(wrapper.vm.editing).toBeNull()
            expect(wrapper.vm.givesText(row)).toBe('message.unlimited')
            expect(wrapper.vm.priceText(row)).toBe('')
        }
        wrapper.vm.openEditor(wrapper.vm.rows.find(r => r.code === 'agents'))
        wrapper.vm.editing.value = '0'
        wrapper.vm.applyEditor()
        expect(wrapper.vm.givesText(wrapper.vm.rows.find(r => r.code === 'agents'))).toBe('0')
    })

    it('edits a copy: cancel changes nothing, apply writes it back', async () => {
        const wrapper = await make()
        const agents = wrapper.vm.rows[0]
        wrapper.vm.openEditor(agents)
        wrapper.vm.editing.value = '8'
        wrapper.vm.editing = null // cancel
        expect(wrapper.vm.rows[0].value).toBe('5')

        wrapper.vm.openEditor(agents)
        wrapper.vm.editing.value = '8'
        wrapper.vm.applyEditor()
        expect(wrapper.vm.rows[0].value).toBe('8')
    })

    it('refuses an offer over 100%', async () => {
        const wrapper = await make()
        wrapper.vm.openEditor(wrapper.vm.rows.find(r => r.code === 'storage'))
        wrapper.vm.editing.prices.USD.offer_percent = '150'
        wrapper.vm.applyEditor()
        expect(wrapper.vm.editing).not.toBe(null)
    })

    it('an optional feature needs a price in at least one currency', async () => {
        const wrapper = await make()
        wrapper.vm.openEditor(wrapper.vm.rows.find(r => r.code === 'chat')) // optional, no price yet
        wrapper.vm.applyEditor()
        expect(wrapper.vm.editing).not.toBe(null)

        wrapper.vm.editing.prices.USD.unit_price = '5'
        wrapper.vm.applyEditor()
        expect(wrapper.vm.editing).toBe(null)
    })

    it('removing can be undone, and changes mark the features unsaved', async () => {
        const wrapper = await make()
        expect(wrapper.vm.dirty).toBe(false)
        const before = wrapper.vm.rows.length
        await wrapper.findAll('tbody tr')[0].findAll('button').find(b => b.attributes('aria-label')?.startsWith('message.plan_feature_remove')).trigger('click')
        expect(wrapper.vm.rows).toHaveLength(before - 1)
        expect(wrapper.vm.dirty).toBe(true)
        expect(wrapper.text()).toContain('message.plan_removed_one')

        await wrapper.find('[role="status"] button').trigger('click')
        expect(wrapper.vm.rows).toHaveLength(before)
        expect(wrapper.vm.dirty).toBe(false)
    })

    it('search, category and page go in the URL', async () => {
        const wrapper = await make()
        wrapper.vm.category = 'Plugins'
        await wrapper.vm.$nextTick()
        expect(mockReplace).toHaveBeenLastCalledWith({ query: { fcat: 'Plugins', fq: undefined, fpage: undefined } })
    })

    it('copy and add say what is missing instead of staying disabled', async () => {
        const wrapper = await make()
        wrapper.vm.openCopy()
        await wrapper.vm.copyFeatures()
        expect(wrapper.vm.copyError).toBe('message.plan_copy_pick')
        wrapper.vm.openPicker()
        wrapper.vm.addPicked()
        expect(wrapper.vm.pickError).toBe('message.plan_add_pick_one')
    })

    it('adds ticked features at once: a whole category, as included or as add-ons with one price', async () => {
        const entitlements = [
            ...ENTITLEMENTS.filter(e => e.code !== 'sso'),
            item({ id: 6, code: 'sso', label: 'SSO', mode: 'none', value: null }),
            item({ id: 7, code: 'kb', label: 'KB', mode: 'none', value: null, category: 'Plugins' }),
            item({ id: 8, code: 'region', label: 'Region', type: 'text', mode: 'none', value: null, sellable: false, default_value: 'US' }),
        ]
        globalThis.mockHttp.onGet('/plan/7/entitlements').reply(200, { data: { entitlements, live_licenses: 3 } })
        const wrapper = mount(PlanEntitlements, {
            props: { planId: 7, currencies: ['USD'], period: '1 Year' },
            global: { stubs: ['AppModal', 'loader', 'ToolTip', 'Tooltip'], components: { 'action-button': ActionButton } },
        })
        await flushPromises()

        wrapper.vm.openPicker()
        expect(wrapper.vm.pickGroups.map(g => g.name)).toEqual(['message.order_features', 'Plugins'])
        wrapper.vm.pickAll(wrapper.vm.pickGroups[0], true) // SSO + Region
        wrapper.vm.togglePick(7, true)                       // KB
        wrapper.vm.addAs = 'optional'
        wrapper.vm.addPicked()
        expect(wrapper.vm.picking).toBe(true) // an add-on needs a price

        wrapper.vm.batchPrices = { USD: '4' }
        wrapper.vm.addPicked()
        expect(wrapper.vm.picking).toBe(false)
        const byCode = code => wrapper.vm.rows.find(r => r.code === code)
        expect([byCode('sso').mode, byCode('sso').prices.USD.unit_price]).toEqual(['optional', '4'])
        expect(byCode('kb').mode).toBe('optional')
        expect([byCode('region').mode, byCode('region').value]).toEqual(['included', 'US']) // can't be sold: included
    })

    it('copies another plan\'s features: same list, values and prices, nothing saved yet', async () => {
        globalThis.mockHttp.onGet('/plan/7/entitlements').reply(200, { data: { entitlements: ENTITLEMENTS, live_licenses: 3, other_plans: [{ id: 9, name: 'Pro' }] } })
        globalThis.mockHttp.onGet('/plan/9/entitlements').reply(200, { data: { entitlements: [
            item({ id: 3, code: 'storage', label: 'Storage', type: 'number', value: '50', prices: { USD: { unit_price: '1', renew_price: '', offer_percent: '' } } }),
            item({ id: 6, code: 'sso', label: 'SSO', mode: 'optional', value: null, prices: { USD: { unit_price: '7', renew_price: '', offer_percent: '' } } }),
        ] } })
        const wrapper = mount(PlanEntitlements, {
            props: { planId: 7, currencies: ['USD'], period: '1 Year' },
            global: { stubs: ['AppModal', 'loader', 'ToolTip', 'Tooltip'], components: { 'action-button': ActionButton } },
        })
        await flushPromises()
        expect(wrapper.vm.otherPlans).toEqual([{ id: 9, name: 'Pro' }])

        wrapper.vm.openCopy()
        wrapper.vm.copyFrom = 9
        await wrapper.vm.copyFeatures()
        expect(wrapper.vm.rows.map(r => [r.code, r.mode, r.value])).toEqual([['storage', 'included', '50'], ['sso', 'optional', null]])
        expect(wrapper.vm.rows.find(r => r.code === 'sso').prices.USD.unit_price).toBe('7')
        expect(wrapper.vm.copying).toBe(false)
        expect(globalThis.mockHttp.history.post).toHaveLength(0) // saved with the plan, not now
    })

    it('ticked rows change together: included, add-ons at one price, or removed', async () => {
        const wrapper = await make()
        const byCode = code => wrapper.vm.rows.find(r => r.code === code)
        wrapper.vm.toggleRow(2, true) // SLA (on/off)
        wrapper.vm.toggleRow(4, true) // Updates (date): can't be an add-on
        wrapper.vm.openBulkAddon()
        wrapper.vm.bulkMakeAddon()
        expect(wrapper.vm.bulkAddon).toBe(true) // needs a price

        wrapper.vm.bulkPrices = { USD: '3' }
        wrapper.vm.bulkMakeAddon()
        expect([byCode('sla').mode, byCode('sla').prices.USD.unit_price]).toEqual(['optional', '3'])
        expect(byCode('updates').mode).toBe('included')
        expect(wrapper.vm.selected).toEqual([])

        wrapper.vm.toggleRow(2, true)
        wrapper.vm.bulkIncluded()
        expect([byCode('sla').mode, byCode('sla').prices.USD.unit_price]).toEqual(['included', '']) // on/off included: nothing to sell

        const before = wrapper.vm.rows.length
        wrapper.vm.selectAll(true) // the rows on screen, as on every DataTable
        const onScreen = wrapper.vm.pageRows.length
        expect(wrapper.vm.selected).toHaveLength(onScreen)
        wrapper.vm.bulkRemove()
        expect(wrapper.vm.rows).toHaveLength(before - onScreen)
    })

    it('the add popup shows what is ticked on top, folds long categories, and tells same-named features apart', async () => {
        const plugins = Array.from({ length: 30 }, (_, n) => item({ id: 100 + n, code: `p_${n}`, label: n < 2 ? 'Adhoc Approval' : `P ${n}`, mode: 'none', value: null, category: 'Plugins' }))
        globalThis.mockHttp.onGet('/plan/7/entitlements').reply(200, { data: { entitlements: [...ENTITLEMENTS, ...plugins], live_licenses: 3 } })
        const AppModal = { props: ['showModal'], template: '<div v-if="showModal" class="modal-stub"><slot name="fields" /></div>' }
        const wrapper = mount(PlanEntitlements, {
            props: { planId: 7, currencies: ['USD'], period: '1 Year' },
            global: { stubs: ['loader', 'ToolTip', 'Tooltip'], components: { 'action-button': ActionButton, AppModal } },
        })
        await flushPromises()
        wrapper.vm.openPicker()
        await wrapper.vm.$nextTick()

        const plugGroup = wrapper.vm.pickGroups.find(g => g.name === 'Plugins')
        expect(wrapper.vm.groupOpen(plugGroup)).toBe(false) // 30 plugins: folded
        wrapper.vm.pickSearch = 'adhoc'
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.groupOpen(wrapper.vm.pickGroups[0])).toBe(true) // a search opens what matches
        expect(wrapper.vm.sameName(plugins[0])).toBe(true)

        wrapper.vm.togglePick(100, true)
        await wrapper.vm.$nextTick()
        expect(wrapper.find('[role="status"]').text()).toContain('Adhoc Approval')
        await wrapper.find('[role="status"] .badge button').trigger('click')
        expect(wrapper.vm.picked).toEqual([])
    })

    it('an empty plan says so, with nothing to select', async () => {
        const entitlements = ENTITLEMENTS.map(e => ({ ...e, mode: 'none', value: null }))
        globalThis.mockHttp.onGet('/plan/7/entitlements').reply(200, { data: { entitlements, live_licenses: 0 } })
        const wrapper = mount(PlanEntitlements, {
            props: { planId: 7, currencies: ['USD'], period: '1 Year' },
            global: { stubs: ['AppModal', 'loader', 'ToolTip', 'Tooltip'], components: { 'action-button': ActionButton } },
        })
        await flushPromises()
        expect(wrapper.text()).toContain('message.plan_features_none_yet')
        expect(wrapper.find('tbody button').exists()).toBe(false)
        expect(wrapper.find('input[name="select_all_rows"]').exists()).toBe(false)
    })

    it('adding a feature includes it and opens its editor', async () => {
        const wrapper = await make()
        wrapper.vm.addFeature('6')
        expect(wrapper.vm.rows.find(r => r.code === 'sso').mode).toBe('included')
        expect(wrapper.vm.editing.code).toBe('sso')
    })

    it('search applies on Enter; a category narrows the rows', async () => {
        const wrapper = await make()
        await wrapper.find('input.globe-search').setValue('plugin_3')
        expect(wrapper.vm.pageRows).toHaveLength(10) // not yet
        await wrapper.find('input.globe-search').trigger('keyup.enter')
        expect(wrapper.vm.pageRows.map(r => r.code)).toEqual(['plugin_3'])

        wrapper.vm.search = ''
        wrapper.vm.category = 'Plugins'
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.pageRows).toHaveLength(9)
    })

    it('the summary counts features instead of listing each one', async () => {
        const wrapper = await make()
        expect(wrapper.vm.summary).toHaveLength(1) // one features line, however many features (agents included)
        expect(wrapper.vm.summary[0]).toEqual({ label: 'message.features', value: 'message.plan_summary_included', extra: 'message.plan_summary_optional' })
    })

    it('edits agents with the same fields as any other limit, but never as optional', async () => {
        globalThis.mockHttp.onGet('/plan/7/entitlements').reply(200, { data: { entitlements: ENTITLEMENTS, live_licenses: 3 } })
        const AppModal = { props: ['showModal'], template: '<div v-if="showModal" class="modal-stub"><slot name="fields" /></div>' }
        const wrapper = mount(PlanEntitlements, {
            props: { planId: 7, currencies: ['USD'], period: '1 Year' },
            global: { stubs: ['loader', 'ToolTip', 'Tooltip'], components: { 'action-button': ActionButton, AppModal } },
        })
        await flushPromises()
        const fields = () => ({
            value: wrapper.find('#text-field-value').exists(),
            unlimited: wrapper.find('input[name="value_kind"][value="unlimited"]').exists(),
            price: wrapper.find('#unit_USD').exists(),
            optional: wrapper.find('input[name="mode"][value="optional"]').exists(),
        })

        wrapper.vm.openEditor(wrapper.vm.rows.find(r => r.code === 'storage'))
        await wrapper.vm.$nextTick()
        expect(fields()).toEqual({ value: true, unlimited: true, price: true, optional: true })

        wrapper.vm.openEditor(wrapper.vm.rows.find(r => r.code === 'agents'))
        await wrapper.vm.$nextTick()
        expect(fields()).toEqual({ value: true, unlimited: true, price: true, optional: false })
        expect(wrapper.find('input[name="agents_unlimited"]').exists()).toBe(false)

        await wrapper.find('input[name="value_kind"][value="unlimited"]').setValue(true)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.editing.value).toBeNull()
        expect(wrapper.find('#text-field-value').exists()).toBe(false) // the box shows only for Limited
        expect(fields().price).toBe(false) // unlimited: nothing more to sell
        expect(wrapper.find('input[name="buy_more"]').exists()).toBe(false)
    })

    it('the editor shows only the steps that apply, and says what the customer gets', async () => {
        globalThis.mockHttp.onGet('/plan/7/entitlements').reply(200, { data: { entitlements: ENTITLEMENTS, live_licenses: 3 } })
        const AppModal = { props: ['showModal'], template: '<div v-if="showModal" class="modal-stub"><slot name="fields" /></div>' }
        const wrapper = mount(PlanEntitlements, {
            props: { planId: 7, currencies: ['USD', 'INR'], period: '1 Year' },
            global: { stubs: ['loader', 'ToolTip', 'Tooltip'], components: { 'action-button': ActionButton, AppModal } },
        })
        await flushPromises()
        const open = async (code) => { wrapper.vm.openEditor(wrapper.vm.rows.find(r => r.code === code)); await wrapper.vm.$nextTick() }

        // A number with no amount yet says what's missing instead of showing a gap
        await open('storage')
        wrapper.vm.editing.value = ''
        expect(wrapper.vm.summaryLines[0]).toMatchObject({ text: 'message.plan_get_set_amount', cls: 'text-warning' })

        // On/off, included: no amount, no price
        await open('sla')
        expect([wrapper.find('#text-field-value').exists(), wrapper.find('#unit_USD').exists()]).toEqual([false, false])
        expect(wrapper.vm.summaryLines.map(l => l.text)).toEqual(['SLA'])

        // Add-on: price only, required
        await open('chat')
        expect([wrapper.find('#text-field-value').exists(), wrapper.find('#unit_USD').exists()]).toEqual([false, true])

        // A limited number with a price: "buy more" on, prices shown; untick it and the prices go on Apply
        await open('storage')
        expect(wrapper.vm.editing.buyMore).toBe(true)
        expect(wrapper.find('#unit_USD').exists()).toBe(true)
        expect(wrapper.vm.summaryLines.map(l => l.text)).toEqual(['10 Storage', 'message.plan_get_extra', 'message.plan_get_not_sold'])
        wrapper.vm.editing.buyMore = false
        await wrapper.vm.$nextTick()
        expect(wrapper.find('#unit_USD').exists()).toBe(false)
        wrapper.vm.applyEditor()
        expect(wrapper.vm.rows.find(r => r.code === 'storage').prices.USD.unit_price).toBe('')

        // "Buy more" ticked with no price is refused
        await open('storage')
        wrapper.vm.editing.buyMore = true
        wrapper.vm.applyEditor()
        expect(wrapper.vm.editing).not.toBeNull()
    })

    it('a plan including no agents needs an agent price in every currency', async () => {
        const wrapper = await make()
        wrapper.vm.openEditor(wrapper.vm.rows.find(r => r.code === 'agents'))
        wrapper.vm.editing.value = '0'
        expect(wrapper.vm.summaryLines.map(l => l.text)).toEqual(['message.plan_get_agents_all'])
        await wrapper.setProps({ currencies: ['USD', 'INR'] })
        wrapper.vm.editing.prices.INR = { unit_price: '', renew_price: '', offer_percent: '' }
        wrapper.vm.applyEditor()
        expect(wrapper.vm.editing).not.toBeNull() // INR has no price

        wrapper.vm.editing.prices.INR.unit_price = '700'
        wrapper.vm.applyEditor()
        expect(wrapper.vm.editing).toBeNull()
    })

    it('agents are added and removed like any other feature', async () => {
        const wrapper = await make()
        const removeAgents = wrapper.findAll('tbody tr')[0].findAll('button').find(b => b.attributes('aria-label')?.startsWith('message.plan_feature_remove'))
        expect(removeAgents).toBeTruthy()
        await removeAgents.trigger('click')
        expect(wrapper.vm.rows.some(r => r.code === 'agents')).toBe(false)

        wrapper.vm.addFeature('1')
        expect(wrapper.vm.rows[0]).toMatchObject({ code: 'agents', mode: 'included' })
        expect(wrapper.vm.editing.code).toBe('agents')
    })

    it('adding agents starts at one agent, not unlimited (0000 on the license)', async () => {
        const entitlements = ENTITLEMENTS.map(e => (e.code === 'agents' ? { ...e, mode: 'none', value: null, prices: {} } : e))
        globalThis.mockHttp.onGet('/plan/7/entitlements').reply(200, { data: { entitlements, live_licenses: 3 } })
        const wrapper = mount(PlanEntitlements, {
            props: { planId: 7, currencies: ['USD'], period: '1 Year' },
            global: { stubs: ['AppModal', 'loader', 'ToolTip', 'Tooltip'], components: { 'action-button': ActionButton } },
        })
        await flushPromises()
        wrapper.vm.addFeature('1')
        expect(wrapper.vm.editing.value).toBe('1')
        wrapper.vm.addFeature('3') // any other feature keeps its value
        expect(wrapper.vm.editing.value).toBe('10')
    })

    it('saves every feature with the plan', async () => {
        const wrapper = await make()
        globalThis.mockHttp.onPost('/plan/7/entitlements').reply(200, {})
        await wrapper.vm.save()
        const sent = JSON.parse(globalThis.mockHttp.history.post[0].data).entitlements
        expect(sent.find(e => e.id === 1)).toEqual(expect.objectContaining({ mode: 'included', value: '5' }))
        expect(sent.find(e => e.id === 1)).not.toHaveProperty('unlimited')
        expect(sent.find(e => e.id === 4).value).toEqual({ ends: 'subscription' })
    })
})
