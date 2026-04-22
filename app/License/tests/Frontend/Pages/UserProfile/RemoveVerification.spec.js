import { mount } from '@vue/test-utils';
import RemoveVerification from '../../../../../Resources/js/Pages/UserProfile/RemoveVerification.vue';
import axios from 'axios';
import { successHandler, errorHandler } from '../../../../../Resources/js/helpers/responseHandler';

jest.mock('axios');
jest.mock('../../../../../Resources/js/helpers/responseHandler');

describe('RemoveVerification.vue', () => {
    let globalConfig;
    let mockStore;

    beforeEach(() => {
        mockStore = {
            dispatch: jest.fn()
        };

        globalConfig = {
            stubs: {
                'modal': {
                    template: '<div><slot name="title"></slot><slot name="fields"></slot><slot name="controls"></slot></div>'
                },
                'custom-loader': true,
                'text-field': {
                    template: '<input @keyup="keyupListener" @input="onChange($event.target.value, name)" :name="name" />',
                    props: ['keyupListener', 'onChange', 'name']
                },
            },
            mocks: {
                lang: (msg) => msg,
                $store: mockStore,
            }
        };

        axios.post.mockReset();
    });

    it('renders correctly when showModal is true', () => {
        const wrapper = mount(RemoveVerification, {
            props: {
                showModal: true,
                onClose: jest.fn()
            },
            global: globalConfig,
        });

        expect(wrapper.find('h4').text()).toBe('turn_off_authenticator');
        expect(wrapper.text()).toContain('turn_off_authenticator_setup');
    });

    it('calls onRemove when Turn Off button is clicked', async () => {
        axios.post.mockResolvedValue({ data: { message: 'Success' } });
        const onClose = jest.fn();
        const removedFA = jest.fn();

        const wrapper = mount(RemoveVerification, {
            props: {
                showModal: true,
                onClose,
                removedFA
            },
            global: globalConfig,
        });

        await wrapper.find('button').trigger('click');

        expect(axios.post).toHaveBeenCalledWith('/api/admin/2fa/disable');
        expect(onClose).toHaveBeenCalled();
        expect(removedFA).toHaveBeenCalled();
        expect(successHandler).toHaveBeenCalled();
    });

    it('shows password field when disable fails with password_confirmation_required', async () => {
        axios.post.mockRejectedValue({
            response: {
                data: { message: 'password_confirmation_required' }
            }
        });

        const wrapper = mount(RemoveVerification, {
            props: {
                showModal: true,
                onClose: jest.fn()
            },
            global: globalConfig,
        });

        await wrapper.find('button').trigger('click');
        await new Promise(resolve => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.showPasswordRequiredFrom).toBe(true);
        expect(wrapper.find('input[name="password"]').exists()).toBe(true);
        expect(wrapper.find('button').text()).toContain('validate');
    });

    it('calls validatePass and then onRemove when password is submitted', async () => {
        // Initial state: password required
        const wrapper = mount(RemoveVerification, {
            props: {
                showModal: true,
                onClose: jest.fn()
            },
            global: globalConfig,
        });
        await wrapper.setData({ showPasswordRequiredFrom: true, password: 'password123' });

        axios.post.mockResolvedValueOnce({ data: { message: 'Password Verified' } }); // for verify/password
        axios.post.mockResolvedValueOnce({ data: { message: 'Disabled' } }); // for 2fa/disable

        await wrapper.find('button').trigger('click');

        expect(axios.post).toHaveBeenCalledWith('/api/admin/verify/password', { password: 'password123' });
        
        // Need to wait for the next tick because validatePass calls onRemove
        await new Promise(resolve => setTimeout(resolve, 0));
        
        expect(axios.post).toHaveBeenCalledWith('/api/admin/2fa/disable');
    });

    it('handles incorrect password during validation', async () => {
        const wrapper = mount(RemoveVerification, {
            props: {
                showModal: true,
                onClose: jest.fn()
            },
            global: globalConfig,
        });
        await wrapper.setData({ showPasswordRequiredFrom: true, password: 'wrong' });

        axios.post.mockRejectedValue({ response: { data: { message: 'Error' } } });

        await wrapper.find('button').trigger('click');

        expect(mockStore.dispatch).toHaveBeenCalledWith('setValidationError', { password: 'Incorrect password.' });
    });
});
