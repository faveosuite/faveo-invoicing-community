jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDateTime: (v) => v, formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import ViewLicenseReports from '../../../../../Resources/js/Pages/Report/ViewLicenseReports.vue'

describe('ViewLicenseReports.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        wrapper = mount(ViewLicenseReports, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'table-actions', 'inline-loader'],
            },
        })
    })

    afterEach(() => {
        axiosMock.restore()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the card title', () => {
        expect(wrapper.find('.card-title').text()).toBe('view_license_reports')
    })

    it('renders DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('initializes columns correctly', () => {
        expect(wrapper.vm.columns).toEqual(['report_text', 'user', 'license', 'report_date_time', 'report_status'])
    })

    it('initializes sortable options', () => {
        expect(wrapper.vm.options.sortable).toContain('product_title')
        expect(wrapper.vm.options.sortable).toContain('report_text')
    })

    it('initializes filterable options', () => {
        expect(wrapper.vm.options.filterable).toContain('product_title')
    })

    it('endPoint contains reportLicense', () => {
        expect(wrapper.vm.endPoint).toContain('/api/admin/reportLicense')
    })

    it('requestAdapter maps sort field with fallback to report_date_time', () => {
        const result = wrapper.vm.options.requestAdapter({ orderBy: '', ascending: false, query: '  search  ', limit: 10, page: 1 })
        expect(result['sort-field']).toBe('report_date_time')
        expect(result['sort-order']).toBe('desc')
        expect(result['search-query']).toBe('search')
    })

    it('requestAdapter uses provided orderBy when set', () => {
        const result = wrapper.vm.options.requestAdapter({ orderBy: 'product_title', ascending: true, query: '', limit: 25, page: 2 })
        expect(result['sort-field']).toBe('product_title')
        expect(result['sort-order']).toBe('asc')
    })

    it('responseAdapter maps rows with keyVal and idVal', () => {
        const rows = [{ id: 5, report_text: 'test', report_status: 1 }]
        const result = wrapper.vm.options.responseAdapter({ data: { data: { data: rows, total: 1 } } })
        expect(result.count).toBe(1)
        expect(result.data[0].keyVal).toBe('id')
        expect(result.data[0].idVal).toBe(5)
    })

    it('responseAdapter returns empty array for no data', () => {
        const result = wrapper.vm.options.responseAdapter({ data: { data: { data: [], total: 0 } } })
        expect(result.data).toHaveLength(0)
        expect(result.count).toBe(0)
    })
})
