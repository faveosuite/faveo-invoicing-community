import { mount } from '@vue/test-utils';
import InstallationsView from '../../../../../Resources/js/Pages/Installations/InstallationsView.vue';
import axios from 'axios';
import { createStore } from 'vuex';

jest.mock('axios');

describe('InstallationsView.vue', () => {
    let globalConfig;
    let store;

    beforeEach(() => {
        delete window.location;
        window.location = { pathname: '/installations/1/view' };

        axios.get.mockResolvedValue({
            data: {
                data: {
                    id: 1,
                    product_title: 'Test Product',
                    license_code: 'ABCD1234EFGH5678',
                    license_id: 10,
                    installation_date: '2023-01-01',
                    installation_domain: 'example.com',
                    installation_ip: '127.0.0.1',
                    installation_disable_ip_verification: 1,
                    installation_status: 1
                }
            }
        });

        store = createStore({
            actions: {
                unsetValidationError: jest.fn(),
            }
        });

        globalConfig = {
            plugins: [store],
            stubs: {
                'custom-loader': true,
                'alert': true,
                'data-table': true,
                'delete-modal': true,
                'router-link': true,
            },
            mocks: {
                lang: (msg) => msg,
                basePath: () => '/admin',
            },
            directives: {
                tooltip: () => {}
            }
        };
    });

    it('fetches and displays installation details', async () => {
        const wrapper = mount(InstallationsView, {
            global: globalConfig,
        });

        await new Promise(resolve => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.product_title).toBe('Test Product');
        expect(wrapper.find('.card-title').text()).toBe('Test Product');
        expect(wrapper.vm.license_code).toBe('ABCD1234EFGH5678');
    });

    it('shows and hides delete modal', async () => {
        const wrapper = mount(InstallationsView, {
            global: globalConfig,
        });

        expect(wrapper.vm.showModal).toBe(false);
        wrapper.vm.showDeleteModal();
        expect(wrapper.vm.showModal).toBe(true);

        wrapper.vm.onClose();
        expect(wrapper.vm.showModal).toBe(false);
    });

    it('renders correct status labels', async () => {
        const wrapper = mount(InstallationsView, {
            global: globalConfig,
        });

        await new Promise(resolve => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();

        const statusLabels = wrapper.findAll('.text-success');
        expect(statusLabels.length).toBeGreaterThan(0);
        expect(statusLabels.at(0).text()).toBe('enabled');
        expect(statusLabels.at(1).text()).toBe('active');
    });
});
