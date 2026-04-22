import { mount } from '@vue/test-utils';
import DeleteModal from '../../../../../Resources/js/components/Reusable/DeleteModal.vue';
import axios from 'axios';

jest.mock('axios');

describe('DeleteModal.vue', () => {
    let globalConfig;

    beforeEach(() => {
        window.emitter = {
            emit: jest.fn()
        };

        globalConfig = {
            stubs: {
                'modal': {
                    template: '<div><slot name="title"></slot><slot name="fields"></slot><slot name="alert"></slot><slot name="controls"></slot></div>'
                },
                'loader': true,
                'alert': true,
            },
            mocks: {
                trans: (msg) => msg,
                getApiKey: 'test-api-key',
                $router: { push: jest.fn() }
            }
        };
    });

    it('renders with default props', () => {
        const wrapper = mount(DeleteModal, {
            props: {
                showModal: true,
                onClose: jest.fn()
            },
            global: globalConfig,
        });

        expect(wrapper.text()).toContain('are_you_sure');
        expect(wrapper.find('button').text()).toContain('delte');
    });

    it('renders with custom titles and messages', () => {
        const wrapper = mount(DeleteModal, {
            props: {
                showModal: true,
                onClose: jest.fn(),
                modalTitle: 'custom_title',
                modalMessage: 'custom_message',
                btnTitle: 'custom_btn'
            },
            global: globalConfig,
        });

        expect(wrapper.text()).toContain('custom_title');
        expect(wrapper.text()).toContain('custom_message');
        expect(wrapper.find('button').text()).toContain('custom_btn');
    });

    it('calls onSubmit and emits refreshData on success if no redirectUrl', async () => {
        const wrapper = mount(DeleteModal, {
            props: {
                showModal: true,
                onClose: jest.fn(),
                deleteUrl: '/api/delete',
                keyVal: 'id',
                idVal: 123
            },
            global: globalConfig,
        });

        axios.post.mockResolvedValue({
            data: { message: 'Deleted' }
        });

        await wrapper.find('button').trigger('click');
        
        expect(axios.post).toHaveBeenCalledWith('/api/delete', expect.objectContaining({
            id: 123
        }));
        
        await new Promise(resolve => setTimeout(resolve, 0));
        expect(window.emitter.emit).toHaveBeenCalledWith('refreshData');
        expect(wrapper.props().onClose).toHaveBeenCalled();
    });

    it('redirects after deletion if redirectUrl is provided', async () => {
        jest.useFakeTimers();
        const pushMock = jest.fn();
        const wrapper = mount(DeleteModal, {
            props: {
                showModal: true,
                onClose: jest.fn(),
                deleteUrl: '/api/delete',
                redirectUrl: '/list'
            },
            global: {
                ...globalConfig,
                mocks: {
                    ...globalConfig.mocks,
                    $router: { push: pushMock }
                }
            },
        });

        axios.post.mockResolvedValue({
            data: { message: 'Deleted' }
        });

        await wrapper.find('button').trigger('click');
        
        jest.advanceTimersByTime(3000);
        expect(pushMock).toHaveBeenCalledWith({ path: '/list' });
        jest.useRealTimers();
    });
});
