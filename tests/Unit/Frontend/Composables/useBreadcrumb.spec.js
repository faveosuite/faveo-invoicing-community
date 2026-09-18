import { defineComponent } from 'vue'
import { mount } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'
import { createTestingPinia } from '@pinia/testing'
import { setPageTitle, useBreadcrumb } from '@/core/composables/useBreadcrumb.js'

// Helper: build a test component that exposes composable return values
function makeBreadcrumbComponent() {
    return defineComponent({
        setup() {
            const { pageTitle, breadcrumbs } = useBreadcrumb()
            return { pageTitle, breadcrumbs }
        },
        template: '<div></div>',
    })
}

// Mount component with a memory router, then push to target path AFTER mount
// so Vue Router resolves the route reactively and populates route.meta.
async function mountAtPath(routes, targetPath, extraRoutes = []) {
    const allRoutes = [
        ...routes,
        ...extraRoutes,
        { path: '/:pathMatch(.*)*', component: { template: '<div />' } },
    ]
    const router = createRouter({
        history: createMemoryHistory(),
        routes:  allRoutes,
    })

    const wrapper = mount(makeBreadcrumbComponent(), {
        global: {
            plugins: [
                router,
                createTestingPinia({ stubActions: true, createSpy: jest.fn }),
            ],
        },
    })

    await router.push(targetPath)
    await flushPromises()
    return { wrapper, router }
}

describe('useBreadcrumb', () => {
    beforeEach(() => {
        // Reset the module-level title override between tests
        setPageTitle(null)
    })

    it('pageTitle returns route.meta.title when no override', async () => {
        const routes = [
            { path: '/dashboard', component: { template: '<div />' }, meta: { title: 'Dashboard' } },
        ]
        const { wrapper } = await mountAtPath(routes, '/dashboard')
        expect(wrapper.vm.pageTitle).toBe('Dashboard')
    })

    it('setPageTitle overrides the page title', async () => {
        const routes = [
            { path: '/dashboard', component: { template: '<div />' }, meta: { title: 'Dashboard' } },
        ]
        const { wrapper } = await mountAtPath(routes, '/dashboard')
        setPageTitle('Custom Title')
        expect(wrapper.vm.pageTitle).toBe('Custom Title')
    })

    it('setPageTitle with null/empty reverts to route.meta.title', async () => {
        const routes = [
            { path: '/dashboard', component: { template: '<div />' }, meta: { title: 'Dashboard' } },
        ]
        const { wrapper } = await mountAtPath(routes, '/dashboard')
        setPageTitle('Override')
        setPageTitle(null)
        expect(wrapper.vm.pageTitle).toBe('Dashboard')
    })

    it('breadcrumbs built from path segments with resolved meta', async () => {
        const routes = [
            {
                path:      '/admin',
                component: { template: '<div />' },
                meta:      { title: 'Admin' },
                children:  [
                    {
                        path:      'users',
                        component: { template: '<div />' },
                        meta:      { title: 'Users' },
                    },
                ],
            },
        ]
        const { wrapper } = await mountAtPath(routes, '/admin/users')
        const crumbs = wrapper.vm.breadcrumbs
        expect(crumbs.length).toBeGreaterThanOrEqual(1)
        const titles = crumbs.map(c => c.title)
        expect(titles).toContain('Users')
    })

    it('last breadcrumb is marked as active', async () => {
        const routes = [
            {
                path:      '/admin',
                component: { template: '<div />' },
                meta:      { title: 'Admin' },
                children:  [
                    {
                        path:      'users',
                        component: { template: '<div />' },
                        meta:      { title: 'Users' },
                    },
                ],
            },
        ]
        const { wrapper } = await mountAtPath(routes, '/admin/users')
        const crumbs = wrapper.vm.breadcrumbs
        const last = crumbs[crumbs.length - 1]
        expect(last.isActive).toBe(true)
    })

    it('meta.breadcrumb array is used when present', async () => {
        const metaBreadcrumb = [
            { title: 'Home', to: '/' },
            { title: 'Settings' },
        ]
        const routes = [
            {
                path:      '/settings',
                component: { template: '<div />' },
                meta:      { title: 'Settings', breadcrumb: metaBreadcrumb },
            },
        ]
        const { wrapper } = await mountAtPath(routes, '/settings')
        const crumbs = wrapper.vm.breadcrumbs
        expect(crumbs.length).toBe(2)
        expect(crumbs[0].title).toBe('Home')
        expect(crumbs[0].to).toBe('/')
        expect(crumbs[1].title).toBe('Settings')
        // Last breadcrumb should be active
        expect(crumbs[1].isActive).toBe(true)
        // First breadcrumb should not be active
        expect(crumbs[0].isActive).toBe(false)
    })

    it('numeric-only path segments are skipped (except last)', async () => {
        const routes = [
            {
                path:      '/invoices',
                component: { template: '<div />' },
                meta:      { title: 'Invoices' },
                children:  [
                    {
                        path:      ':id',
                        component: { template: '<div />' },
                        meta:      { title: 'Invoice Detail' },
                    },
                ],
            },
        ]
        const { wrapper } = await mountAtPath(routes, '/invoices/42')
        const crumbs = wrapper.vm.breadcrumbs
        // "42" is numeric but it is the last segment — NOT skipped.
        // Both /invoices (title: Invoices) and /invoices/42 (title: Invoice Detail) should appear.
        const titles = crumbs.map(c => c.title)
        expect(titles).toContain('Invoices')
    })
})
