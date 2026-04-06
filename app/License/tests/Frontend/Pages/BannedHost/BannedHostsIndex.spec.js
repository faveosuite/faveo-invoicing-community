import { mount } from '@vue/test-utils';
import BannedHostsIndex from '../../../../../../Resources/js/Pages/BannedHost/BannedHostsIndex.vue';

describe('BannedHostsIndex.vue', () => {
    it('renders correctly', () => {
        const wrapper = mount(BannedHostsIndex, {
            global: {
                directives: {
                    tooltip: {}
                },
                mocks: {
                    lang: (s) => s
                },
                stubs: {
                    'custom-loader': true,
                    'alert': true,
                    'router-link': true,
                    'data-table': {
                        template: '<div class="data-table-stub"></div>',
                        props: ['url', 'dataColumns', 'option']
                    }
                }
            }
        });

        expect(wrapper.text()).toContain('view_banned_hosts');
        expect(wrapper.find('.data-table-stub').exists()).toBe(true);
    });

    it('sets correct options for data-table', () => {
        const wrapper = mount(BannedHostsIndex, {
            global: {
                directives: {
                    tooltip: {}
                },
                mocks: {
                    lang: (s) => s
                },
                stubs: {
                    'custom-loader': true,
                    'alert': true,
                    'router-link': true,
                    'data-table': true
                }
            }
        });

        expect(wrapper.vm.options.headings.banned_host_ip).toBe('ip_address');
        expect(wrapper.vm.options.headings.comments).toBe('comments');
        expect(wrapper.vm.options.filterable).toContain('banned_host_ip');
    });

    it('responseAdapter formats data correctly', () => {
        const wrapper = mount(BannedHostsIndex, {
            global: {
                directives: {
                    tooltip: {}
                },
                mocks: {
                    lang: (s) => s
                },
                stubs: {
                    'custom-loader': true,
                    'alert': true,
                    'router-link': true,
                    'data-table': true
                }
            }
        });

        const mockResponse = {
            data: {
                data: {
                    data: [
                        { id: 1, banned_host_ip: '1.1.1.1' }
                    ],
                    total: 1
                }
            }
        };

        const result = wrapper.vm.options.responseAdapter(mockResponse);
        expect(result.count).toBe(1);
        expect(result.data[0].edit_url).toBe('/banned-hosts/1/edit');
        expect(result.data[0].delete_url).toBe('/api/admin/bannedHosts/delete');
    });
});
