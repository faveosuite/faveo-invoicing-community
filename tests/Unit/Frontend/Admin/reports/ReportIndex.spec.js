jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ReportIndex from '@/pages/admin/reports/ReportIndex.vue'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'DynamicSelect', 'AppModal', 'ImageUpload', 'PhoneField',
    'RecaptchaProvider', 'RecaptchaCheckbox', 'RecaptchaV2Invisible', 'RecaptchaV3',
    'ZohoCard', 'spinner-loader', 'router-link',
]

describe('ReportIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/reports/).reply(200, { data: [] })
        globalThis.mockHttp.onDelete(/\/reports/).reply(200, { data: { message: 'Deleted' } })
        wrapper = mount(ReportIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders a DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('renders DeleteModal stub when pendingBulkDelete is set', async () => {
        expect(wrapper.find('delete-modal-stub').exists()).toBe(false)
        wrapper.vm.pendingBulkDelete = { report_ids: [1] }
        await wrapper.vm.$nextTick()
        expect(wrapper.find('delete-modal-stub').exists()).toBe(true)
    })

    it('initialises selected as empty array', () => {
        expect(Array.isArray(wrapper.vm.selected)).toBe(true)
        expect(wrapper.vm.selected.length).toBe(0)
    })

    it('toggleRow adds id to selected', () => {
        wrapper.vm.toggleRow(42)
        expect(wrapper.vm.selected).toContain(42)
    })

    it('toggleRow removes id when already selected', () => {
        wrapper.vm.selected = [42]
        wrapper.vm.toggleRow(42)
        expect(wrapper.vm.selected).not.toContain(42)
    })

    it('confirmBulkDelete sets pendingBulkDelete when rows are selected', () => {
        wrapper.vm.selected = [1, 2]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).not.toBeNull()
    })

    it('confirmBulkDelete does nothing when nothing selected', () => {
        wrapper.vm.selected = []
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })
})
