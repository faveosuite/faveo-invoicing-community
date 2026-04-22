import { mount } from '@vue/test-utils';
import InstallationsIndex from '../../../../../Resources/js/Pages/Installations/InstallationsIndex.vue';
import { createStore } from 'vuex';

describe('InstallationsIndex.vue', () => {
    let store;
    let globalConfig;

    beforeEach(() => {
        store = createStore({
            getters: {
                formattedTime: () => 'YYYY-MM-DD HH:mm:ss',
            }
        });

        globalConfig = {
            plugins: [store],
            stubs: {
                'custom-loader': true,
                'alert': true,
                'data-table': true,
                'router-link': true,
            },
            mocks: {
                lang: (msg) => msg,
                basePath: () => '/admin',
            }
        };
    });

    it('renders correctly', () => {
        const wrapper = mount(InstallationsIndex, {
            global: globalConfig,
        });

        expect(wrapper.find('.card-title').text()).toBe('all_installations');
        expect(wrapper.find('data-table-stub').exists()).toBe(true);
    });

    it('shows loader when loading is true', async () => {
        const wrapper = mount(InstallationsIndex, {
            global: globalConfig,
        });
        
        await wrapper.setData({ loading: true });
        expect(wrapper.find('custom-loader-stub').exists()).toBe(true);
    });

    it('initializes correct columns and options', () => {
        const wrapper = mount(InstallationsIndex, {
            global: globalConfig,
        });

        expect(wrapper.vm.columns).toContain('product_title');
        expect(wrapper.vm.columns).toContain('license');
        expect(wrapper.vm.options.headings.product_title).toBe('product');
    });
});
