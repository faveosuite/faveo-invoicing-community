import { mount } from '@vue/test-utils';
import ViewUpdateReports from '../../../../../Resources/js/Pages/Report/ViewUpdateReports.vue';

describe('ViewUpdateReports.vue', () => {
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
                emitter: {
                    on: jest.fn(),
                    off: jest.fn(),
                    emit: jest.fn(),
                }
            }
        };
    });

    it('renders the component correctly', () => {
        const wrapper = mount(ViewUpdateReports, {
            global: globalConfig,
        });

        expect(wrapper.find('.card-title').text()).toBe('view_update_reports');
        expect(wrapper.find('data-table-stub').exists()).toBe(true);
    });

    it('shows loader when loading is true', async () => {
        const wrapper = mount(ViewUpdateReports, {
            global: globalConfig,
        });
        
        await wrapper.setData({ loading: true });
        expect(wrapper.find('custom-loader-stub').exists()).toBe(true);
    });

    it('initializes data table options correctly', () => {
        const wrapper = mount(ViewUpdateReports, {
            global: globalConfig,
        });

        expect(wrapper.vm.options.sortable).toEqual(['report_text', 'report_date_time', 'report_status']);
        expect(wrapper.vm.columns).toEqual(['report_text', 'product', 'report_date_time', 'report_status']);
    });
});
