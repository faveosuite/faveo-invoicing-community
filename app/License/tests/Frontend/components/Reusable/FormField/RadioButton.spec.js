import { mount } from '@vue/test-utils';
import RadioButton from '../../../../../../Resources/js/components/Reusable/FormField/RadioButton.vue';

describe('RadioButton.vue', () => {
    const props = {
        options: [
            { name: 'Option 1', value: 'v1' },
            { name: 'Option 2', value: 'v2' }
        ],
        value: 'v1',
        name: 'test_radio',
        label: 'Test Label',
        onChange: jest.fn()
    };

    it('renders labels and radio inputs', () => {
        const wrapper = mount(RadioButton, {
            props,
            global: {
                mocks: {
                    lang: (s) => s
                },
                stubs: {
                    'tool-tip': true
                }
            }
        });

        expect(wrapper.text()).toContain('Test Label');
        expect(wrapper.text()).toContain('Option 1');
        expect(wrapper.text()).toContain('Option 2');
        expect(wrapper.findAll('input[type="radio"]')).toHaveLength(2);
    });

    it('calls onChange when selection changes', async () => {
        const wrapper = mount(RadioButton, {
            props,
            global: {
                mocks: {
                    lang: (s) => s
                },
                stubs: {
                    'tool-tip': true
                }
            }
        });

        const radios = wrapper.findAll('input[type="radio"]');
        await radios[1].setValue();
        expect(props.onChange).toHaveBeenCalledWith('v2', 'test_radio');
    });

    it('updates internal state when value prop changes', async () => {
        const wrapper = mount(RadioButton, {
            props,
            global: {
                mocks: {
                    lang: (s) => s
                },
                stubs: {
                    'tool-tip': true
                }
            }
        });

        await wrapper.setProps({ value: 'v2' });
        expect(wrapper.vm.checked).toBe('v2');
    });

    it('disables radio buttons when disabled prop is true', () => {
        const wrapper = mount(RadioButton, {
            props: {
                ...props,
                disabled: true
            },
            global: {
                mocks: {
                    lang: (s) => s
                },
                stubs: {
                    'tool-tip': true
                }
            }
        });

        const radios = wrapper.findAll('input[type="radio"]');
        expect(radios[0].element.disabled).toBe(true);
        expect(radios[1].element.disabled).toBe(true);
    });
});
