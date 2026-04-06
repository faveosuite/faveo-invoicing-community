import { mount } from '@vue/test-utils';
import SimplePagination from '../../../../../Resources/js/components/Reusable/SimplePagination.vue';

describe('SimplePagination.vue', () => {
    it('calls onPagination with "previous" when previous button is clicked', async () => {
        const onPagination = jest.fn();
        const wrapper = mount(SimplePagination, {
            props: {
                prev_page: 'url1',
                next_page: 'url2',
                onPagination
            },
            global: {
                mocks: {
                    trans: (key) => key
                }
            }
        });

        await wrapper.find('button.btn.btn-primary:first-child').trigger('click');
        expect(onPagination).toHaveBeenCalledWith('previous');
    });

    it('calls onPagination with "next" when next button is clicked', async () => {
        const onPagination = jest.fn();
        const wrapper = mount(SimplePagination, {
            props: {
                prev_page: 'url1',
                next_page: 'url2',
                onPagination
            },
            global: {
                mocks: {
                    trans: (key) => key
                }
            }
        });

        await wrapper.findAll('button.btn.btn-primary')[1].trigger('click');
        expect(onPagination).toHaveBeenCalledWith('next');
    });

    it('disables previous button when prev_page is empty', () => {
        const wrapper = mount(SimplePagination, {
            props: {
                prev_page: '',
                next_page: 'url2',
                onPagination: jest.fn()
            },
            global: {
                mocks: {
                    trans: (key) => key
                }
            }
        });

        const prevBtn = wrapper.find('button.btn.btn-primary:first-child');
        expect(prevBtn.element.disabled).toBe(true);
    });

    it('disables next button when next_page is empty', () => {
        const wrapper = mount(SimplePagination, {
            props: {
                prev_page: 'url1',
                next_page: '',
                onPagination: jest.fn()
            },
            global: {
                mocks: {
                    trans: (key) => key
                }
            }
        });

        const nextBtn = wrapper.findAll('button.btn.btn-primary')[1];
        expect(nextBtn.element.disabled).toBe(true);
    });
});
