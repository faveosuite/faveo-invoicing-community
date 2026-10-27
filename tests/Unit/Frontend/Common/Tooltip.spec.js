import { mount } from '@vue/test-utils'
import Tooltip from '@/components/Reusable/Tooltip.vue'

const mountTooltip = (props = {}) =>
    mount(Tooltip, {
        props: { message: 'Help text', ...props },
    })

describe('Tooltip.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mountTooltip()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders a span wrapper', () => {
        expect(wrapper.element.tagName).toBe('SPAN')
    })

    it('renders the question circle icon', () => {
        expect(wrapper.find('.fa-question-circle').exists()).toBe(true)
    })

    it('sets the title attribute to message', () => {
        expect(wrapper.attributes('title')).toBe('Help text')
    })

    it('uses custom message prop', () => {
        wrapper = mountTooltip({ message: 'Custom tooltip content' })
        expect(wrapper.attributes('title')).toBe('Custom tooltip content')
    })

    it('has cursor help on icon', () => {
        // cursor: help now lives in a scoped CSS class rather than an inline
        // style, so we assert the class is applied instead of the style attr.
        const icon = wrapper.find('.fa-question-circle')
        expect(icon.classes()).toContain('tooltip-icon')
    })

    it('uses default size of medium', () => {
        expect(wrapper.props('size')).toBe('medium')
    })

    it('accepts custom size prop', () => {
        wrapper = mountTooltip({ size: 'small' })
        expect(wrapper.props('size')).toBe('small')
    })

    it('applies font size style from size prop', () => {
        wrapper = mountTooltip({ size: '14px' })
        const icon = wrapper.find('.fa-question-circle')
        expect(icon.attributes('style')).toContain('font-size: 14px')
    })
})
