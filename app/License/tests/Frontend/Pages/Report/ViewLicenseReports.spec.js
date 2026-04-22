import { mount } from '@vue/test-utils';
import ViewLicenseReports from '../../../../../Resources/js/Pages/Report/ViewLicenseReports.vue';

describe('ViewLicenseReports.vue', () => {
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
                emitter: {
                    on: jest.fn(),
                    off: jest.fn(),
                    emit: jest.fn(),
                }
            }
        };
    });

    it('renders the component correctly', () => {
        const wrapper = mount(ViewLicenseReports, {
            global: globalConfig,
        });

        expect(wrapper.find('.card-title').text()).toBe('view_license_reports');
        expect(wrapper.find('data-table-stub').exists()).toBe(true);
    });

    it('shows loader when loading is true', async () => {
        const wrapper = mount(ViewLicenseReports, {
            global: globalConfig,
        });
        
        await wrapper.setData({ loading: true });
        expect(wrapper.find('custom-loader-stub').exists()).toBe(true);
    });

    it('initializes data table options correctly', () => {
        const wrapper = mount(ViewLicenseReports, {
            global: globalConfig,
        });

        expect(wrapper.vm.options.sortable).toEqual(['product_title', 'report_text', 'report_date_time', 'report_status']);
        expect(wrapper.vm.columns).toEqual(['report_text', 'user' ,'license','report_date_time', 'report_status']);
    });
});
