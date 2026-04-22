import { mount } from '@vue/test-utils';
import LicensesIndex from '../../../../../Resources/js/Pages/License/LicensesIndex.vue';
import { createStore } from 'vuex';
import { RouterLinkStub } from '@vue/test-utils';

describe('LicensesIndex.vue', () => {
    let store;
    let getters;

    beforeEach(() => {
        getters = {
            formattedTime: () => '2023-01-01 00:00:00',
        };
        store = createStore({
            getters,
        });
    });

    it('renders the card title "All Licenses"', () => {
        const wrapper = mount(LicensesIndex, {
            global: {
                plugins: [store],
                stubs: {
                    'router-link': RouterLinkStub,
                    'data-table': true,
                    'custom-loader': true,
                    'alert': true,
                },
                mocks: {
                    lang: (msg) => msg === 'all_licenses' ? 'All Licenses' : msg,
                },
                directives: {
                    tooltip: () => {}
                }
            },
        });

        expect(wrapper.find('.card-title').text()).toBe('all_licenses');
    });

    it('contains a router-link to create license page', () => {
        const wrapper = mount(LicensesIndex, {
            global: {
                plugins: [store],
                stubs: {
                    'router-link': RouterLinkStub,
                    'data-table': true,
                    'custom-loader': true,
                    'alert': true,
                },
                mocks: {
                    lang: (msg) => msg,
                },
                directives: {
                    tooltip: () => {}
                }
            },
        });

        const link = wrapper.findComponent(RouterLinkStub);
        expect(link.props().to).toBe('/licenses/create');
    });

    it('shows loader when loading state is true', async () => {
        const wrapper = mount(LicensesIndex, {
            global: {
                plugins: [store],
                stubs: {
                    'router-link': RouterLinkStub,
                    'data-table': true,
                    'custom-loader': {
                        template: '<div class="loader"></div>'
                    },
                    'alert': true,
                },
                mocks: {
                    lang: (msg) => msg,
                },
                directives: {
                    tooltip: () => {}
                }
            },
        });

        await wrapper.setData({ loading: true });
        expect(wrapper.find('.loader').exists()).toBe(true);
    });

    it('renders data-table when loading is false', async () => {
        const wrapper = mount(LicensesIndex, {
            global: {
                plugins: [store],
                stubs: {
                    'router-link': RouterLinkStub,
                    'data-table': {
                        template: '<div class="data-table"></div>'
                    },
                    'custom-loader': true,
                    'alert': true,
                },
                mocks: {
                    lang: (msg) => msg,
                },
                directives: {
                    tooltip: () => {}
                }
            },
        });

        await wrapper.setData({ loading: false });
        expect(wrapper.find('.data-table').exists()).toBe(true);
    });

    it('defines dataColumns correctly', () => {
        const wrapper = mount(LicensesIndex, {
            global: {
                plugins: [store],
                stubs: {
                    'router-link': RouterLinkStub,
                    'data-table': true,
                    'custom-loader': true,
                    'alert': true,
                },
                mocks: {
                    lang: (msg) => msg,
                },
                directives: {
                    tooltip: () => {}
                }
            },
        });

        expect(wrapper.vm.dataColumns).toContain('license_code');
        expect(wrapper.vm.dataColumns).toContain('actions');
    });
});
