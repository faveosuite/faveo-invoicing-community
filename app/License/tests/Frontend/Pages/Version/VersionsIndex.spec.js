import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import VersionsIndex from '../../../../../Resources/js/Pages/Version/VersionsIndex.vue'

jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDateTime: (v) => v, formatDate: (v) => v }) }))

describe('VersionsIndex.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        wrapper = mount(VersionsIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['custom-loader', 'alert', 'data-table', 'router-link', 'inline-loader', 'action-button', 'AppAlert', 'DataTable', 'table-actions'],
            },
        })
    })

    afterEach(() => {
        axiosMock.restore()
    })

    it('renders the component', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the card title with all_versions', () => {
        expect(wrapper.find('.card-title').text()).toBe('all_versions')
    })

    it('renders the DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('sets the correct endpoint', () => {
        const dataTableStub = wrapper.find('data-table-stub')
        expect(dataTableStub.attributes('url')).toContain('/api/admin/viewVersions')
    })

    it('requestAdapter maps sort field and trims query', () => {
        const result = wrapper.vm.options.requestAdapter({ orderBy: 'version_number', ascending: true, query: '  2.0  ', limit: 10, page: 1 })
        expect(result.sort_field).toBe('version_number')
        expect(result.sort_order).toBe('asc')
        expect(result.search_query).toBe('2.0')
    })

    it('requestAdapter falls back to id when orderBy empty', () => {
        const result = wrapper.vm.options.requestAdapter({ orderBy: '', ascending: false, query: '', limit: 25, page: 1 })
        expect(result.sort_field).toBe('id')
        expect(result.sort_order).toBe('desc')
    })

    it('responseAdapter maps rows with view_url, keyVal, idVal', () => {
        const rows = [{ id: 10, version_number: '3.0.0', product_title: 'Product A' }]
        const result = wrapper.vm.options.responseAdapter({ data: { data: { data: rows, total: 1 } } })
        expect(result.count).toBe(1)
        expect(result.data[0].view_url).toContain('/versions/10/view')
        expect(result.data[0].keyVal).toBe('id')
        expect(result.data[0].idVal).toBe(10)
    })

    it('template product_title returns em dash when missing', () => {
        expect(wrapper.vm.options.templates.product_title(null, { product_title: null, product_id: null })).toBe('—')
    })

    it('template product_title returns vnode when product exists', () => {
        const result = wrapper.vm.options.templates.product_title(null, { product_title: 'Product A', product_id: 2 })
        expect(result).toBeTruthy()
        expect(result.props).toBeDefined()
    })

    it('template version_number returns em dash when missing', () => {
        expect(wrapper.vm.options.templates.version_number(null, { version_number: null, id: null })).toBe('—')
    })

    it('columnsClasses defines correct classes', () => {
        expect(wrapper.vm.options.columnsClasses.version_number).toBe('dt-code')
        expect(wrapper.vm.options.columnsClasses.actions).toBe('dt-action')
    })

    it('template version_number returns vnode when both present', () => {
        const result = wrapper.vm.options.templates.version_number(null, { version_number: '2.0.0', id: 5 })
        expect(result).toBeTruthy()
        expect(result.props).toBeDefined()
    })

    it('template version_status returns success badge for active', () => {
        const result = wrapper.vm.options.templates.version_status(null, { version_status: 1 })
        expect(result.props.class).toContain('bg-success')
    })

    it('template version_status returns danger badge for inactive', () => {
        const result = wrapper.vm.options.templates.version_status(null, { version_status: 0 })
        expect(result.props.class).toContain('bg-danger')
    })

    it('template version_date calls formatDateTime', () => {
        const result = wrapper.vm.options.templates.version_date(null, { version_date: '2024-01-01 10:00:00' })
        expect(result).toBe('2024-01-01 10:00:00')
    })

    it('headings defines expected labels', () => {
        expect(wrapper.vm.options.headings.version_number).toBe('version')
        expect(wrapper.vm.options.headings.version_status).toBe('status')
    })
})
