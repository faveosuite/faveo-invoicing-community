import { mount } from '@vue/test-utils';
import DynamicSelect from '../../../../../../Resources/js/components/Reusable/FormField/DynamicSelect.vue';

describe('DynamicSelect.vue (static-select)', () => {
    const props = {
        name: 'static_select',
        onChange: jest.fn(),
        elements: [
            { id: 1, name: 'Option 1' },
            { id: 2, name: 'Option 2' }
        ],
        label: 'Static Label'
    };

    it('renders the label and options', () => {
        const wrapper = mount(DynamicSelect, {
            props,
            global: {
                stubs: {
                    'form-field-template': {
                        template: '<div><label>{{label}}</label><slot /></div>',
                        props: ['label']
                    },
                    'v-select': true
                }
            }
        });

        expect(wrapper.text()).toContain('Static Label');
        expect(wrapper.vm.listElements).toHaveLength(2);
    });

    it('calls onChange when value changes', async () => {
        const wrapper = mount(DynamicSelect, {
            props,
            global: {
                stubs: {
                    'form-field-template': true,
                    'v-select': true
                }
            }
        });

        await wrapper.vm.onValueChange({ id: 1, name: 'Option 1' });
        expect(props.onChange).toHaveBeenCalledWith({ id: 1, name: 'Option 1' }, 'static_select');
    });

    it('filters elements on search', async () => {
        const wrapper = mount(DynamicSelect, {
            props,
            global: {
                stubs: {
                    'form-field-template': true,
                    'v-select': true
                }
            }
        });

        wrapper.vm.onSearch('Option 1');
        // debounce is 350ms, but we can call filterListElements directly or wait
        wrapper.vm.filterListElements();
        expect(wrapper.vm.listElements).toHaveLength(1);
        expect(wrapper.vm.listElements[0].name).toBe('Option 1');
    });
});
