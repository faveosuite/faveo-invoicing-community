jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { slug: 'about-us' }, query: {} }),
}))
jest.mock('@/core/composables/useBreadcrumb.js', () => ({ setPageTitle: jest.fn() }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { errorHandler } from '@/helpers/responseHandler'
import { setPageTitle } from '@/core/composables/useBreadcrumb.js'
import PageView from '@/pages/client/pages/PageView.vue'

describe('PageView.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('page-content/about-us').reply(200, {
            data: { name: 'About Us', content: '<p>Welcome!</p>' },
        })

        wrapper = mount(PageView, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'loader',
                    'router-link',
                ],
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

    it('calls GET page-content/:slug on mount', async () => {
        await flushPromises()
        expect(axiosMock.history.get.some(r => r.url.includes('page-content/about-us'))).toBe(true)
    })

    it('sets loading to false after fetch completes', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('sets page data after successful API call', async () => {
        await flushPromises()
        expect(wrapper.vm.page).toEqual({ name: 'About Us', content: '<p>Welcome!</p>' })
    })

    it('calls setPageTitle with the page name', async () => {
        await flushPromises()
        expect(setPageTitle).toHaveBeenCalledWith('About Us')
    })

    it('sets loading to false when API returns 500', async () => {
        axiosMock.onGet('page-content/about-us').reply(500)

        const w = mount(PageView, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'router-link'],
            },
        })
        await flushPromises()
        expect(w.vm.loading).toBe(false)
        w.unmount()
    })

    it('calls errorHandler when API returns 500', async () => {
        axiosMock.onGet('page-content/about-us').reply(500, { message: 'Not found' })

        const w = mount(PageView, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'router-link'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })

    it('leaves page as null when API returns 500', async () => {
        axiosMock.onGet('page-content/about-us').reply(500)

        const w = mount(PageView, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'router-link'],
            },
        })
        await flushPromises()
        expect(w.vm.page).toBeNull()
        w.unmount()
    })

    it('renders page content after successful load', async () => {
        await flushPromises()
        expect(wrapper.html()).toContain('Welcome!')
    })

    it('calls setPageTitle(null) on unmount', async () => {
        await flushPromises()
        wrapper.unmount()
        expect(setPageTitle).toHaveBeenCalledWith(null)
    })

    it('page is null when API returns empty data', async () => {
        axiosMock.onGet('page-content/about-us').reply(200, { data: null })

        const w = mount(PageView, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['loader', 'router-link'],
            },
        })
        await flushPromises()
        expect(w.vm.page).toBeNull()
        w.unmount()
    })
})
