import { mount } from '@vue/test-utils';
import TextField from '../../../../../../Resources/js/components/Reusable/FormField/TextField.vue';

describe('TextField.vue', () => {
    const props = {
        label: 'Test Label',
        value: 'initial value',
        name: 'test_field',
        onChange: jest.fn()
    };

    it('renders text input by default', () => {
        const wrapper = mount(TextField, {
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

        expect(wrapper.find('input[type="text"]').exists()).toBe(true);
        expect(wrapper.find('input').element.value).toBe('initial value');
    });

    it('renders textarea when type is textarea', () => {
        const wrapper = mount(TextField, {
            props: {
                ...props,
                type: 'textarea'
            },
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' }
                }
            }
        });

        expect(wrapper.find('textarea').exists()).toBe(true);
    });

    it('renders password input when type is password', () => {
        const wrapper = mount(TextField, {
            props: {
                ...props,
                type: 'password'
            },
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' }
                }
            }
        });

        expect(wrapper.find('input[type="password"]').exists()).toBe(true);
        expect(wrapper.find('.eye-icon').exists()).toBe(true);
    });

    it('toggles password visibility when eye icon is clicked', async () => {
        const wrapper = mount(TextField, {
            props: {
                ...props,
                type: 'password'
            },
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' }
                }
            }
        });

        const eyeIcon = wrapper.find('.eye-icon');
        expect(wrapper.find('input[type="password"]').exists()).toBe(true);

        await eyeIcon.trigger('click');
        expect(wrapper.find('input[type="text"]').exists()).toBe(true);
        expect(wrapper.vm.showPassword).toBe(true);

        await eyeIcon.trigger('click');
        expect(wrapper.find('input[type="password"]').exists()).toBe(true);
        expect(wrapper.vm.showPassword).toBe(false);
    });

    it('calls onChange when input value changes', async () => {
        const wrapper = mount(TextField, {
            props,
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' }
                }
            }
        });

        const input = wrapper.find('input');
        await input.setValue('new value');
        expect(props.onChange).toHaveBeenCalledWith('new value', 'test_field');
    });

    it('updates internal state when value prop changes', async () => {
        const wrapper = mount(TextField, {
            props,
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' }
                }
            }
        });

        await wrapper.setProps({ value: 'updated value' });
        expect(wrapper.vm.changedValue).toBe('updated value');
    });

    it('calls keyupListener when key is released', async () => {
        const keyupListener = jest.fn();
        const wrapper = mount(TextField, {
            props: {
                ...props,
                keyupListener
            },
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' }
                }
            }
        });

        await wrapper.find('input').trigger('keyup');
        expect(keyupListener).toHaveBeenCalled();
    });

    it('disables the input field when disabled prop is true', () => {
        const wrapper = mount(TextField, {
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

        expect(wrapper.find('input').element.disabled).toBe(true);
    });
});
