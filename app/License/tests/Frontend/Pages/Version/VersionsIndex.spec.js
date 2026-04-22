import { mount } from '@vue/test-utils';
import VersionsIndex from '../../../../../Resources/js/Pages/Version/VersionsIndex.vue';

describe('VersionsIndex.vue', () => {
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
        const wrapper = mount(VersionsIndex, {
            global: globalConfig,
        });

        expect(wrapper.find('.card-title').text()).toBe('all_versions');
        expect(wrapper.find('data-table-stub').exists()).toBe(true);
    });

    it('shows loader when loading is true', async () => {
        const wrapper = mount(VersionsIndex, {
            global: globalConfig,
        });
        
        await wrapper.setData({ loading: true });
        expect(wrapper.find('custom-loader-stub').exists()).toBe(true);
    });
});
