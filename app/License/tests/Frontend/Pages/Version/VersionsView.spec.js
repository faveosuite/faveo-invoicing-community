import { mount } from '@vue/test-utils';
import VersionsView from '../../../../../Resources/js/Pages/Version/VersionsView.vue';
import axios from 'axios';

jest.mock('axios');

describe('VersionsView.vue', () => {
    let globalConfig;

    beforeEach(() => {
        axios.get.mockResolvedValue({
            data: {
                data: {
                    id: 1,
                    version_number: '1.0.0',
                    product_title: 'Test Product',
                    product_id: 1,
                    version_date: '2023-01-01',
                    version_install_count: 10,
                    version_status: 1
                }
            }
        });

        globalConfig = {
            stubs: {
                'custom-loader': true,
                'alert': true,
                'data-table': true,
            },
            mocks: {
                lang: (msg) => msg,
                basePath: () => '/admin',
            }
        };

        // Mock window.location
        delete window.location;
        window.location = { pathname: '/admin/versions/1/view' };
    });

    it('renders and fetches version details', async () => {
        const wrapper = mount(VersionsView, {
            global: globalConfig,
        });

        await new Promise(resolve => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();

        expect(wrapper.find('.card-title').text()).toBe('Test Product - 1.0.0');
        expect(wrapper.find('a').text()).toBe('Test Product');
        expect(wrapper.find('.text-success').text()).toBe('active');
        expect(wrapper.vm.endPoint).toBe('/api/admin/versionCallbacks/1');
    });

    it('shows loader when loading is true', async () => {
        axios.get.mockImplementation(() => new Promise(() => {})); // never resolves, keeps loading = true
        const wrapper = mount(VersionsView, {
            global: globalConfig,
        });
        await wrapper.vm.$nextTick();
        expect(wrapper.find('custom-loader-stub').exists()).toBe(true);
    });
});
