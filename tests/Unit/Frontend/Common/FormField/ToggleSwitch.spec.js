import { mount } from '@vue/test-utils'
import ToggleSwitch from '@/components/Reusable/FormField/ToggleSwitch.vue'

describe('ToggleSwitch.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(ToggleSwitch, {
            slots: { default: '<button class="inner-toggle">Toggle</button>' },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the toggleswitch-wrapper container', () => {
        expect(wrapper.find('.toggleswitch-wrapper').exists()).toBe(true)
    })

    it('renders slotted content', () => {
        expect(wrapper.find('.inner-toggle').exists()).toBe(true)
    })

    it('renders without slot content', () => {
        wrapper = mount(ToggleSwitch)
        expect(wrapper.find('.toggleswitch-wrapper').exists()).toBe(true)
    })

    it('accepts any slot content', () => {
        wrapper = mount(ToggleSwitch, {
            slots: { default: '<span class="custom-span">Toggle Me</span>' },
        })
        expect(wrapper.find('.custom-span').text()).toBe('Toggle Me')
    })

    it('is a simple slot wrapper with no props', () => {
        expect(Object.keys(wrapper.props())).toHaveLength(0)
    })
})
