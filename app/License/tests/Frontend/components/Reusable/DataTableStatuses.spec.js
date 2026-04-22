import { mount } from '@vue/test-utils';
import DataTableStatuses from '../../../../../Resources/js/components/Reusable/DataTableStatuses.vue';

describe('DataTableStatuses.vue', () => {
    let propsData;

    beforeEach(() => {
        propsData = {
            data: {
                id: 1,
                is_2fa_enabled: 1,
            },
        };
    });

    const mountComponent = (props = propsData) => {
        return mount(DataTableStatuses, {
            props,
            global: {
                mocks: {
                    lang: (key) => key,
                },
                directives: {
                    tooltip: {},
                },
            },
        });
    };

    it('renders 2FA enabled status correctly', () => {
        const wrapper = mountComponent();
        const icon = wrapper.find('[id="2fa_status_user__1"]');
        expect(icon.exists()).toBe(true);
        expect(icon.classes()).toContain('success');
    });

    it('renders 2FA disabled status correctly', () => {
        propsData.data.is_2fa_enabled = 0;
        const wrapper = mountComponent();
        const icon = wrapper.find('[id="2fa_status_user__1"]');
        expect(icon.exists()).toBe(true);
        expect(icon.classes()).toContain('danger');
    });
});
