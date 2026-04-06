import { mount } from '@vue/test-utils';
import Dashboard from '../../../../../Resources/js/Pages/Dashboard.vue';
import axios from 'axios';

jest.mock('axios');

describe('Dashboard.vue', () => {
    let globalConfig;

    beforeEach(() => {
        axios.get.mockResolvedValue({
            data: {
                data: {
                    productsCount: 10,
                    licenseCount: 20,
                    versionsCount: 5,
                    callbacksCount: 50,
                    latestProducts: [],
                    latestVersions: [],
                    latestInstallations: [],
                    latestCallbacks: [],
                    latestReports: [],
                    expiredVersions: [],
                    latestClients: [],
                    latestLicenses: [],
                    expiringSupport: [],
                    expiringUpdates: []
                }
            }
        });

        globalConfig = {
            stubs: {
                'custom-loader': true,
                'latest-product': true,
                'latest-version': true,
                'latest-installations': true,
                'latest-callbacks': true,
                'latest-product-report': true,
                'expiring-version': true,
                'latest-clients': true,
                'latest-licenses': true,
                'expiring-support': true,
                'expiring-updates': true,
                'router-link': true,
            },
            mocks: {
                lang: (msg) => msg,
                basePath: () => '/admin',
            }
        };
    });

    it('renders the counts correctly after fetching data', async () => {
        const wrapper = mount(Dashboard, {
            global: globalConfig,
        });

        // Wait for axios call and component update
        await new Promise(resolve => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.products).toBe(10);
        expect(wrapper.vm.licenses).toBe(20);
        expect(wrapper.vm.versions).toBe(5);
        expect(wrapper.vm.callbacks).toBe(50);
        
        expect(wrapper.find('h3').text()).toBe('10');
    });

    it('shows loader when loading is true', async () => {
        const wrapper = mount(Dashboard, {
            global: globalConfig,
        });
        
        await wrapper.setData({ loading: true });
        expect(wrapper.find('custom-loader-stub').exists()).toBe(true);
    });

    it('updates data when updateProp is called', async () => {
        const wrapper = mount(Dashboard, {
            global: globalConfig,
        });

        const newData = { latestProducts: [{ id: 1, name: 'New Product' }] };
        wrapper.vm.updateProp('latestProducts', newData);
        
        expect(wrapper.vm.latest_products).toEqual(newData.latestProducts);
    });
});
