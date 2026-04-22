import { mount } from '@vue/test-utils';
import CallbacksIndex from '../../../../../Resources/js/Pages/Callbacks/CallbacksIndex.vue';

describe('CallbacksIndex.vue', () => {
    let globalConfig;

    beforeEach(() => {
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
    });

    it('renders the component correctly', () => {
        const wrapper = mount(CallbacksIndex, {
            global: globalConfig,
        });

        expect(wrapper.find('.container-fluid').exists()).toBe(true);
        expect(wrapper.find('.alert-info').text()).toContain('callbacks_description');
    });

    it('changes tabs and updates data', async () => {
        const wrapper = mount(CallbacksIndex, {
            global: globalConfig,
        });

        // Default tab is 'license'
        expect(wrapper.vm.activeTab).toBe('license');
        expect(wrapper.vm.endPoint).toBe('/api/admin/showLicenseCallbacks');

        // Click on 'update' tab
        const updateTab = wrapper.findAll('.nav-item')[1];
        await updateTab.trigger('click');

        expect(wrapper.vm.activeTab).toBe('update');
        expect(wrapper.vm.endPoint).toBe('/api/admin/showUpdateCallbacks');
    });

    it('shows loader when loading is true', async () => {
        const wrapper = mount(CallbacksIndex, {
            global: globalConfig,
        });
        
        await wrapper.setData({ loading: true });
        expect(wrapper.find('custom-loader-stub').exists()).toBe(true);
    });
});
