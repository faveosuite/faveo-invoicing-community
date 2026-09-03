jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: {}, query: {} }),
    RouterLink: { template: '<a><slot/></a>' },
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import SeoPagesIndex from '@/pages/admin/settings/settings/seo/SeoPagesIndex.vue'

describe('SeoPagesIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(SeoPagesIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['DataTable', 'AppAlert', 'router-link'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders AppAlert', () => {
        expect(wrapper.find('app-alert-stub').exists()).toBe(true)
    })

    it('renders a DataTable stub pointed at /seo/default-pages', () => {
        const table = wrapper.find('data-table-stub')
        expect(table.exists()).toBe(true)
        expect(table.attributes('url')).toBe('/seo/default-pages')
    })

    it('renders the Pages card title', () => {
        expect(wrapper.find('.card-title').text()).toContain('message.pages')
    })

    describe('pageLabel via templates.page', () => {
        const page = (row) => wrapper.vm.tableOptions.templates.page(null, row)

        it('maps login to the login/register label', () => {
            expect(page({ page_key: 'login' })).toBe('message.seo_login_and_register')
        })

        it('maps forgot_password to the forgot-password label', () => {
            expect(page({ page_key: 'forgot_password' })).toBe('message.forgot-password')
        })

        it('maps reset_password to the reset_password label', () => {
            expect(page({ page_key: 'reset_password' })).toBe('message.reset_password')
        })

        it('maps cart to the shopping_cart label', () => {
            expect(page({ page_key: 'cart' })).toBe('message.shopping_cart')
        })

        it('falls back to the raw page_key for unknown keys', () => {
            expect(page({ page_key: 'some_unknown_page' })).toBe('some_unknown_page')
        })
    })

    describe('templates', () => {
        const tpl = () => wrapper.vm.tableOptions.templates

        it('meta_title returns — when falsy', () => {
            expect(tpl().meta_title(null, {})).toBe('—')
        })

        it('meta_title returns the value when set', () => {
            expect(tpl().meta_title(null, { meta_title: 'Login Page' })).toBe('Login Page')
        })

        it('meta_description returns — when falsy', () => {
            expect(tpl().meta_description(null, {})).toBe('—')
        })

        it('meta_description returns the value when set', () => {
            expect(tpl().meta_description(null, { meta_description: 'Sign in to your account' })).toBe('Sign in to your account')
        })

        it('action links to the edit route for the row page_key', () => {
            const vnode = tpl().action(null, { page_key: 'login' })
            expect(vnode.props.to).toBe('/settings/seo/login/edit')
        })
    })

    it('is filterable', () => {
        expect(wrapper.vm.tableOptions.filterable).toBe(true)
    })
})
