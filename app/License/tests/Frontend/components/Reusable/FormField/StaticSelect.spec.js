import { mount } from '@vue/test-utils';
import StaticSelect from '../../../../../../Resources/js/components/Reusable/FormField/StaticSelect.vue';

jest.mock('../../../../../../Resources/js/helpers/extraLogics', () => ({
    getSubStringValue: jest.fn((val) => val)
}));

describe('StaticSelect.vue', () => {
    const props = {
        label: 'Test Select',
        elements: [
            { id: 1, name: 'Option 1' },
            { id: 2, name: 'Option 2' }
        ],
        name: 'test_select',
        value: 1,
        onChange: jest.fn()
    };

    it('renders label and options', () => {
        const wrapper = mount(StaticSelect, {
            props,
            global: {
                stubs: {
                    'form-field-template': {
                        template: '<div><label>{{label}}</label><slot /></div>',
                        props: ['label']
                    }
                }
            }
        });

        expect(wrapper.text()).toContain('Test Select');
        expect(wrapper.findAll('option')).toHaveLength(3); // "Select" + 2 options
        expect(wrapper.text()).toContain('Option 1');
        expect(wrapper.text()).toContain('Option 2');
    });

    it('calls onChange when selection changes', async () => {
        const wrapper = mount(StaticSelect, {
            props,
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' }
                }
            }
        });

        const select = wrapper.find('select');
        await select.setValue(2);
        expect(props.onChange).toHaveBeenCalledWith(2, 'test_select');
    });

    it('updates internal state when value prop changes', async () => {
        const wrapper = mount(StaticSelect, {
            props,
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' }
                }
            }
        });

        await wrapper.setProps({ value: 2 });
        expect(wrapper.vm.selectedValue).toBe(2);
    });

    it('hides empty select when hideEmptySelect prop is true', () => {
        const wrapper = mount(StaticSelect, {
            props: {
                ...props,
                hideEmptySelect: true
            },
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' }
                }
            }
        });

        expect(wrapper.findAll('option')).toHaveLength(2);
        expect(wrapper.find('option[value=""]').exists()).toBe(false);
    });

    it('disables the select field when disabled prop is true', () => {
        const wrapper = mount(StaticSelect, {
            props: {
                ...props,
                disabled: true
            },
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' }
                }
            }
        });

        expect(wrapper.find('select').element.disabled).toBe(true);
    });
});
