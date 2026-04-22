import { mount } from '@vue/test-utils';
import LicenseCreateEdit from '../../../../../Resources/js/Pages/License/LicenseCreateEdit.vue';
import { createStore } from 'vuex';
import axios from 'axios';
import { validateLicenseSettings } from "../../../../../Resources/js/helpers/validator/validateLicenseSettings.js";

jest.mock('axios');
jest.mock('../../../../../Resources/js/helpers/validator/validateLicenseSettings.js');

describe('LicenseCreateEdit.vue', () => {
    let store;

    beforeEach(() => {
        store = createStore({
            getters: {
                getUserData: () => ({ client_id: 1 }),
            }
        });

        axios.get.mockResolvedValue({
            data: {
                data: {
                    data: [],
                    license: {
                        id: 1,
                        license_code: 'ABCD',
                        license_status: 1,
                        license_ip: '',
                        license_domain: '',
                        license_comments: ''
                    },
                    product_name: [{ product_id: 1, product_title: 'Test Product' }]
                }
            }
        });

        validateLicenseSettings.mockReturnValue({ errors: {}, isValid: true });
        
        // Mock window.location
        delete window.location;
        window.location = { pathname: '/licenses/create' };
    });

    const getGlobalConfig = () => ({
        plugins: [store],
        stubs: {
            'text-field': true,
            'number-field': true,
            'static-select': true,
            'dynamic-select': true,
            'radio-button': true,
            'date-picker': true,
            'custom-loader': true,
            'alert': true,
        },
        mocks: {
            lang: (msg) => msg,
            trans: (msg) => msg,
        }
    });

    it('renders "Create New License" title when in create mode', async () => {
        const wrapper = mount(LicenseCreateEdit, {
            global: getGlobalConfig(),
        });

        await wrapper.setData({ hasDataPopulated: true, title: 'create_new_license', license_ip: '', license_domain: '', license_comments: '' });
        expect(wrapper.find('.card-title').text()).toBe('create_new_license');
    });

    it('renders "Edit License" title when in edit mode', async () => {
        window.location.pathname = '/licenses/1/edit';
        const wrapper = mount(LicenseCreateEdit, {
            global: getGlobalConfig(),
        });

        await wrapper.setData({ hasDataPopulated: true, title: 'edit_license' });
        expect(wrapper.find('.card-title').text()).toBe('edit_license');
    });

    it('calls onSubmit when save button is clicked', async () => {
        const wrapper = mount(LicenseCreateEdit, {
            global: getGlobalConfig(),
        });

        await wrapper.setData({ hasDataPopulated: true });
        axios.post.mockResolvedValue({ data: { message: 'Success' } });

        await wrapper.find('button.btn-primary').trigger('click');
        expect(axios.post).toHaveBeenCalled();
    });

    it('generates random code when generateCode is called', async () => {
        const wrapper = mount(LicenseCreateEdit, {
            global: getGlobalConfig(),
        });

        wrapper.vm.generateCode();
        expect(wrapper.vm.license_code).toHaveLength(16);
    });

    it('updates state when onChange is called', async () => {
        const wrapper = mount(LicenseCreateEdit, {
            global: getGlobalConfig(),
        });

        wrapper.vm.onChange('NEW-CODE', 'license_code');
        expect(wrapper.vm.license_code).toBe('NEW-CODE');
    });

    it('handles product change in onChange', async () => {
        const wrapper = mount(LicenseCreateEdit, {
            global: getGlobalConfig(),
        });

        wrapper.vm.onChange({ product_id: 5, product_title: 'New Product' }, 'product');
        expect(wrapper.vm.product_id).toBe(5);
        expect(wrapper.vm.product_title).toBe('New Product');
    });
});
