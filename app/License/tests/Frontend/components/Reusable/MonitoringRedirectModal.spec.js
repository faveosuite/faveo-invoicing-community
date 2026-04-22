import { mount } from '@vue/test-utils';
import MonitoringRedirectModal from '../../../../../Resources/js/components/Reusable/MonitoringRedirectModal.vue';

describe('MonitoringRedirectModal.vue', () => {
    it('renders the modal when showModal is true', () => {
        const wrapper = mount(MonitoringRedirectModal, {
            props: {
                showModal: true,
                onClose: jest.fn(),
                tool: 'Pulse'
            },
            global: {
                stubs: {
                    modal: {
                        template: '<div><slot name="title" /><slot name="fields" /></div>'
                    }
                }
            }
        });

        expect(wrapper.text()).toContain('Monitoring unavailable');
        expect(wrapper.text()).toContain('Pulse could not load');
        expect(wrapper.text()).toContain('Invalid installation path detected.');
    });

    it('uses Pulse as default tool name if not provided', () => {
        const wrapper = mount(MonitoringRedirectModal, {
            props: {
                showModal: true,
                onClose: jest.fn()
            },
            global: {
                stubs: {
                    modal: {
                        template: '<div><slot name="title" /><slot name="fields" /></div>'
                    }
                }
            }
        });

        expect(wrapper.text()).toContain('Pulse could not load');
    });

    it('uses provided tool name', () => {
        const wrapper = mount(MonitoringRedirectModal, {
            props: {
                showModal: true,
                onClose: jest.fn(),
                tool: 'Horizon'
            },
            global: {
                stubs: {
                    modal: {
                        template: '<div><slot name="title" /><slot name="fields" /></div>'
                    }
                }
            }
        });

        expect(wrapper.text()).toContain('Horizon could not load');
    });

    it('does not render the modal when showModal is false', () => {
        const wrapper = mount(MonitoringRedirectModal, {
            props: {
                showModal: false,
                onClose: jest.fn()
            },
            global: {
                stubs: {
                    modal: true
                }
            }
        });

        expect(wrapper.findComponent({ name: 'modal' }).exists()).toBe(false);
    });
});
