import { mount } from '@vue/test-utils';
import LicensesView from '../../../../../Resources/js/Pages/License/LicensesView.vue';
import { createStore } from 'vuex';
import { RouterLinkStub } from '@vue/test-utils';
import axios from 'axios';
import copy from 'clipboard-copy';

jest.mock('axios');
jest.mock('clipboard-copy');

describe('LicensesView.vue', () => {
    let store;

    beforeEach(() => {
        store = createStore({
            actions: {
                unsetValidationError: jest.fn(),
            }
        });

        axios.get.mockResolvedValue({
            data: {
                data: {
                    id: 1,
                    product_title: 'Test Product',
                    client_email: 'test@example.com',
                    license_code: 'ABCD-1234',
                    license_status: 1
                }
            }
        });
    });

    it('renders the product title correctly', async () => {
        const wrapper = mount(LicensesView, {
            global: {
                plugins: [store],
                stubs: {
                    'router-link': RouterLinkStub,
                    'data-table': true,
                    'custom-loader': true,
                    'alert': true,
                    'delete-modal': true,
                },
                mocks: {
                    lang: (msg) => msg,
                    basePath: () => '',
                },
                directives: {
                    tooltip: () => {}
                }
            },
        });

        await wrapper.vm.$nextTick(); // wait for axios and data update
        expect(wrapper.find('.card-title').text()).toBe('Test Product');
    });

    it('displays "Active" status for license_status 1', async () => {
        const wrapper = mount(LicensesView, {
            global: {
                plugins: [store],
                stubs: {
                    'router-link': RouterLinkStub,
                    'data-table': true,
                    'custom-loader': true,
                    'alert': true,
                    'delete-modal': true,
                },
                mocks: {
                    lang: (msg) => msg,
                    basePath: () => '',
                },
                directives: {
                    tooltip: () => {}
                }
            },
        });

        await wrapper.setData({ license_status: 1 });
        expect(wrapper.text()).toContain('Active');
    });

    it('calls copy function when copy button is clicked', async () => {
        const wrapper = mount(LicensesView, {
            global: {
                plugins: [store],
                stubs: {
                    'router-link': RouterLinkStub,
                    'data-table': true,
                    'custom-loader': true,
                    'alert': true,
                    'delete-modal': true,
                },
                mocks: {
                    lang: (msg) => msg,
                    basePath: () => '',
                },
                directives: {
                    tooltip: () => {}
                }
            },
        });

        // Wait for axios to complete so it doesn't overwrite our data
        await new Promise(resolve => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();
        await wrapper.setData({ license_code: 'ABCD-EFGH-IJKL-MNOP' });
        wrapper.vm.copyCommand();

        expect(copy).toHaveBeenCalledWith('ABCD-EFGH-IJKL-MNOP');
    });

    it('switches data between installations, callbacks and logs tabs', async () => {
        const wrapper = mount(LicensesView, {
            global: {
                plugins: [store],
                stubs: {
                    'router-link': RouterLinkStub,
                    'data-table': true,
                    'custom-loader': true,
                    'alert': true,
                    'delete-modal': true,
                },
                mocks: {
                    lang: (msg) => msg,
                    basePath: () => '',
                },
                directives: {
                    tooltip: () => {}
                }
            },
        });

        const tabs = wrapper.findAll('.nav-link');
        
        // Click Callbacks tab
        await tabs[1].trigger('click');
        expect(wrapper.vm.endPoint).toContain('licenseCallbacks');

        // Click Logs tab
        await tabs[2].trigger('click');
        expect(wrapper.vm.endPoint).toContain('installationLogs');
    });

    it('shows delete modal when delete button is clicked', async () => {
        const wrapper = mount(LicensesView, {
            global: {
                plugins: [store],
                stubs: {
                    'router-link': RouterLinkStub,
                    'data-table': true,
                    'custom-loader': true,
                    'alert': true,
                    'delete-modal': {
                        template: '<div class="delete-modal-mock"></div>'
                    },
                },
                mocks: {
                    lang: (msg) => msg,
                    basePath: () => '',
                },
                directives: {
                    tooltip: () => {}
                }
            },
        });

        await wrapper.find('.fa-trash').element.parentElement.click();
        expect(wrapper.find('.delete-modal-mock').exists()).toBe(true);
    });
});
