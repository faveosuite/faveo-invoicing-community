import { mount } from '@vue/test-utils';
import LogsTrace from '../../../../../Resources/js/components/Reusable/LogsTrace.vue';
import { getSubStringValue } from '../../../../../Resources/js/helpers/extraLogics';

// Mock the helper and components
jest.mock('../../../../../Resources/js/helpers/extraLogics', () => ({
    lang: jest.fn((str) => str),
    getSubStringValue: jest.fn((value, count) => {
        if (value && value.length > count) {
            return value.substring(0, count) + '...';
        }
        return value;
    })
}));

describe('LogsTrace.vue', () => {
    const data = {
        trace: 'This is a long trace message that should be truncated by the subStr method because it exceeds forty characters.'
    };

    it('renders the truncated trace and read more link', () => {
        const wrapper = mount(LogsTrace, {
            props: { data },
            global: {
                stubs: {
                    'logs-modal': true
                }
            }
        });

        const traceParagraph = wrapper.find('#logs_trace');
        expect(traceParagraph.text()).toContain('This is a long trace message that should...');
        expect(wrapper.find('#logs_read_more').exists()).toBe(true);
    });

    it('opens the LogsModal when read more is clicked', async () => {
        const wrapper = mount(LogsTrace, {
            props: { data },
            global: {
                stubs: {
                    'logs-modal': true
                }
            }
        });

        await wrapper.find('#logs_read_more').trigger('click');
        expect(wrapper.vm.showModal).toBe(true);
        expect(wrapper.findComponent({ name: 'logs-modal' }).exists()).toBe(true);
    });

    it('closes the LogsModal when onClose is called', async () => {
        const wrapper = mount(LogsTrace, {
            props: { data },
            global: {
                stubs: {
                    'logs-modal': true
                }
            }
        });

        wrapper.vm.showModal = true;
        await wrapper.vm.$nextTick();

        wrapper.vm.onClose();
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.showModal).toBe(false);
    });
});
