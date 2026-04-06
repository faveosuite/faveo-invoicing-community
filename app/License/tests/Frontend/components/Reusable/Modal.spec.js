import { mount } from '@vue/test-utils';
import Modal from '../../../../../Resources/js/components/Reusable/Modal.vue';

describe('Modal.vue', () => {
    it('renders the modal when showModal is true', () => {
        const wrapper = mount(Modal, {
            props: {
                showModal: true,
            },
        });

        expect(wrapper.find('.modal-mask').exists()).toBe(true);
    });

    it('does not render the modal when showModal is false', () => {
        const wrapper = mount(Modal, {
            props: {
                showModal: false,
            },
        });

        expect(wrapper.find('.modal-mask').exists()).toBe(false);
    });

    it('renders title slot content', () => {
        const wrapper = mount(Modal, {
            props: {
                showModal: true,
            },
            slots: {
                title: '<h3>Test Title</h3>',
            },
        });

        expect(wrapper.find('h3').text()).toBe('Test Title');
    });

    it('renders fields slot content', () => {
        const wrapper = mount(Modal, {
            props: {
                showModal: true,
            },
            slots: {
                fields: '<div class="test-field">Field content</div>',
            },
        });

        expect(wrapper.find('.test-field').text()).toBe('Field content');
    });

    it('calls onClose when close button is clicked', async () => {
        const onClose = jest.fn();
        const wrapper = mount(Modal, {
            props: {
                showModal: true,
                onClose,
            },
        });

        await wrapper.find('button.btn-close').trigger('click');
        expect(onClose).toHaveBeenCalled();
    });

    it('does not render footer when showFooter is false', () => {
        const wrapper = mount(Modal, {
            props: {
                showModal: true,
                showFooter: false,
            },
        });

        expect(wrapper.find('.modal-footer').exists()).toBe(false);
    });

    it('applies custom classname to modal-wrapper', () => {
        const wrapper = mount(Modal, {
            props: {
                showModal: true,
                classname: 'modal-lg',
            },
        });

        expect(wrapper.find('.modal-wrapper').classes()).toContain('modal-lg');
    });
});
