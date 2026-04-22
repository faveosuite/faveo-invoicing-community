import { mount } from '@vue/test-utils';
import WhiteList from '../../../../../Resources/js/Pages/WhiteList/WhiteList.vue';

describe('WhiteList.vue', () => {
    let globalConfig;

    beforeEach(() => {
        globalConfig = {
            directives: {
                tooltip: {}
            },
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

    it('renders the component correctly', () => {
        const wrapper = mount(WhiteList, {
            global: globalConfig,
        });

        expect(wrapper.find('.card-title').text()).toBe('view_whitelist_ip');
        expect(wrapper.find('data-table-stub').exists()).toBe(true);
    });

    it('shows loader when loading is true', async () => {
        const wrapper = mount(WhiteList, {
            global: globalConfig,
        });
        
        await wrapper.setData({ loading: true });
        expect(wrapper.find('custom-loader-stub').exists()).toBe(true);
    });
});
