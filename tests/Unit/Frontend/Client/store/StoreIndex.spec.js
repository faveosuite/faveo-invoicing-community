jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn(), replace: jest.fn() }),
    useRoute: () => ({ params: {}, query: {} }),
}))
jest.mock('@/core/composables/useBreadcrumb.js', () => ({ setPageTitle: jest.fn() }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { errorHandler } from '@/helpers/responseHandler'
import { setPageTitle } from '@/core/composables/useBreadcrumb.js'
import StoreIndex from '@/pages/client/store/StoreIndex.vue'

const groupsFixture = [
    { id: 1, name: 'Hosting Plans', tagline: 'Best hosting', status: true },
    { id: 2, name: 'Domains', tagline: '', status: false },
]

const productsFixture = {
    group: { id: 1, name: 'Hosting Plans', tagline: 'Best hosting', status: true },
    products: [
        { id: 10, name: 'Basic Plan', price: 9.99 },
        { id: 11, name: 'Pro Plan', price: 19.99 },
    ],
    currency_symbol: '$',
    cloud_subdomain: 'cloud.example.com',
    data_centers: [{ id: 1, name: 'US East' }],
}

describe('StoreIndex.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)

        axiosMock.onGet('/store/groups').reply(200, { data: groupsFixture })
        axiosMock.onGet('/store/1/products').reply(200, { data: productsFixture })

        wrapper = mount(StoreIndex, {
            global: {
                plugins: [createTestingPinia({
                    initialState: {
                        auth: { user: { id: 1, first_name: 'John', email: 'john@example.com' }, isAuthenticated: true },
                    },
                })],
                stubs: ['loader', 'pricing-table', 'app-alert'],
            },
        })
    })

    afterEach(() => {
        axiosMock.restore()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('calls GET /store/groups on mount', async () => {
        await flushPromises()

        const reqs = axiosMock.history.get.filter(r => r.url === '/store/groups')
        expect(reqs.length).toBeGreaterThan(0)
    })

    it('calls GET /store/{id}/products after loading groups', async () => {
        await flushPromises()

        const reqs = axiosMock.history.get.filter(r => r.url === '/store/1/products')
        expect(reqs.length).toBeGreaterThan(0)
    })

    it('populates groups after mount', async () => {
        await flushPromises()

        expect(wrapper.vm.groups).toHaveLength(2)
    })

    it('populates products after loading', async () => {
        await flushPromises()

        expect(wrapper.vm.products).toHaveLength(2)
    })

    it('sets currencySymbol from API response', async () => {
        await flushPromises()

        expect(wrapper.vm.currencySymbol).toBe('$')
    })

    it('sets currentGroup from API response', async () => {
        await flushPromises()

        expect(wrapper.vm.currentGroup).not.toBeNull()
        expect(wrapper.vm.currentGroup.name).toBe('Hosting Plans')
    })

    it('sets loadingGroups to false after mount', async () => {
        await flushPromises()

        expect(wrapper.vm.loadingGroups).toBe(false)
    })

    it('sets loadingProducts to false after loading products', async () => {
        await flushPromises()

        expect(wrapper.vm.loadingProducts).toBe(false)
    })

    it('calls setPageTitle with group name after loading products', async () => {
        await flushPromises()

        expect(setPageTitle).toHaveBeenCalledWith('Hosting Plans')
    })

    it('calls errorHandler when groups fetch fails', async () => {
        axiosMock.reset()
        axiosMock.onGet('/store/groups').reply(500, { message: 'Server error' })

        mount(StoreIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'pricing-table', 'app-alert'],
            },
        })
        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
    })

    it('calls errorHandler when products fetch fails', async () => {
        axiosMock.reset()
        axiosMock.onGet('/store/groups').reply(200, { data: groupsFixture })
        axiosMock.onGet('/store/1/products').reply(500, { message: 'Server error' })

        mount(StoreIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'pricing-table', 'app-alert'],
            },
        })
        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
    })

    it('keeps empty products array when groups list is empty', async () => {
        axiosMock.reset()
        axiosMock.onGet('/store/groups').reply(200, { data: [] })

        const w = mount(StoreIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'pricing-table', 'app-alert'],
            },
        })
        await flushPromises()

        expect(w.vm.products).toHaveLength(0)
        expect(w.vm.groups).toHaveLength(0)
    })

    it('sets dataCenters from API response', async () => {
        await flushPromises()

        expect(wrapper.vm.dataCenters).toHaveLength(1)
        expect(wrapper.vm.dataCenters[0].name).toBe('US East')
    })

    it('selectGroup calls the correct products endpoint', async () => {
        await flushPromises()

        axiosMock.onGet('/store/2/products').reply(200, {
            data: {
                group: { id: 2, name: 'Domains' },
                products: [],
                currency_symbol: '€',
                cloud_subdomain: '',
                data_centers: [],
            },
        })

        await wrapper.vm.selectGroup(2)
        await flushPromises()

        const reqs = axiosMock.history.get.filter(r => r.url === '/store/2/products')
        expect(reqs.length).toBeGreaterThan(0)
    })
})
