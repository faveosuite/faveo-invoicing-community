jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MSG91Reports from '@/pages/admin/settings/logs/MSG91Reports.vue'

describe('MSG91Reports.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/getMsgFilters/).reply(200, {
            data: { statuses: [], sources: [], actions: [] },
        })

        wrapper = mount(MSG91Reports, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'DataTable', 'MSG91Filter', 'inline-loader',
                ],
            },
        })
        wrapper.vm.dtRef = { refresh: jest.fn() }
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the card', () => {
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
        wrapper.vm.activeFilters = { status: 'success' }
        wrapper.vm.onFilterReset()
        expect(wrapper.vm.activeFilters).toEqual({})
    })
})
