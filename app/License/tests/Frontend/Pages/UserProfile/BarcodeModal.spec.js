import { mount } from '@vue/test-utils';
import BarcodeModal from '../../../../../Resources/js/Pages/UserProfile/BarcodeModal.vue';
import axios from 'axios';
import copy from 'clipboard-copy';

jest.mock('axios');
jest.mock('clipboard-copy');

describe('BarcodeModal.vue', () => {
    let globalConfig;
    let mockStore;

    beforeEach(() => {
        axios.get.mockResolvedValue({ data: {} });
        axios.post.mockResolvedValue({ data: { data: { image: '<svg></svg>', secret: 'ABC', code: ['123', '456'] } } });

        mockStore = {
            dispatch: jest.fn()
        };

        globalConfig = {
            stubs: {
                'modal': {
                    template: '<div><slot name="title"></slot><slot name="fields"></slot><slot name="controls"></slot></div>'
                },
                'custom-loader': true,
                'text-field': true,
            },
            mocks: {
                lang: (msg) => msg,
                trans: (msg) => msg,
                $store: mockStore,
            },
            directives: {
                tooltip: () => {}
            }
        };
    });

    it('renders the component when showModal is true', async () => {
        const wrapper = mount(BarcodeModal, {
            props: {
                showModal: true,
                onClose: jest.fn()
            },
            global: globalConfig,
        });

        expect(wrapper.find('.modal-title').text()).toBe('setup_authenticator');
    });

    it('fetches required password status on mount', () => {
        mount(BarcodeModal, {
            props: { showModal: true, onClose: jest.fn() },
            global: globalConfig,
        });
        expect(axios.get).toHaveBeenCalledWith('/api/admin/show/verify-password');
    });

    it('copies recovery code to clipboard', async () => {
        const wrapper = mount(BarcodeModal, {
            props: { showModal: true, onClose: jest.fn() },
            global: globalConfig,
        });

        // Flush all pending axios promises so they don't overwrite our test data
        await new Promise(resolve => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();

        await wrapper.setData({
            passwordVerified: true,
            recoveryCode: 'TEST-CODE',
            recoveryCopied: false
        });

        // Call the copy method directly since v-tooltip is a directive not an HTML attribute
        wrapper.vm.copyToClipboard();
        expect(copy).toHaveBeenCalledWith('TEST-CODE');
        expect(wrapper.vm.copied).toBe(true);
    });

    it('transitions to barcode scan after recovery code is handled', async () => {
        const wrapper = mount(BarcodeModal, {
            props: { showModal: true, onClose: jest.fn() },
            global: globalConfig,
        });

        await wrapper.setData({ 
            passwordVerified: true, 
            recoveryCopied: false,
            copied: true 
        });

        const nextBtn = wrapper.findAll('button').find(b => b.text().includes('next'));
        await nextBtn.trigger('click');

        expect(wrapper.vm.recoveryCopied).toBe(true);
        expect(axios.post).toHaveBeenCalledWith('/api/admin/2fa/enable');
    });
});
