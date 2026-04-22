import { mount } from '@vue/test-utils';
import Datatable from '../../../../../Resources/js/components/Reusable/Datatable.vue';

jest.mock('vue-tables-2', () => ({
    ServerTable: {},
    ClientTable: {},
    Event: {
        $on: jest.fn(),
        $off: jest.fn(),
        $emit: jest.fn(),
    }
}), { virtual: true });

// Mock vue-tables-2
const ServerTableMock = {
    template: '<div><slot name="id" :row="{ id: 1 }"></slot><slot name="afterTable"></slot></div>',
    methods: {
        setLimit: jest.fn(),
        refresh: jest.fn(),
    },
    data() {
        return {
            data: [{ id: 1 }, { id: 2 }]
        }
    }
};

describe('Datatable.vue', () => {
    let globalConfig;

    beforeEach(() => {
        // Mock global eventHub
        window.eventHub = {
            $on: jest.fn(),
            $off: jest.fn(),
            $emit: jest.fn(),
        };

        globalConfig = {
            stubs: {
                'v-server-table': ServerTableMock,
                'custom-loader': true,
                'loader': true,
            },
            mocks: {
                lang: (msg) => msg,
            }
        };
    });

    it('renders the datatable component', () => {
        const wrapper = mount(Datatable, {
            props: {
                dataColumns: ['id', 'name'],
                url: '/api/data'
            },
            global: globalConfig,
        });

        expect(wrapper.find('#datatable').exists()).toBe(true);
    });

    it('shows loader when loading state is true', () => {
        const wrapper = mount(Datatable, {
            props: {
                dataColumns: ['id', 'name'],
                url: '/api/data'
            },
            global: globalConfig,
        });

        expect(wrapper.find('loader-stub').exists()).toBe(true);
    });

    it('toggles all rows when toggleAll is called', async () => {
        const wrapper = mount(Datatable, {
            props: {
                dataColumns: ['id', 'name'],
                url: '/api/data'
            },
            global: globalConfig,
        });

        wrapper.vm.allMarked = true;
        wrapper.vm.toggleAll();
        
        expect(wrapper.vm.markedRows).toEqual([1, 2]);

        wrapper.vm.allMarked = false;
        wrapper.vm.toggleAll();
        expect(wrapper.vm.markedRows).toEqual([]);
    });

    it('calls tickets prop when markedRows change', async () => {
        const ticketsMock = jest.fn();
        const wrapper = mount(Datatable, {
            props: {
                dataColumns: ['id', 'name'],
                url: '/api/data',
                tickets: ticketsMock
            },
            global: globalConfig,
        });

        wrapper.vm.markedRows = [1];
        await wrapper.vm.$nextTick();
        expect(ticketsMock).toHaveBeenCalledWith([1]);
    });

    it('shows error message when showTable is false and error_message exists', async () => {
        const wrapper = mount(Datatable, {
            props: {
                dataColumns: ['id', 'name'],
                url: '/api/data'
            },
            global: globalConfig,
        });

        await wrapper.setData({
            showTable: false,
            error_message: 'Some Error'
        });

        expect(wrapper.find('.callout-danger').exists()).toBe(true);
        expect(wrapper.find('.callout-danger').text()).toContain('Some Error');
    });

    it('updates endPoint when url prop changes', async () => {
        const wrapper = mount(Datatable, {
            props: {
                dataColumns: ['id', 'name'],
                url: '/api/data'
            },
            global: globalConfig,
        });

        await wrapper.setProps({ url: '/api/new-data' });
        expect(wrapper.vm.endPoint).toBe('/api/new-data');
    });
});
