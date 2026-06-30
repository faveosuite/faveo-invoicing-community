jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDateTime: (v) => v, formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import LicensesIndex from '../../../../../Resources/js/Pages/License/LicensesIndex.vue'

describe('LicensesIndex.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('/api/admin/viewLicenses').reply(200, {
            data: { data: [], total: 0 }
        })

        wrapper = mount(LicensesIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'ColumnSelector', 'table-actions', 'inline-loader', 'action-button', 'delete-modal'],
                directives: { tooltip: () => {} },
            },
        })
    })

    afterEach(() => {
        axiosMock.restore()
    })

    it('renders the component', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the card title', () => {
        expect(wrapper.find('.card-title').exists()).toBeTruthy()
        expect(wrapper.find('.card-title').text()).toBe('all_licenses')
    })

    it('renders a link to the create license page', () => {
        const link = wrapper.find('a')
        expect(link.exists()).toBeTruthy()
    })

    it('renders a data-table component', () => {
        expect(wrapper.find('data-table-stub').exists()).toBeTruthy()
    })

    it('defines dataColumns with license_code', () => {
        expect(wrapper.vm.dataColumns).toContain('license_code')
    })

    it('defines dataColumns with actions', () => {
        expect(wrapper.vm.dataColumns).toContain('actions')
    })

    it('defines all expected default columns', () => {
        const expected = [
            'license_code', 'client_email', 'product_title', 'license_order_number',
            'license_domain', 'license_ip', 'license_date', 'installation_counts',
            'call_backs_count', 'latest_call_backs', 'license_limit', 'license_expire_date',
            'license_updates_date', 'license_support_date', 'license_status', 'actions'
        ]
        expected.forEach(col => {
            expect(wrapper.vm.dataColumns).toContain(col)
        })
    })

    it('onColumnsChange updates dataColumns to provided keys', async () => {
        wrapper.vm.onColumnsChange(['license_code', 'license_status'])
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.dataColumns).toEqual(['license_code', 'license_status'])
    })

    it('onColumnsChange resets to DEFAULT_COLUMNS when empty array given', async () => {
        wrapper.vm.onColumnsChange([])
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.dataColumns).toContain('license_code')
        expect(wrapper.vm.dataColumns).toContain('actions')
    })

    it('renders an app-alert stub', () => {
        expect(wrapper.find('app-alert-stub').exists()).toBeTruthy()
    })

    it('options has sortable and filterable defined', () => {
        expect(wrapper.vm.options.sortable).toContain('product_title')
        expect(wrapper.vm.options.filterable).toContain('product_title')
    })

    it('columnLabels contains expected keys', () => {
        expect(wrapper.vm.columnLabels).toHaveProperty('license_code')
        expect(wrapper.vm.columnLabels).toHaveProperty('client_email')
        expect(wrapper.vm.columnLabels).toHaveProperty('license_status')
    })

    it('endPoint contains viewLicenses', () => {
        expect(wrapper.vm.endPoint).toContain('viewLicenses')
    })

    // ── requestAdapter ────────────────────────────────────────────────────────

    it('requestAdapter maps all fields with trim', () => {
        const result = wrapper.vm.options.requestAdapter({ orderBy: 'license_code', ascending: true, query: '  abc  ', limit: 10, page: 2 })
        expect(result.sort_field).toBe('license_code')
        expect(result.sort_order).toBe('asc')
        expect(result.search_query).toBe('abc')
        expect(result.perPage).toBe(10)
        expect(result.page).toBe(2)
    })

    it('requestAdapter falls back to id and desc when orderBy empty', () => {
        const result = wrapper.vm.options.requestAdapter({ orderBy: '', ascending: false, query: '', limit: 25, page: 1 })
        expect(result.sort_field).toBe('id')
        expect(result.sort_order).toBe('desc')
    })

    // ── responseAdapter ───────────────────────────────────────────────────────

    it('responseAdapter maps rows with edit_url, view_url, delete_url, keyVal, idVal', () => {
        const rows = [{ id: 5, license_code: 'ABCD1234', client_email: 'a@b.com' }]
        const result = wrapper.vm.options.responseAdapter({ data: { data: { data: rows, total: 1 } } })
        expect(result.count).toBe(1)
        expect(result.data[0].edit_url).toContain('/licenses/5/edit')
        expect(result.data[0].view_url).toContain('/licenses/5/view')
        expect(result.data[0].delete_url).toContain('/api/admin/license/delete')
        expect(result.data[0].keyVal).toBe('id')
        expect(result.data[0].idVal).toBe(5)
    })

    it('responseAdapter returns empty for no rows', () => {
        const result = wrapper.vm.options.responseAdapter({ data: { data: { data: [], total: 0 } } })
        expect(result.data).toHaveLength(0)
        expect(result.count).toBe(0)
    })

    // ── templates ─────────────────────────────────────────────────────────────

    it('template license_ip returns em dash when null', () => {
        expect(wrapper.vm.options.templates.license_ip(null, { license_ip: null })).toBe('—')
    })

    it('template license_ip returns value when present', () => {
        expect(wrapper.vm.options.templates.license_ip(null, { license_ip: '1.2.3.4' })).toBe('1.2.3.4') // NOSONAR    })

    it('template latest_call_backs returns em dash when null', () => {
        expect(wrapper.vm.options.templates.latest_call_backs(null, { latest_call_backs: null })).toBe('—')
    })

    it('template license_expire_date returns em dash when null', () => {
        expect(wrapper.vm.options.templates.license_expire_date(null, { license_expire_date: null })).toBe('—')
    })

    it('template license_expire_date returns value when present', () => {
        expect(wrapper.vm.options.templates.license_expire_date(null, { license_expire_date: '2025-12-31' })).toBe('2025-12-31')
    })

    it('template license_code returns em dash when null', () => {
        expect(wrapper.vm.options.templates.license_code(null, { license_code: null, id: null })).toBe('—')
    })

    it('template license_code returns vnode with formatted code when present', () => {
        const result = wrapper.vm.options.templates.license_code(null, { license_code: 'ABCD1234', id: 10 })
        expect(result).toBeTruthy()
        expect(result.props).toBeDefined()
    })

    it('template product_title returns em dash when null', () => {
        expect(wrapper.vm.options.templates.product_title(null, { product_title: null, product_id: null })).toBe('—')
    })

    it('template product_title returns vnode when present', () => {
        const result = wrapper.vm.options.templates.product_title(null, { product_title: 'Product A', product_id: 3 })
        expect(result).toBeTruthy()
        expect(result.props).toBeDefined()
    })

    it('template client_email returns em dash when null', () => {
        expect(wrapper.vm.options.templates.client_email(null, { client_email: null })).toBe('—')
    })

    it('template client_email returns vnode when present', () => {
        const result = wrapper.vm.options.templates.client_email(null, { client_email: 'user@example.com', client_id: 7 })
        expect(result).toBeTruthy()
        expect(result.props).toBeDefined()
    })

    it('template license_domain returns em dash when null', () => {
        expect(wrapper.vm.options.templates.license_domain(null, { license_domain: null })).toBe('—')
    })

    it('template license_domain returns anchor when present', () => {
        const result = wrapper.vm.options.templates.license_domain(null, { license_domain: 'example.com' })
        expect(result).toBeTruthy()
        expect(result.props.href).toContain('example.com')
    })

    it('template license_status returns success badge for truthy status', () => {
        const result = wrapper.vm.options.templates.license_status(null, { license_status: 1 })
        expect(result.props.class).toContain('bg-success')
    })

    it('template license_status returns danger badge for falsy status', () => {
        const result = wrapper.vm.options.templates.license_status(null, { license_status: 0 })
        expect(result.props.class).toContain('bg-danger')
    })

    it('template license_order_number returns em dash when null', () => {
        expect(wrapper.vm.options.templates.license_order_number(null, { license_order_number: null })).toBe('—')
    })

    it('template license_order_number returns anchor when present', () => {
        const result = wrapper.vm.options.templates.license_order_number(null, { license_order_number: 'ORD-001' })
        expect(result).toBeTruthy()
        expect(result.props.href).toContain('ORD-001')
    })

    it('headings defines all expected labels', () => {
        expect(wrapper.vm.options.headings.product_title).toBe('product')
        expect(wrapper.vm.options.headings.license_status).toBe('status')
        expect(wrapper.vm.options.headings.actions).toBe('actions')
    })
})
