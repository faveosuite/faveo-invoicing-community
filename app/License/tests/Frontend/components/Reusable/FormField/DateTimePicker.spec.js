import { mount } from '@vue/test-utils';
import DateTimePicker from '../../../../../../Resources/js/components/Reusable/FormField/DateTimePicker.vue';

describe('DateTimePicker.vue', () => {
    const props = {
        label: 'Date Time Label',
        value: '2023-10-27 10:00:00',
        name: 'date_time_field',
        onChange: jest.fn()
    };

    it('renders the label', () => {
        const wrapper = mount(DateTimePicker, {
            props,
            global: {
                stubs: {
                    'form-field-template': {
                        template: '<div><label>{{label}}</label><slot /></div>',
                        props: ['label']
                    },
                    'date-picker': true
                }
            }
        });

        expect(wrapper.text()).toContain('Date Time Label');
    });

    it('calls onChange when date time changes', async () => {
        const wrapper = mount(DateTimePicker, {
            props,
            global: {
                stubs: {
                    'form-field-template': true,
                    'date-picker': true
                }
            }
        });

        const newValue = '2023-10-28 11:00:00';
        await wrapper.vm.onDateTimeChange(newValue, 'date_time_field');
        expect(props.onChange).toHaveBeenCalledWith(newValue, 'date_time_field');
    });

    it('updates changedValue when value prop changes', async () => {
        const wrapper = mount(DateTimePicker, {
            props,
            global: {
                stubs: {
                    'form-field-template': true,
                    'date-picker': true
                }
            }
        });

        const newValue = '2023-10-29 12:00:00';
        await wrapper.setProps({ value: newValue });
        expect(wrapper.vm.changedValue).toBe(newValue);
    });
});
