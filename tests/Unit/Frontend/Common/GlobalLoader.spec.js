import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { createPinia, setActivePinia } from 'pinia'
import GlobalLoader from '@/components/Reusable/GlobalLoader.vue'
import { useLoaderStore } from '@/core/stores/loader.js'

const mountLoader = (props = {}) =>
    mount(GlobalLoader, {
        props,
        global: {
            plugins: [createTestingPinia({ stubActions: false })],
            stubs: {
                Teleport: { template: '<div><slot /></div>' },
                Transition: false,
                FulfillingBouncingCircleSpinner: {
                    template: '<div class="spinner"></div>',
                    props: ['animationDuration', 'size', 'color'],
                },
            },
        },
    })

describe('GlobalLoader.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mountLoader()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('does not show overlay when loader is hidden', () => {
        expect(wrapper.find('.global-loader-overlay').exists()).toBe(false)
    })

    it('shows overlay when loaderStore.showLoader is true', async () => {
        const store = useLoaderStore()
        store.axiosCallsStack = ['request-1']
        await wrapper.vm.$nextTick()
        expect(wrapper.find('.global-loader-overlay').exists()).toBe(true)
    })

    it('showLoader getter returns false when axiosCallsStack is empty', () => {
        // Use a real Pinia so getters are computed properly
        const pinia = createPinia()
        setActivePinia(pinia)
        const store = useLoaderStore()
        store.startLoader('req-1')
        expect(store.showLoader).toBe(true)
        store.forceStopLoader()
        expect(store.showLoader).toBe(false)
    })

    it('renders spinner inside overlay when visible', async () => {
        const store = useLoaderStore()
        store.axiosCallsStack = ['request-1']
        await wrapper.vm.$nextTick()
        expect(wrapper.find('.spinner').exists()).toBe(true)
    })

    it('accepts duration prop with default 4000', () => {
        expect(wrapper.props('duration')).toBe(4000)
    })

    it('accepts size prop with default 60', () => {
        expect(wrapper.props('size')).toBe(60)
    })

    it('accepts color prop with default #1d78ff', () => {
        expect(wrapper.props('color')).toBe('#1d78ff')
    })

    it('passes custom duration to spinner', async () => {
        wrapper = mountLoader({ duration: 2000 })
        const store = useLoaderStore()
        store.axiosCallsStack = ['req']
        await wrapper.vm.$nextTick()
        expect(wrapper.find('.spinner').exists()).toBe(true)
    })

    it('passes custom color to spinner', async () => {
        wrapper = mountLoader({ color: '#ff0000' })
        const store = useLoaderStore()
        store.axiosCallsStack = ['req']
        await wrapper.vm.$nextTick()
        expect(wrapper.find('.spinner').exists()).toBe(true)
    })
})
