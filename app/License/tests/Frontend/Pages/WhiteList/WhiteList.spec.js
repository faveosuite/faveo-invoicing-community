jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDateTime: (v) => v, formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import WhiteList from '../../../../../Resources/js/Pages/WhiteList/WhiteList.vue'

describe('WhiteList.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        wrapper = mount(WhiteList, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'table-actions', 'inline-loader', 'delete-modal'],
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
        expect(wrapper.find('.card-title').text()).toBe('view_whitelist_ip')
    })

    it('renders DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('initializes columns correctly', () => {
        expect(wrapper.vm.columns).toContain('whitelist_host_ip')
        expect(wrapper.vm.columns).toContain('actions')
    })

    it('endPoint contains view-Whitelist', () => {
        expect(wrapper.vm.endPoint).toContain('/api/admin/view-Whitelist')
    })

    it('initializes sortable options', () => {
        expect(wrapper.vm.options.sortable).toContain('whitelist_host_comments')
    })

    it('requestAdapter maps sort field correctly', () => {
        const result = wrapper.vm.options.requestAdapter({ orderBy: 'whitelist_host_ip', ascending: true, query: 'test', limit: 10, page: 1 })
        expect(result.sort_field).toBe('whitelist_host_ip')
        expect(result.sort_order).toBe('asc')
        expect(result.search_query).toBe('test')
    })

    it('requestAdapter falls back to id when orderBy is empty', () => {
        const result = wrapper.vm.options.requestAdapter({ orderBy: '', ascending: false, query: '', limit: 25, page: 1 })
        expect(result.sort_field).toBe('id')
        expect(result.sort_order).toBe('desc')
    })

    it('responseAdapter maps rows with edit_url, delete_url, keyVal, idVal', () => {
        const rows = [{ id: 3, whitelist_host_ip: '192.168.0.1', whitelist_host_comments: 'office' }] // NOSONAR        const result = wrapper.vm.options.responseAdapter({ data: { data: { data: rows, total: 1 } } })
        expect(result.count).toBe(1)
        expect(result.data[0].edit_url).toContain('/whitelist/3/edit')
        expect(result.data[0].delete_url).toContain('/api/admin/delete-whitelist-ip')
        expect(result.data[0].keyVal).toBe('id')
        expect(result.data[0].idVal).toBe(3)
    })

    it('template whitelist_host_ip returns em dash when null', () => {
        expect(wrapper.vm.options.templates.whitelist_host_ip(null, { whitelist_host_ip: null })).toBe('—')
    })

    it('template whitelist_host_ip returns value when present', () => {
        expect(wrapper.vm.options.templates.whitelist_host_ip(null, { whitelist_host_ip: '10.0.0.1' })).toBe('10.0.0.1') // NOSONAR    })

    it('template whitelist_host_comments returns em dash when null', () => {
        expect(wrapper.vm.options.templates.whitelist_host_comments(null, { whitelist_host_comments: null })).toBe('—')
    })

    it('headings defines expected labels', () => {
        expect(wrapper.vm.options.headings.whitelist_host_ip).toBe('ip_address')
        expect(wrapper.vm.options.headings.actions).toBe('actions')
    })

    it('template whitelist_host_date calls formatDate', () => {
        const result = wrapper.vm.options.templates.whitelist_host_date(null, { whitelist_host_date: '2024-06-01' })
        expect(result).toBe('2024-06-01')
    })

    it('template whitelist_host_comments returns value when present', () => {
        expect(wrapper.vm.options.templates.whitelist_host_comments(null, { whitelist_host_comments: 'office' })).toBe('office')
    })
})
