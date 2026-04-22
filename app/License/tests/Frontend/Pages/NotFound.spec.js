import { mount } from '@vue/test-utils';
import NotFound from '../../../../Resources/js/Pages/NotFound.vue';

describe('NotFound.vue', () => {
    it('renders 404 error page with correct message', () => {
        const wrapper = mount(NotFound, {
            global: {
                stubs: {
                    'router-link': true
                }
            }
        });

        expect(wrapper.find('.headline').text()).toBe('404');
        expect(wrapper.find('h3').text()).toContain('Oops! Page not found.');
        expect(wrapper.find('p').text()).toContain('We could not find the page you were looking for.');
    });

    it('contains a router-link to return to dashboard', () => {
        const wrapper = mount(NotFound, {
            global: {
                stubs: {
                    'router-link': {
                        template: '<a :href="to"><slot /></a>',
                        props: ['to']
                    }
                }
            }
        });

        const link = wrapper.find('a');
        expect(link.exists()).toBe(true);
        expect(link.attributes('href')).toBe('/');
        expect(link.text()).toBe('return to dashboard.');
    });
});
