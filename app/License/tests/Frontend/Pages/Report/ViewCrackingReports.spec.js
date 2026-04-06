import { mount } from '@vue/test-utils';
import ViewCrackingReports from '../../../../../Resources/js/Pages/Report/ViewCrackingReports.vue';

describe('ViewCrackingReports.vue', () => {
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
            }
        };
    });

    it('renders the component correctly', () => {
        const wrapper = mount(ViewCrackingReports, {
            global: globalConfig,
        });

        expect(wrapper.find('.card-title').text()).toBe('view_cracking_reports');
        expect(wrapper.find('data-table-stub').exists()).toBe(true);
    });

    it('shows loader when loading is true', async () => {
        const wrapper = mount(ViewCrackingReports, {
            global: globalConfig,
        });
        
        await wrapper.setData({ loading: true });
        expect(wrapper.find('custom-loader-stub').exists()).toBe(true);
    });

    it('initializes data table options correctly', () => {
        const wrapper = mount(ViewCrackingReports, {
            global: globalConfig,
        });

        expect(wrapper.vm.options.sortable).toEqual(['report_text', 'license_code', 'report_date_time', 'report_status']);
        expect(wrapper.vm.columns).toEqual(['report_text','license_code','report_date_time','report_status']);
    });
});
