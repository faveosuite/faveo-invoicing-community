jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
const mockDownloadFile = jest.fn()
jest.mock('@/core/composables/useDownload', () => ({ useDownload: () => ({ downloadFile: mockDownloadFile }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ProductTableActions from '@/pages/admin/products/components/ProductTableActions.vue'

describe('ProductTableActions.vue', () => {
    let wrapper

    const mountComponent = (props = {}) =>
        mount(ProductTableActions, {
            props: {
                productId: 1,
                ...props,
            },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['router-link'],
            },
        })

    beforeEach(() => {
        wrapper = mountComponent()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders a router-link for edit', () => {
        expect(wrapper.find('router-link-stub').exists()).toBe(true)
    })

    it('accepts productId as number', () => {
        wrapper = mountComponent({ productId: 42 })
        expect(wrapper.exists()).toBeTruthy()
    })

    it('accepts productId as string', () => {
        wrapper = mountComponent({ productId: '99' })
        expect(wrapper.exists()).toBeTruthy()
    })

    it('does not render download button when downloadUrl is absent', () => {
        wrapper = mountComponent({ downloadUrl: null })
        expect(wrapper.find('button').exists()).toBe(false)
    })

    it('renders download button when downloadUrl is provided', () => {
        wrapper = mountComponent({ downloadUrl: 'https://example.com/file.zip' })
        expect(wrapper.find('button').exists()).toBe(true)
    })

    it('calls downloadFile when download button is clicked', async () => {
        wrapper = mountComponent({ downloadUrl: 'https://example.com/file.zip' })
        await wrapper.find('button').trigger('click')
        expect(mockDownloadFile).toHaveBeenCalledWith('https://example.com/file.zip')
    })
})
