jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDateTime: (v) => v, formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import InstallationsIndex from '../../../../../Resources/js/Pages/Installations/InstallationsIndex.vue'

describe('InstallationsIndex.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        wrapper = mount(InstallationsIndex, {
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
        expect(wrapper.find('.card-title').text()).toBe('all_installations')
    })

    it('renders DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('initializes columns correctly', () => {
        expect(wrapper.vm.columns).toContain('product_title')
        expect(wrapper.vm.columns).toContain('installation_domain')
        expect(wrapper.vm.columns).toContain('actions')
    })

    it('endPoint contains viewInstallations', () => {
        expect(wrapper.vm.endPoint).toContain('/api/admin/viewInstallations')
    })

    it('requestAdapter maps correctly with trim', () => {
        const result = wrapper.vm.options.requestAdapter({ orderBy: 'product_title', ascending: false, query: '  hello  ', limit: 15, page: 3 })
        expect(result['sort-field']).toBe('product_title')
        expect(result['sort-order']).toBe('desc')
        expect(result['search-query']).toBe('hello')
        expect(result.limit).toBe(15)
        expect(result.page).toBe(3)
    })

    it('requestAdapter falls back to id when orderBy empty', () => {
        const result = wrapper.vm.options.requestAdapter({ orderBy: '', ascending: true, query: '', limit: 10, page: 1 })
        expect(result['sort-field']).toBe('id')
    })

    it('responseAdapter maps rows with edit_url, view_url, keyVal, idVal', () => {
        const rows = [{ id: 7, product_title: 'Product A', installation_domain: 'example.com' }]
        const result = wrapper.vm.options.responseAdapter({ data: { data: { data: rows, total: 1 } } })
        expect(result.count).toBe(1)
        expect(result.data[0].edit_url).toContain('/installations/7/edit')
        expect(result.data[0].view_url).toContain('/installations/7/view')
        expect(result.data[0].delete_url).toContain('/api/admin/installations/delete')
        expect(result.data[0].keyVal).toBe('id')
        expect(result.data[0].idVal).toBe(7)
    })

    it('responseAdapter returns count 0 for empty result', () => {
        const result = wrapper.vm.options.responseAdapter({ data: { data: { data: [], total: 0 } } })
        expect(result.data).toHaveLength(0)
        expect(result.count).toBe(0)
    })

    it('template product_title returns em dash when missing', () => {
        expect(wrapper.vm.options.templates.product_title(null, { product_title: null, product_id: null })).toBe('—')
    })

    it('template product_title returns router-link vnode when present', () => {
        const result = wrapper.vm.options.templates.product_title(null, { product_title: 'Product A', product_id: 5 })
        expect(result).toBeTruthy()
        expect(result.props).toBeDefined()
    })

    it('columnsClasses defines correct classes', () => {
        expect(wrapper.vm.options.columnsClasses.product_title).toBe('dt-name')
        expect(wrapper.vm.options.columnsClasses.actions).toBe('dt-action')
    })

    it('template installation_date calls formatDate', () => {
        const result = wrapper.vm.options.templates.installation_date(null, { installation_date: '2024-01-01' })
        expect(result).toBe('2024-01-01')
    })

    it('template license returns em dash when null', () => {
        expect(wrapper.vm.options.templates.license(null, { license_code: null, license_id: null })).toBe('—')
    })

    it('template license returns vnode with formatted code when present', () => {
        const result = wrapper.vm.options.templates.license(null, { license_code: 'ABCD1234', license_id: 3 })
        expect(result).toBeTruthy()
        expect(result.props).toBeDefined()
    })

    it('template client_email returns em dash when null', () => {
        expect(wrapper.vm.options.templates.client_email(null, { client_email: null })).toBe('—')
    })

    it('template client_email returns vnode when present', () => {
        const result = wrapper.vm.options.templates.client_email(null, { client_email: 'user@test.com', client_id: 2 })
        expect(result).toBeTruthy()
        expect(result.props).toBeDefined()
    })

    it('template installation_domain returns em dash when null', () => {
        expect(wrapper.vm.options.templates.installation_domain(null, { installation_domain: null })).toBe('—')
    })

    it('template installation_domain returns anchor when present', () => {
        const result = wrapper.vm.options.templates.installation_domain(null, { installation_domain: 'site.com' })
        expect(result.props.href).toContain('site.com')
    })

    it('template installation_status returns success badge', () => {
        const result = wrapper.vm.options.templates.installation_status(null, { installation_status: 1 })
        expect(result.props.class).toContain('bg-success')
    })

    it('template installation_status returns danger badge', () => {
        const result = wrapper.vm.options.templates.installation_status(null, { installation_status: 0 })
        expect(result.props.class).toContain('bg-danger')
    })

    it('headings defines all expected labels', () => {
        expect(wrapper.vm.options.headings.product_title).toBe('product')
        expect(wrapper.vm.options.headings.installation_status).toBe('status')
    })
})
