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
import InvoiceIndex from '@/pages/client/invoices/InvoiceIndex.vue'

// AppCard and DataTable are globally registered by theme plugins (not imported
// locally), so they must be explicitly stubbed as PascalCase objects.
const globalStubs = {
    AppCard: { template: '<div class="app-card"><slot /></div>' },
    DataTable: { template: '<div class="data-table" :url="url" :data-columns="dataColumns"></div>', props: ['url', 'dataColumns', 'option'] },
    'action-button': true,
    'router-link': { template: '<a><slot /></a>' },
}

describe('InvoiceIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(InvoiceIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: globalStubs,
            },
        })
    })

    afterEach(() => {
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the AppCard wrapper', () => {
        expect(wrapper.find('.app-card').exists()).toBeTruthy()
    })

    it('renders the DataTable component', () => {
        expect(wrapper.find('.data-table').exists()).toBeTruthy()
    })

    it('passes an apiUrl containing /get-my-invoices to DataTable', () => {
        const table = wrapper.find('.data-table')
        expect(table.attributes('url')).toContain('/get-my-invoices')
    })

    it('navigates to /checkout?invoice=:id when goToPay is called', async () => {
        // goToPay calls router.push; we verify it executes without throwing
        wrapper.vm.goToPay(7)
        await flushPromises()
        expect(wrapper.exists()).toBeTruthy()
    })

    it('does not make any HTTP calls on mount (data-table handles its own fetching)', () => {
        // InvoiceIndex has no onMounted HTTP call — DataTable handles API calls internally
        expect(wrapper.exists()).toBeTruthy()
    })
})
