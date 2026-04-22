import { mount } from '@vue/test-utils';
import Profile from '../../../../../Resources/js/Pages/UserProfile/Profile.vue';
import axios from 'axios';
import { createStore } from 'vuex';

jest.mock('axios');

describe('Profile.vue', () => {
    let globalConfig;
    let store;

    beforeEach(() => {
        axios.get.mockResolvedValue({
            data: {
                data: {
                    client_fname: 'John',
                    client_lname: 'Doe',
                    client_username: 'johndoe',
                    client_email: 'john@example.com',
                    is_2fa_enabled: false,
                    timezone: { timezone_name: 'UTC' }
                }
            }
        });

        store = createStore({
            getters: {
                getUserData: () => ({
                    client_mobile_code: '91',
                    client_iso2: 'IN'
                })
            },
            actions: {
                setUserData: jest.fn(),
                setValidationError: jest.fn(),
                unsetValidationError: jest.fn()
            }
        });

        globalConfig = {
            stubs: {
                'alert': true,
                'custom-loader': true,
                'image-upload': true,
                'text-field': true,
                'dynamic-select': true,
                'phoneWithCountryCode': true,
                'barcode-modal': true,
                'remove-modal': true,
            },
            mocks: {
                lang: (msg) => msg,
                basePath: () => '/admin',
            },
            plugins: [store]
        };
    });

    it('renders the component and fetches profile info', async () => {
        const wrapper = mount(Profile, {
            global: globalConfig,
        });

        expect(axios.get).toHaveBeenCalledWith('/api/admin/profile/info');
        
        await wrapper.vm.$nextTick();
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.client_fname).toBe('John');
        expect(wrapper.find('.card-title').text()).toBe('profile');
    });

    it('shows barcode modal when turn on 2fa is clicked', async () => {
        const wrapper = mount(Profile, {
            global: globalConfig,
        });
        
        await wrapper.setData({ two_factor: false, hasDataPopulated: true });
        
        const turnOnBtn = wrapper.find('button.btn-primary.float-end');
        expect(turnOnBtn.text()).toContain('turn_on');
        
        await turnOnBtn.trigger('click');
        expect(wrapper.vm.showModal).toBe(true);
    });

    it('submits profile updates', async () => {
        axios.post.mockResolvedValue({ data: { message: 'Success' } });
        const wrapper = mount(Profile, {
            global: globalConfig,
        });
        
        await wrapper.setData({ 
            hasDataPopulated: true,
            client_fname: 'Jane',
            client_lname: 'Doe',
            client_username: 'janedoe'
        });

        // Mock isValid
        wrapper.vm.isValid = jest.fn().mockReturnValue(true);
        // We don't mock isPhoneValid directly since it's a computed property;
        // Instead, the setup should be such that isPhoneValid evaluates to true or we mock its dependencies.
        // It relies on $refs.phoneWithCountryCode.isValid, let's mock it if it's accessed, 
        // but often the component uses it only if it's there. 
        // Let's stub the method that accesses it if needed, or we just let it be.
        
        await wrapper.find('.card-footer button.btn-primary').trigger('click');
        expect(axios.post).toHaveBeenCalled();
    });
});
