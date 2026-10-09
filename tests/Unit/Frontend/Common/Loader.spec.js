import { mount } from '@vue/test-utils'
import Loader from '@/components/Reusable/Loader.vue'

const mountLoader = (props = {}) =>
    mount(Loader, {
        props,
        global: {
            stubs: {
                FulfillingBouncingCircleSpinner: {
                    template: '<div class="spinner" :data-duration="animationDuration" :data-size="size" :data-color="color"></div>',
                    props: ['animationDuration', 'size', 'color'],
                },
            },
        },
    })

describe('Loader.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mountLoader()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the container div', () => {
        expect(wrapper.find('div').exists()).toBe(true)
    })

    it('renders the spinner component', () => {
        expect(wrapper.find('.spinner').exists()).toBe(true)
    })

    it('passes default duration of 4000', () => {
        expect(wrapper.find('.spinner').attributes('data-duration')).toBe('4000')
    })

    it('passes default size of 60', () => {
        expect(wrapper.find('.spinner').attributes('data-size')).toBe('60')
    })

    it('passes default color #1d78ff', () => {
        expect(wrapper.find('.spinner').attributes('data-color')).toBe('#1d78ff')
    })

    it('passes custom duration prop', () => {
        wrapper = mountLoader({ duration: 2000 })
        expect(wrapper.find('.spinner').attributes('data-duration')).toBe('2000')
    })

    it('passes custom size prop', () => {
        wrapper = mountLoader({ size: 30 })
        expect(wrapper.find('.spinner').attributes('data-size')).toBe('30')
    })

    it('passes custom color prop', () => {
        wrapper = mountLoader({ color: '#ff0000' })
        expect(wrapper.find('.spinner').attributes('data-color')).toBe('#ff0000')
    })

    it('has justify-content-center class on container', () => {
        expect(wrapper.find('div').classes()).toContain('justify-content-center')
    })
})
