import { mount } from '@vue/test-utils'
import TextareaField from '@/components/Reusable/FormField/TextareaField.vue'

describe('TextareaField.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(TextareaField, {
            slots: { default: '<textarea class="test-textarea"></textarea>' },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the textareafield-wrapper container', () => {
        expect(wrapper.find('.textareafield-wrapper').exists()).toBe(true)
    })

    it('renders slotted content', () => {
        expect(wrapper.find('.test-textarea').exists()).toBe(true)
    })

    it('renders without slot content', () => {
        wrapper = mount(TextareaField)
        expect(wrapper.find('.textareafield-wrapper').exists()).toBe(true)
    })

    it('accepts any slot content', () => {
        wrapper = mount(TextareaField, {
            slots: { default: '<div class="custom-content">Content</div>' },
        })
        expect(wrapper.find('.custom-content').exists()).toBe(true)
    })

    it('is a simple slot wrapper with no props', () => {
        expect(Object.keys(wrapper.props())).toHaveLength(0)
    })
})
