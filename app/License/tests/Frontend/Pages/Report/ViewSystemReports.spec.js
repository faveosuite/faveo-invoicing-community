import { mount } from '@vue/test-utils';
import ViewSystemReports from '../../../../../Resources/js/Pages/Report/ViewSystemReports.vue';

describe('ViewSystemReports.vue', () => {
    let globalConfig;

    beforeEach(() => {
        globalConfig = {
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
        const wrapper = mount(ViewSystemReports, {
            global: globalConfig,
        });

        expect(wrapper.find('.card-title').text()).toBe('view_system_reports');
        expect(wrapper.find('data-table-stub').exists()).toBe(true);
    });

    it('shows loader when loading is true', async () => {
        const wrapper = mount(ViewSystemReports, {
            global: globalConfig,
        });
        
        await wrapper.setData({ loading: true });
        expect(wrapper.find('custom-loader-stub').exists()).toBe(true);
    });

    it('initializes data table options correctly', () => {
        const wrapper = mount(ViewSystemReports, {
            global: globalConfig,
        });

        expect(wrapper.vm.options.sortable).toEqual(['report_text', 'user_formatted', 'report_date_time', 'report_status']);
        expect(wrapper.vm.columns).toEqual(['report_text' ,'user_formatted','report_date_time','report_status']);
    });
});
