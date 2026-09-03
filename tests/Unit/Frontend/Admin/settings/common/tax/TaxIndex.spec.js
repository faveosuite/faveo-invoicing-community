jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: {}, query: {} }),
    RouterLink: { template: '<a><slot /></a>' },
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { errorHandler } from '@/helpers/responseHandler'
import TaxIndex from '@/pages/admin/settings/common/tax/TaxIndex.vue'

const SelectFieldStub = {
    name: 'DynamicSelect',
    props: { name: String, onChange: Function, value: [Object, String, Number, Array], elements: Array, clearable: Boolean, searchable: Boolean, multiple: Boolean, taggable: Boolean },
    template: '<div class="sf-stub" :data-name="name"></div>',
}

describe('TaxIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/tax-options/).reply(200, {
            data: {
                options: { tax_enable: 1, inclusive: 0, rounding: 0, tax_based_on: 'billing' },
                additional_tax_classes: '',
                classes: [{ slug: '', name: 'Standard' }],
            },
        })
        globalThis.mockHttp.onPost(/\/taxes\/option/).reply(200, { message: 'Saved' })

        wrapper = mount(TaxIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'DataTable', 'DynamicSelect', 'action-button',
                    'DeleteModal', 'loader', 'inline-loader', 'router-link',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches tax options on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.get[0].url).toMatch(/\/tax-options/)
    })

    it('handles 500 error on loadOptions', async () => {
        globalThis.mockHttp.onGet(/\/tax-options/).reply(500)
        const w = mount(TaxIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'DynamicSelect', 'action-button', 'DeleteModal', 'loader', 'inline-loader', 'router-link'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })

    it('submits options via POST to /taxes/option', async () => {
        await flushPromises()
        await wrapper.vm.saveOptions()
        await flushPromises()

        expect(globalThis.mockHttp.history.post.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.post[0].url).toMatch(/\/taxes\/option/)
    })

    it('confirmBulkDelete sets pendingBulkDelete when items are selected', async () => {
        await flushPromises()
        wrapper.vm.selected = [1, 2]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toEqual({ select: [1, 2] })
    })

    it('renders DataTable stub', () => {
        expect(wrapper.findComponent({ name: 'DataTable' }).exists() || wrapper.html().includes('datatable')).toBeTruthy()
    })
})

describe('TaxIndex.vue — branch coverage', () => {
    let wrapper

    beforeEach(async () => {
        globalThis.mockHttp.onGet(/\/tax-options/).reply(200, {
            data: {
                options: { tax_enable: 1, inclusive: 0, rounding: 0, tax_based_on: 'billing' },
                additional_tax_classes: 'ClassA\nClassB',
                classes: [{ slug: '', name: 'Standard' }, { slug: 'reduced', name: 'Reduced' }],
            },
        })
        globalThis.mockHttp.onPost(/\/taxes\/option/).reply(200, { message: 'Saved' })
        wrapper = mount(TaxIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'DynamicSelect', 'action-button', 'DeleteModal', 'loader', 'inline-loader', 'router-link'],
            },
        })
        wrapper.vm.dtRef = { refresh: jest.fn(), tableData: [] }
        await flushPromises()
    })

    // ── allSelected / toggleAll ──────────────────────────────────────
    it('allSelected is false when tableData is empty', () => {
        expect(wrapper.vm.allSelected).toBe(false)
    })
    it('allSelected is true when all rows are selected', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selected = [1, 2]
        expect(wrapper.vm.allSelected).toBe(true)
    })
    it('toggleAll selects all rows when checked', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.toggleAll({ target: { checked: true } })
        expect(wrapper.vm.selected).toEqual(expect.arrayContaining([1, 2]))
    })
    it('toggleAll deselects all when unchecked', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }], refresh: jest.fn() }
        wrapper.vm.selected = [1, 99]
        wrapper.vm.toggleAll({ target: { checked: false } })
        expect(wrapper.vm.selected).not.toContain(1)
        expect(wrapper.vm.selected).toContain(99)
    })

    // ── onClassesChange ─────────────────────────────────────────────
    it('onClassesChange maps string values to {name} objects', () => {
        wrapper.vm.onClassesChange(['Standard', 'Reduced'])
        expect(wrapper.vm.additionalClasses).toEqual([{ name: 'Standard' }, { name: 'Reduced' }])
    })
    it('onClassesChange handles null/undefined gracefully', () => {
        wrapper.vm.onClassesChange(null)
        expect(wrapper.vm.additionalClasses).toEqual([])
    })
    it('onClassesChange filters blank entries', () => {
        wrapper.vm.onClassesChange(['Standard', '  ', ''])
        expect(wrapper.vm.additionalClasses).toEqual([{ name: 'Standard' }])
    })

    // ── setActiveClass ───────────────────────────────────────────────
    it('setActiveClass updates activeClass and clears selection', () => {
        wrapper.vm.dtRef = { refresh: jest.fn(), tableData: [] }
        wrapper.vm.selected = [1, 2]
        wrapper.vm.setActiveClass('reduced')
        expect(wrapper.vm.activeClass).toBe('reduced')
        expect(wrapper.vm.selected).toEqual([])
    })

    // ── orderedClasses / createTo ───────────────────────────────────
    it('orderedClasses puts Standard first', () => {
        const ordered = wrapper.vm.orderedClasses
        expect(ordered[0].slug).toBe('')
    })
    it('createTo includes class query when activeClass is set', () => {
        wrapper.vm.activeClass = 'reduced'
        expect(wrapper.vm.createTo.query).toEqual({ class: 'reduced' })
    })
    it('createTo has empty query when activeClass is empty string', () => {
        wrapper.vm.activeClass = ''
        expect(wrapper.vm.createTo.query).toEqual({})
    })

    // ── templates ────────────────────────────────────────────────────
    describe('templates', () => {
        const tpl = () => wrapper.vm.tableOptions.templates
        it('name returns — when falsy', () => { expect(tpl().name(null, {})).toBe('—') })
        it('country returns — when falsy', () => { expect(tpl().country(null, {})).toBe('—') })
        it('state returns — when falsy', () => { expect(tpl().state(null, {})).toBe('—') })
        it('rate returns — when undefined', () => { expect(tpl().rate(null, {})).toBe('—') })
        it('rate returns value when 0', () => { expect(tpl().rate(null, { rate: 0 })).toBe(0) })
        it('priority returns — when undefined', () => { expect(tpl().priority(null, {})).toBe('—') })
        it('compound returns — when falsy', () => { expect(tpl().compound(null, {})).toBe('—') })
        it('active returns — when falsy', () => { expect(tpl().active(null, {})).toBe('—') })
    })

    // ── requestAdapter ───────────────────────────────────────────────
    describe('requestAdapter', () => {
        const adapt = (d) => wrapper.vm.tableOptions.requestAdapter(d)
        it('defaults sort-field to created_at', () => {
            expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-field']).toBe('created_at')
        })
        it('includes activeClass in tax_class param', () => {
            wrapper.vm.activeClass = 'reduced'
            expect(adapt({ ascending: true, query: '', page: 1, limit: 10 }).tax_class).toBe('reduced')
        })
        it('defaults to desc when no orderBy (latest first)', () => {
            expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-order']).toBe('desc')
        })
        it('sets desc when ascending=false', () => {
            expect(adapt({ ascending: false, query: '', page: 1, limit: 10 })['sort-order']).toBe('desc')
        })
    })

    // ── loadOptions: parses additional_tax_classes ───────────────────
    it('loadOptions parses additional_tax_classes into name objects', async () => {
        expect(wrapper.vm.additionalClasses).toEqual(
            expect.arrayContaining([{ name: 'ClassA' }, { name: 'ClassB' }])
        )
    })

    // ── confirmBulkDelete: empty selection ───────────────────────────
    it('confirmBulkDelete does nothing when selection is empty', () => {
        wrapper.vm.selected = []
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })
})

describe('TaxIndex.vue — SelectField onChange branch coverage', () => {
    let wrapper

    beforeEach(async () => {
        globalThis.mockHttp.onGet(/\/tax-options/).reply(200, {
            data: {
                options: { tax_enable: 0, inclusive: 0, rounding: 0, tax_based_on: 'billing' },
                additional_tax_classes: '',
                classes: [{ slug: '', name: 'Standard' }],
            },
        })
        globalThis.mockHttp.onPost(/\/taxes\/option/).reply(200, { message: 'ok' })

        wrapper = mount(TaxIndex, {
            global: {
                plugins: [createTestingPinia()],
                components: { DynamicSelect: SelectFieldStub },
                stubs: ['AppAlert', 'DataTable', 'DeleteModal', 'action-button', 'loader', 'router-link', 'TextField'],
            },
        })
        await flushPromises()
    })

    function getSF(name) {
        return wrapper.findAllComponents(SelectFieldStub).find(c => c.props('name') === name)
    }

    it('tax_enable onChange sets options.tax_enable from val.id', () => {
        getSF('tax_enable')?.props('onChange')({ id: 1 })
        expect(wrapper.vm.options.tax_enable).toBe(1)
    })

    it('tax_enable onChange sets 0 when val is null', () => {
        getSF('tax_enable')?.props('onChange')(null)
        expect(wrapper.vm.options.tax_enable).toBe(0)
    })

    it('inclusive onChange sets options.inclusive from val.id', () => {
        getSF('inclusive')?.props('onChange')({ id: 1 })
        expect(wrapper.vm.options.inclusive).toBe(1)
    })

    it('inclusive onChange sets 0 when val is null', () => {
        getSF('inclusive')?.props('onChange')(null)
        expect(wrapper.vm.options.inclusive).toBe(0)
    })

    it('rounding onChange sets options.rounding from val.id', () => {
        getSF('rounding')?.props('onChange')({ id: 1 })
        expect(wrapper.vm.options.rounding).toBe(1)
    })

    it('rounding onChange sets 0 when val is null', () => {
        getSF('rounding')?.props('onChange')(null)
        expect(wrapper.vm.options.rounding).toBe(0)
    })

    it('tax_based_on onChange sets options.tax_based_on from val.id', () => {
        getSF('tax_based_on')?.props('onChange')({ id: 'base' })
        expect(wrapper.vm.options.tax_based_on).toBe('base')
    })

    it('tax_based_on onChange defaults to billing when val is null', () => {
        getSF('tax_based_on')?.props('onChange')(null)
        expect(wrapper.vm.options.tax_based_on).toBe('billing')
    })
})
