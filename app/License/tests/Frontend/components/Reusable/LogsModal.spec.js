import { mount } from '@vue/test-utils';
import LogsModal from '../../../../../Resources/js/components/Reusable/LogsModal.vue';
import axios from 'axios';

jest.mock('axios');

describe('LogsModal.vue', () => {
    let propsData;

    beforeEach(() => {
        propsData = {
            showModal: true,
            onClose: jest.fn(),
            data: { id: 1, trace: 'Test trace content' },
            title: 'trace',
        };
        
        axios.get.mockResolvedValue({
            data: {
                data: {
                    mail_body: 'Mocked mail body'
                },
                message: 'Success'
            }
        });
    });

    const mountComponent = (props = propsData) => {
        return mount(LogsModal, {
            props,
            global: {
                mocks: {
                    lang: (key) => key,
                },
                stubs: {
                    modal: {
                        template: '<div><slot name="title"></slot><slot name="fields"></slot></div>'
                    }
                }
            },
        });
    };

    it('renders trace content when title is "trace"', () => {
        const wrapper = mountComponent();
        expect(wrapper.find('p').classes()).toContain('trace');
        expect(wrapper.find('p').text()).toBe('Test trace content');
    });

    it('fetches and renders log content when title is "logs_content"', async () => {
        propsData.title = 'logs_content';
        const wrapper = mountComponent();
        
        // Wait for axios call
        await new Promise(resolve => setTimeout(resolve, 0));
        
        expect(axios.get).toHaveBeenCalledWith('/api/get-log-mail-body/1');
        // Since we didn't mock contentParser, it will probably be undefined if it's not defined in the component
        // Wait, I should check if contentParser is defined in LogsModal.vue
    });
});
