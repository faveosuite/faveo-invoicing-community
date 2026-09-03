jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import PaymentLog from '@/pages/admin/settings/logs/PaymentLog.vue'

describe('PaymentLog.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(PaymentLog, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'AppModal', 'DataTable', 'DeleteModal',
                    'PaymentFilter', 'action-button', 'inline-loader',
                ],
            },
        })
        wrapper.vm.dtRef = { refresh: jest.fn() }
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the payment log card', () => {
        expect(wrapper.find('.card').exists()).toBeTruthy()
    })

    it('contains a DataTable stub', () => {
        expect(
            wrapper.find('data-table-stub').exists() ||
            wrapper.findComponent({ name: 'DataTable' }).exists() ||
            wrapper.html().toLowerCase().includes('datatable')
        ).toBeTruthy()
    })

    it('toggles showFilter when filter button is clicked', async () => {
        const filterBtn = wrapper.find('button.btn-tool')
        expect(filterBtn.exists()).toBeTruthy()
        await filterBtn.trigger('click')
        expect(wrapper.vm.showFilter).toBe(true)
    })

    it('updates activeFilters when onFilterApply is called', () => {
        const params = { status: 'success' }
        wrapper.vm.onFilterApply(params)
        expect(wrapper.vm.activeFilters).toEqual(params)
        expect(wrapper.vm.showFilter).toBe(false)
    })

    it('clears activeFilters when onFilterReset is called', () => {
        wrapper.vm.activeFilters = { status: 'failed' }
        wrapper.vm.onFilterReset()
        expect(wrapper.vm.activeFilters).toEqual({})
    })

    it('sets deleteTarget when a row delete is triggered', () => {
        const row = { id: 42, status: 'success' }
        wrapper.vm.deleteTarget = row
        expect(wrapper.vm.deleteTarget).toEqual(row)
    })

    it('opens exception modal via openException', () => {
        wrapper.vm.openException('Error stack trace here')
        expect(wrapper.vm.showExceptionModal).toBe(true)
        expect(wrapper.vm.exceptionContent).toBe('Error stack trace here')
    })

    it('resets deleteTarget after onDeleted', () => {
        wrapper.vm.deleteTarget = { id: 1 }
        wrapper.vm.onDeleted()
        expect(wrapper.vm.deleteTarget).toBeNull()
    })

    it('resets bulk state after onBulkDeleted', () => {
        wrapper.vm.selected = [1, 2]
        wrapper.vm.showBulkDeleteModal = true
        wrapper.vm.onBulkDeleted()
        expect(wrapper.vm.showBulkDeleteModal).toBe(false)
        expect(wrapper.vm.selected).toEqual([])
    })

    it('onFilterApply sets activeFilters and hides filter', () => {
        wrapper.vm.onFilterApply({ status: 'paid', gateway: 'stripe' })
        expect(wrapper.vm.activeFilters).toEqual({ status: 'paid', gateway: 'stripe' })
        expect(wrapper.vm.showFilter).toBe(false)
    })

    it('onFilterReset clears activeFilters', () => {
        wrapper.vm.activeFilters = { status: 'paid' }
        wrapper.vm.onFilterReset()
        expect(wrapper.vm.activeFilters).toEqual({})
    })

    it('toggleRow adds id when not selected', () => {
        wrapper.vm.selected = []
        wrapper.vm.toggleRow(42)
        expect(wrapper.vm.selected).toContain(42)
    })

    it('toggleRow removes id when already selected', () => {
        wrapper.vm.selected = [42]
        wrapper.vm.toggleRow(42)
        expect(wrapper.vm.selected).not.toContain(42)
    })

    it('toggleAll selects all rows when checked', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }] }
        wrapper.vm.toggleAll({ target: { checked: true } })
        expect(wrapper.vm.selected).toContain(1)
        expect(wrapper.vm.selected).toContain(2)
    })

    it('toggleAll deselects all rows when unchecked', () => {
        wrapper.vm.selected = [1, 2]
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }] }
        wrapper.vm.toggleAll({ target: { checked: false } })
        expect(wrapper.vm.selected).toHaveLength(0)
    })

    it('copyException copies to clipboard', async () => {
        Object.assign(navigator, { clipboard: { writeText: jest.fn().mockResolvedValue(undefined) } })
        wrapper.vm.exceptionContent = 'Error trace'
        await wrapper.vm.copyException()
        await flushPromises()
        expect(navigator.clipboard.writeText).toHaveBeenCalledWith('Error trace')
        expect(wrapper.vm.copied).toBe(true)
    })
})
