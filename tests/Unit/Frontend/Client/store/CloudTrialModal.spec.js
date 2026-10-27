jest.mock('@/helpers/formUtils.js', () => ({ scrollToFirstError: jest.fn() }))
const mockPush = jest.fn(() => Promise.resolve())
const mockCurrentRoute = { value: { fullPath: '/store?group=1', meta: {} } }
jest.mock('vue-router', () => ({ useRouter: () => ({ push: mockPush, currentRoute: mockCurrentRoute }) }))

import { mount, flushPromises } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { useAuthStore } from '@/core/stores/auth'
import CloudTrialModal, { PENDING_TRIAL_KEY } from '@/themes/porto/components/store/CloudTrialModal.vue'

const cloudData = {
    cloud_subdomain: 'cloud.example.com',
    products: [{ id: 10, name: 'Helpdesk' }, { id: 11, name: 'ServiceDesk' }],
    data_centers: [{ id: 1, name: 'US' }, { id: 2, name: 'EU' }],
}

describe('CloudTrialModal.vue — guest trial resumes after login', () => {
    let axiosMock

    async function openModal({ loggedIn }) {
        const pinia = createTestingPinia()
        useAuthStore(pinia).user = loggedIn ? { id: 1, role: 'user' } : null
        const wrapper = mount(CloudTrialModal, {
            props: { show: false },
            global: { plugins: [pinia], stubs: ['Modal', 'Alert', 'ClientField', 'DynamicSelect', 'action-button', 'loader'] },
        })
        await wrapper.setProps({ show: true })
        await flushPromises()
        return wrapper
    }

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('store/cloud-products').reply(200, { data: cloudData })
        sessionStorage.clear()
        mockCurrentRoute.value = { fullPath: '/store?group=1', meta: {} }
    })

    afterEach(() => {
        axiosMock.restore()
        jest.clearAllMocks()
    })

    it('guest submit saves the form and sends them to login with a way back to this page', async () => {
        const wrapper = await openModal({ loggedIn: false })
        wrapper.vm.domain = 'acme'
        wrapper.vm.selectedProduct = cloudData.products[1]
        wrapper.vm.selectedDataCenter = cloudData.data_centers[1]

        await wrapper.vm.submit()

        expect(JSON.parse(sessionStorage.getItem(PENDING_TRIAL_KEY))).toEqual({ domain: 'acme', product_id: 11, data_center_id: 2 })
        expect(mockPush).toHaveBeenCalledWith({ path: '/login', query: { redirect: '/store?group=1' } })
        expect(axiosMock.history.post).toHaveLength(0)
    })

    it('guest on a login-type page goes to plain /login (no redirect back to it)', async () => {
        mockCurrentRoute.value = { fullPath: '/login', meta: { guestOnly: true } }
        const wrapper = await openModal({ loggedIn: false })

        await wrapper.vm.submit()

        expect(mockPush).toHaveBeenCalledWith('/login')
    })

    it('reopening after login fills in the saved form once, then forgets it', async () => {
        sessionStorage.setItem(PENDING_TRIAL_KEY, JSON.stringify({ domain: 'acme', product_id: 11, data_center_id: 2 }))

        const wrapper = await openModal({ loggedIn: true })

        expect(wrapper.vm.domain).toBe('acme')
        expect(wrapper.vm.selectedProduct).toEqual(cloudData.products[1])
        expect(wrapper.vm.selectedDataCenter).toEqual(cloudData.data_centers[1])
        expect(sessionStorage.getItem(PENDING_TRIAL_KEY)).toBeNull()
        expect(axiosMock.history.post).toHaveLength(0) // user still confirms; nothing auto-submits
    })

    it('opens with an empty form when nothing was saved', async () => {
        const wrapper = await openModal({ loggedIn: true })

        expect(wrapper.vm.domain).toBe('')
        expect(wrapper.vm.selectedProduct).toBeNull()
    })

    it('ignores broken saved data instead of crashing', async () => {
        sessionStorage.setItem(PENDING_TRIAL_KEY, '{not json')

        const wrapper = await openModal({ loggedIn: true })

        expect(wrapper.vm.domain).toBe('')
        expect(sessionStorage.getItem(PENDING_TRIAL_KEY)).toBeNull()
    })
})
