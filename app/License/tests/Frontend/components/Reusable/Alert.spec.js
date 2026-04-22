import { mount } from '@vue/test-utils';
import Alert from '../../../../../Resources/js/components/Reusable/Alert.vue';
import { createStore } from 'vuex';

describe('Alert.vue', () => {
    let store;
    let actions;
    let getters;

    beforeEach(() => {
        actions = {
            unsetAlert: jest.fn(),
        };
        getters = {
            getAlertType: () => 'success',
            getAlertMessage: () => 'Operation successful',
            getAlertComponentName: () => 'test-component',
            getAlertDuration: () => 7000,
        };
        store = createStore({
            actions,
            getters,
        });
    });

    it('renders the alert when type is not empty and componentName matches', () => {
        const wrapper = mount(Alert, {
            props: {
                componentName: 'test-component',
            },
            global: {
                plugins: [store],
            },
        });

        expect(wrapper.find('.alert-container').exists()).toBe(true);
        expect(wrapper.find('.alert-success').exists()).toBe(true);
        expect(wrapper.text()).toContain('Operation successful');
    });

    it('does not render the alert when componentName does not match', () => {
        const wrapper = mount(Alert, {
            props: {
                componentName: 'other-component',
            },
            global: {
                plugins: [store],
            },
        });

        expect(wrapper.find('.alert-container').exists()).toBe(false);
    });

    it('calls unsetAlert action when dismiss button is clicked', async () => {
        const wrapper = mount(Alert, {
            props: {
                componentName: 'test-component',
            },
            global: {
                plugins: [store],
            },
        });

        await wrapper.find('button.close').trigger('click');
        expect(actions.unsetAlert).toHaveBeenCalled();
    });

    it('renders different icon for danger alert type', () => {
        getters.getAlertType = () => 'danger';
        store = createStore({ getters, actions });

        const wrapper = mount(Alert, {
            props: {
                componentName: 'test-component',
            },
            global: {
                plugins: [store],
            },
        });

        expect(wrapper.find('.fa-warning').exists()).toBe(true);
    });
});
