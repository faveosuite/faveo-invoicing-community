import { mount } from '@vue/test-utils';
import FormFieldTemplate from '../../../../../../Resources/js/components/Reusable/FormField/FormFieldTemplate.vue';
import { createStore } from 'vuex';

describe('FormFieldTemplate.vue', () => {
    let store;
    let getters;

    beforeEach(() => {
        getters = {
            getValidationErrors: () => ({
                test_field: 'Error message'
            })
        };
        store = createStore({
            getters
        });
    });

    const props = {
        label: 'Test Label',
        name: 'test_field',
        required: true,
        hint: 'Test Hint'
    };

    it('renders the label and required asterisk', () => {
        const wrapper = mount(FormFieldTemplate, {
            props,
            global: {
                plugins: [store],
                stubs: {
                    'tool-tip': true
                }
            }
        });

        expect(wrapper.find('label').text()).toBe('Test Label');
        expect(wrapper.find('.is-danger').text()).toBe('*');
    });

    it('renders the hint tooltip', () => {
        const wrapper = mount(FormFieldTemplate, {
            props,
            global: {
                plugins: [store],
                stubs: {
                    'tool-tip': {
                        template: '<div class="tooltip-stub">{{message}}</div>',
                        props: ['message']
                    }
                }
            }
        });

        expect(wrapper.find('.tooltip-stub').text()).toBe('Test Hint');
    });

    it('renders error message when error exists in store', () => {
        const wrapper = mount(FormFieldTemplate, {
            props,
            global: {
                plugins: [store],
                stubs: {
                    'tool-tip': true
                }
            }
        });

        expect(wrapper.find('.error-block').text()).toBe('Error message');
        expect(wrapper.classes()).toContain('has-error');
    });

    it('calls onClickEvent when new button is clicked', async () => {
        const onClickEvent = jest.fn();
        const wrapper = mount(FormFieldTemplate, {
            props: {
                ...props,
                showNewButton: true,
                onClickEvent
            },
            global: {
                plugins: [store],
                stubs: {
                    'tool-tip': true
                },
                mocks: {
                    trans: (val) => val
                }
            }
        });

        await wrapper.find('a.btn-light').trigger('click');
        expect(onClickEvent).toHaveBeenCalledWith('test_field');
    });
});
