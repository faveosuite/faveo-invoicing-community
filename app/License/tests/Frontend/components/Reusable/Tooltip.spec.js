import { mount } from '@vue/test-utils';
import Tooltip from '../../../../../Resources/js/components/Reusable/Tooltip.vue';

describe('Tooltip.vue', () => {
    it('renders the tooltip with the provided message', () => {
        const wrapper = mount(Tooltip, {
            props: {
                message: 'This is a tooltip message',
                size: '14px'
            },
            global: {
                directives: {
                    tooltip: jest.fn()
                }
            }
        });

        const icon = wrapper.find('i.fas.fa-question-circle');
        expect(icon.exists()).toBe(true);
        expect(icon.element.style.fontSize).toBe('14px');
    });

    it('renders with default size if not provided', () => {
        const wrapper = mount(Tooltip, {
            props: {
                message: 'This is a tooltip message'
            },
            global: {
                directives: {
                    tooltip: jest.fn()
                }
            }
        });

        const icon = wrapper.find('i.fas.fa-question-circle');
        // 'medium' is the default value in props, but it's applied to v-bind:style="{fontSize:size}"
        // In some environments, it might not be a valid font-size value if it's just 'medium'
        expect(icon.element.style.fontSize).toBe('medium');
    });
});
