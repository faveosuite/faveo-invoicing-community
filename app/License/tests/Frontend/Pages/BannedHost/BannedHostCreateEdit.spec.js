import { mount } from '@vue/test-utils';
import BannedHostCreateEdit from '../../../../../../Resources/js/Pages/BannedHost/BannedHostCreateEdit.vue';
import axios from 'axios';
import { successHandler, errorHandler } from '../../../../../../Resources/js/helpers/responseHandler';
import { bannedHostValidation } from "../../../../../../Resources/js/helpers/validator/bannedHostValidation.js";
import { getIdFromUrl } from '../../../../../../Resources/js/helpers/extraLogics';

jest.mock('axios');
jest.mock('../../../../../../Resources/js/helpers/responseHandler');
jest.mock('../../../../../../Resources/js/helpers/validator/bannedHostValidation.js');
jest.mock('../../../../../../Resources/js/helpers/extraLogics');

describe('BannedHostCreateEdit.vue', () => {
    let wrapper;
    const mockRouter = {
        push: jest.fn()
    };

    beforeEach(() => {
        delete window.location;
        window.location = { pathname: '/banned-hosts/add' };
        getIdFromUrl.mockReturnValue(null);
        bannedHostValidation.mockReturnValue({ isValid: true, errors: {} });
        axios.get.mockResolvedValue({ data: { data: { banned_host_data: { banned_host_ip: '1.1.1.1', comments: 'test' } } } });
        axios.post.mockResolvedValue({ data: { message: 'Success' } });

        wrapper = mount(BannedHostCreateEdit, {
            global: {
                mocks: {
                    lang: (s) => s,
                    $router: mockRouter
                },
                stubs: {
                    'custom-loader': true,
                    'alert': true,
                    'text-field': true
                }
            }
        });
    });

    afterEach(() => {
        jest.clearAllMocks();
    });

    it('renders the create form correctly', () => {
        expect(wrapper.vm.title).toBe('add_new_banned_host');
        expect(wrapper.find('.card-title').text()).toBe('add_new_banned_host');
    });

    it('changes to edit mode when path contains edit', async () => {
        window.location.pathname = '/banned-hosts/edit/1';
        getIdFromUrl.mockReturnValue(1);
        
        // Re-mount to trigger beforeMount logic with new path
        wrapper = mount(BannedHostCreateEdit, {
            global: {
                mocks: {
                    lang: (s) => s,
                    $router: mockRouter
                },
                stubs: {
                    'custom-loader': true,
                    'alert': true,
                    'text-field': true
                }
            }
        });

        expect(wrapper.vm.title).toBe('edit_banned_host');
        expect(axios.get).toHaveBeenCalledWith('/api/admin/viewBannedHost/1');
    });

    it('calls onSubmit and handles success', async () => {
        await wrapper.vm.onSubmit();
        expect(axios.post).toHaveBeenCalled();
        expect(successHandler).toHaveBeenCalled();
    });

    it('handles validation failure in onSubmit', async () => {
        bannedHostValidation.mockReturnValue({ isValid: false, errors: { ip: 'Required' } });
        await wrapper.vm.onSubmit();
        expect(axios.post).not.toHaveBeenCalled();
    });

    it('updates data on onChange', () => {
        wrapper.vm.onChange('1.2.3.4', 'banned_host_ip');
        expect(wrapper.vm.banned_host_ip).toBe('1.2.3.4');
        
        wrapper.vm.onChange('new comment', 'banned_host_comments');
        expect(wrapper.vm.banned_host_comments).toBe('new comment');
    });
});
