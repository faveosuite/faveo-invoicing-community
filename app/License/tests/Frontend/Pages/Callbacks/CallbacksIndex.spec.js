import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import CallbacksIndex from '../../../../../Resources/js/Pages/Callbacks/CallbacksIndex.vue'

jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDateTime: (v) => v }) }))

describe('CallbacksIndex.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        wrapper = mount(CallbacksIndex, {
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

    it('defaults to license tab on mount', () => {
        expect(wrapper.vm.activeTab).toBe('license')
    })

    it('sets license endpoint on mount', () => {
        expect(wrapper.vm.endPoint).toContain('/api/admin/showLicenseCallbacks')
    })

    it('sets license columns on mount', () => {
        expect(wrapper.vm.columns).toEqual(['product_title', 'license', 'callback_ip', 'callback_domain', 'callback_date_time', 'callback_status'])
    })

    it('renders nav tabs', () => {
        const tabs = wrapper.findAll('.nav-item')
        expect(tabs.length).toBe(2)
    })

    it('license tab is active by default', () => {
        const licenseTab = wrapper.findAll('.nav-item')[0].find('.nav-link')
        expect(licenseTab.classes()).toContain('active')
    })

    it('switches to update tab on click', async () => {
        const updateTabItem = wrapper.findAll('.nav-item')[1]
        await updateTabItem.trigger('click')
        expect(wrapper.vm.activeTab).toBe('update')
    })

    it('sets update endpoint when update tab is clicked', async () => {
        const updateTabItem = wrapper.findAll('.nav-item')[1]
        await updateTabItem.trigger('click')
        expect(wrapper.vm.endPoint).toContain('/api/admin/showUpdateCallbacks')
    })

    it('sets update columns when update tab is clicked', async () => {
        const updateTabItem = wrapper.findAll('.nav-item')[1]
        await updateTabItem.trigger('click')
        expect(wrapper.vm.columns).toEqual(['product_title', 'version', 'callback_ip', 'callback_types', 'callback_date_time', 'callback_status'])
    })

    // ── License tab templates ─────────────────────────────────────────────────

    it('license tab: product_title template returns em dash when null', () => {
        expect(wrapper.vm.tableOptions.templates.product_title(null, { product_title: null, product_id: null })).toBe('—')
    })

    it('license tab: product_title template returns vnode when present', () => {
        const result = wrapper.vm.tableOptions.templates.product_title(null, { product_title: 'PA', product_id: 1 })
        expect(result).toBeTruthy()
        expect(result.props).toBeDefined()
    })

    it('license tab: callback_status returns success badge', () => {
        const result = wrapper.vm.tableOptions.templates.callback_status(null, { callback_status: 1 })
        expect(result.props.class).toContain('bg-success')
    })

    it('license tab: callback_status returns danger badge', () => {
        const result = wrapper.vm.tableOptions.templates.callback_status(null, { callback_status: 0 })
        expect(result.props.class).toContain('bg-danger')
    })

    it('license tab: callback_domain returns em dash when null', () => {
        expect(wrapper.vm.tableOptions.templates.callback_domain(null, { callback_domain: null })).toBe('—')
    })

    it('license tab: callback_domain returns anchor when present', () => {
        const result = wrapper.vm.tableOptions.templates.callback_domain(null, { callback_domain: 'test.com' })
        expect(result.props.href).toContain('test.com')
    })

    it('license tab: license template returns em dash when null', () => {
        expect(wrapper.vm.tableOptions.templates.license(null, { license_code: null, license_id: null })).toBe('—')
    })

    it('license tab: license template returns vnode when present', () => {
        const result = wrapper.vm.tableOptions.templates.license(null, { license_code: 'ABCD1234', license_id: 5 })
        expect(result).toBeTruthy()
        expect(result.props).toBeDefined()
    })

    // ── Update tab templates ──────────────────────────────────────────────────

    it('update tab: product_title template returns em dash when null', async () => {
        wrapper.vm.updateData('update')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tableOptions.templates.product_title(null, { product_title: null, product_id: null })).toBe('—')
    })

    it('update tab: product_title template returns vnode when present', async () => {
        wrapper.vm.updateData('update')
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.templates.product_title(null, { product_title: 'PA', product_id: 2 })
        expect(result).toBeTruthy()
        expect(result.props).toBeDefined()
    })

    it('update tab: version template returns em dash when null', async () => {
        wrapper.vm.updateData('update')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tableOptions.templates.version(null, { version_number: null, version_id: null })).toBe('—')
    })

    it('update tab: version template returns vnode when present', async () => {
        wrapper.vm.updateData('update')
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.templates.version(null, { version_number: '2.0', version_id: 3 })
        expect(result).toBeTruthy()
        expect(result.props).toBeDefined()
    })

    it('update tab: callback_status returns success badge', async () => {
        wrapper.vm.updateData('update')
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.templates.callback_status(null, { callback_status: 1 })
        expect(result.props.class).toContain('bg-success')
    })

    it('switches back to license tab when license tab is clicked', async () => {
        const updateTabItem = wrapper.findAll('.nav-item')[1]
        await updateTabItem.trigger('click')

        const licenseTabItem = wrapper.findAll('.nav-item')[0]
        await licenseTabItem.trigger('click')

        expect(wrapper.vm.activeTab).toBe('license')
        expect(wrapper.vm.endPoint).toContain('/api/admin/showLicenseCallbacks')
    })
})
