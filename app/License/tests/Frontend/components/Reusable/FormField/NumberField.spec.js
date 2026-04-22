import { mount } from '@vue/test-utils';
import NumberField from '../../../../../../Resources/js/components/Reusable/FormField/NumberField.vue';

describe('NumberField.vue', () => {
    const props = {
        label: 'Quantity',
        name: 'quantity',
        value: 10,
        onChange: jest.fn()
    };

    it('renders the label and input with value', () => {
        const wrapper = mount(NumberField, {
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

        expect(wrapper.find('label').text()).toBe('Quantity');
        expect(wrapper.find('input#number').element.value).toBe('10');
    });

    it('calls onChange when input value changes', async () => {
        const wrapper = mount(NumberField, {
            props,
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' }
                }
            }
        });

        const input = wrapper.find('input#number');
        await input.setValue('20');
        // trigger input event manually if needed, but setValue usually handles it
        expect(props.onChange).toHaveBeenCalledWith('20', 'quantity');
    });

    it('prevents non-numeric keypress', async () => {
        const wrapper = mount(NumberField, {
            props,
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' }
                }
            }
        });

        const input = wrapper.find('input#number');
        const event = {
            which: 65, // 'A'
            preventDefault: jest.fn()
        };
        wrapper.vm.checkValue(event);
        expect(event.preventDefault).toHaveBeenCalled();
    });

    it('allows numeric keypress', async () => {
        const wrapper = mount(NumberField, {
            props,
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' }
                }
            }
        });

        const input = wrapper.find('input#number');
        const event = {
            which: 50, // '2'
            preventDefault: jest.fn()
        };
        wrapper.vm.checkValue(event);
        expect(event.preventDefault).not.toHaveBeenCalled();
    });
});
