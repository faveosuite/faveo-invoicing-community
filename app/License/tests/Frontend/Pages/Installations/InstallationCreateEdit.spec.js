import { mount } from '@vue/test-utils';
import InstallationCreateEdit from '../../../../../Resources/js/Pages/Installations/InstallationCreateEdit.vue';
import axios from 'axios';
import { createStore } from 'vuex';
import { validateInstallationSettings } from "../../../../../Resources/js/helpers/validator/installationValidation";

jest.mock('axios');
jest.mock('../../../../../Resources/js/helpers/validator/installationValidation');

describe('InstallationCreateEdit.vue', () => {
    let globalConfig;
    let store;

    beforeEach(() => {
        jest.clearAllMocks();
        delete window.location;
        window.location = { pathname: '/installations/1/edit' };

        axios.get.mockResolvedValue({
            data: {
                data: {
                    installation: {
                        id: 1,
                        installation_domain: 'example.com',
                        installation_ip: '127.0.0.1',
                        installation_status: 1,
                        installation_disable_ip_verification: 0
                    }
                }
            }
        });

        validateInstallationSettings.mockReturnValue({ errors: {}, isValid: true });

        store = createStore({
            getters: {
                getApiKey: () => 'test-api-key',
            },
            actions: {
                setAlert: jest.fn(),
            }
        });

        globalConfig = {
            plugins: [store],
            stubs: {
                'custom-loader': true,
                'alert': true,
                'text-field': true,
                'radio-button': true,
            },
            mocks: {
                trans: (msg) => msg,
                $router: { push: jest.fn() }
            }
        };
    });

    it('fetches and populates data on mount', async () => {
        const wrapper = mount(InstallationCreateEdit, {
            global: globalConfig,
        });

        await new Promise(resolve => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.installation_domain).toBe('example.com');
        expect(wrapper.vm.hasDataPopulated).toBe(true);
    });

    it('updates state when onChange is called', async () => {
        const wrapper = mount(InstallationCreateEdit, {
            global: globalConfig,
        });

        wrapper.vm.onChange('192.168.1.1', 'installation_ip');
        expect(wrapper.vm.installation_ip).toBe('192.168.1.1');
    });

    it('calls onSubmit and handles success', async () => {
        const wrapper = mount(InstallationCreateEdit, {
            global: globalConfig,
        });

        await new Promise(resolve => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();

        axios.post.mockResolvedValue({
            data: {
                api_action_success: true,
                action_success: true,
                page_message: 'Updated successfully'
            }
        });

        await wrapper.vm.onSubmit();
        expect(axios.post).toHaveBeenCalled();
    });

    it('prevents submission if invalid', async () => {
        validateInstallationSettings.mockReturnValue({ errors: { some: 'error' }, isValid: false });
        const wrapper = mount(InstallationCreateEdit, {
            global: globalConfig,
        });

        await wrapper.vm.onSubmit();
        expect(axios.post).not.toHaveBeenCalled();
    });
});
