import { mount } from '@vue/test-utils';
import ResponseModal from '../../../../../Resources/js/components/Reusable/ResponseModal.vue';

describe('ResponseModal.vue', () => {
    let globalConfig;

    beforeEach(() => {
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
            }
        };
    });

    it('renders responseData correctly', () => {
        const responseData = { status: 'success', data: [1, 2, 3] };
        const wrapper = mount(ResponseModal, {
            props: {
                showModal: true,
                onClose: jest.fn(),
                responseData: responseData
            },
            global: globalConfig,
        });

        expect(JSON.parse(wrapper.find('pre').text())).toEqual(responseData);
    });

    it('copies data to clipboard when copyMethod is called', async () => {
        const responseData = "test-response-data";
        const wrapper = mount(ResponseModal, {
            props: {
                showModal: true,
                onClose: jest.fn(),
                responseData: responseData
            },
            global: globalConfig,
        });

        // Mock document methods
        document.execCommand = jest.fn();
        const appendChildSpy = jest.spyOn(document.body, 'appendChild');
        const removeChildSpy = jest.spyOn(document.body, 'removeChild');

        await wrapper.find('button').trigger('click');

        expect(document.execCommand).toHaveBeenCalledWith('Copy');
        expect(appendChildSpy).toHaveBeenCalled();
        expect(removeChildSpy).toHaveBeenCalled();
        expect(wrapper.props().onClose).toHaveBeenCalled();
    });

    it('stringify handles different data types', () => {
        const wrapper = mount(ResponseModal, {
            props: {
                showModal: true,
                onClose: jest.fn(),
                responseData: 'test'
            },
            global: globalConfig,
        });

        expect(wrapper.vm.stringify('test')).toBe('"test"');
        expect(wrapper.vm.stringify(123)).toBe('123');
        expect(wrapper.vm.stringify({ key: 'val' })).toBe('{"key":"val"}');
    });
});
