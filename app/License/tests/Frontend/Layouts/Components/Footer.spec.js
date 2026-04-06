import { mount } from '@vue/test-utils';
import Footer from '../../../Resources/js/Layouts/Components/Footer.vue';

describe('Footer.vue', () => {
    const mountComponent = (props = { versioning: '1.2.3' }) => {
        return mount(Footer, {
            props,
            global: {
                mocks: {
                    trans: (key) => key
                }
            }
        });
    };

    it('renders the versioning prop', () => {
        const version = '4.5.6';
        const wrapper = mountComponent({ versioning: version });
        expect(wrapper.text()).toContain(version);
        expect(wrapper.text()).toContain('version');
    });

    it('renders copyright year', () => {
        const wrapper = mountComponent();
        const currentYear = new Date().getFullYear().toString();
        expect(wrapper.text()).toContain(currentYear);
    });

    it('renders trademark information', () => {
        const wrapper = mountComponent();
        expect(wrapper.text()).toContain('Ladybird Web Solution');
        expect(wrapper.text()).toContain('all_rights_reserved');
        expect(wrapper.text()).toContain('powered_by');
    });

    it('has correct links', () => {
        const wrapper = mountComponent();
        const links = wrapper.findAll('a');
        expect(links.length).toBe(2);
        expect(links[0].attributes('href')).toBe('https://www.faveohelpdesk.com');
        expect(links[1].attributes('href')).toBe('https://www.faveohelpdesk.com');
    });
});
