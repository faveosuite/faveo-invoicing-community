import { mount, shallowMount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { createRouter, createMemoryHistory } from 'vue-router'

const defaultStubs = {
    'router-link': { template: '<a><slot /></a>' },
    'router-view': { template: '<div />' },
    Teleport: { template: '<div><slot /></div>' },
    Transition: { template: '<div><slot /></div>' },
    TransitionGroup: { template: '<div><slot /></div>' },
}

const defaultMocks = {
    $t: (key) => key,
    __: (key) => key,
}

/**
 * Mount with a fresh createTestingPinia.
 * Actions are auto-stubbed as jest.fn() — assert calls, not side effects.
 * Override initialState to pre-seed any store before mount.
 *
 * @example
 * const wrapper = mountWithPinia(Alert, {
 *   props: { componentName: 'dashboard' },
 *   initialState: { alert: { type: 'success', message: 'Done', component_name: 'dashboard' } }
 * })
 */
export function mountWithPinia(component, {
    initialState = {},
    props = {},
    stubs = {},
    mocks = {},
    ...options
} = {}) {
    return mount(component, {
        props,
        global: {
            plugins: [
                createTestingPinia({
                    initialState,
                    stubActions: true,
                    createSpy: jest.fn,
                }),
            ],
            stubs: { ...defaultStubs, ...stubs },
            mocks: { ...defaultMocks, ...mocks },
        },
        ...options,
    })
}

/**
 * Mount with real Pinia (actions run for real).
 * Use this when testing a component's full integration with store logic.
 * Pair with MSW handlers to control HTTP responses.
 *
 * @example
 * const wrapper = mountWithRealPinia(CartPage)
 * await flushPromises()
 * expect(wrapper.find('.item-count').text()).toBe('2 items')
 */
export function mountWithRealPinia(component, {
    props = {},
    stubs = {},
    mocks = {},
    ...options
} = {}) {
    const { createPinia } = require('pinia')
    return mount(component, {
        props,
        global: {
            plugins: [createPinia()],
            stubs: { ...defaultStubs, ...stubs },
            mocks: { ...defaultMocks, ...mocks },
        },
        ...options,
    })
}

/**
 * Mount with a memory router.
 * Use for components that call useRoute() / useRouter() / useBreadcrumb().
 *
 * @example
 * const wrapper = mountWithRouter(Breadcrumbs, {
 *   routes: [{ path: '/admin/users', component: UserIndex, meta: { title: 'Users' } }],
 *   currentPath: '/admin/users',
 * })
 */
export function mountWithRouter(component, {
    routes = [],
    currentPath = '/',
    initialState = {},
    props = {},
    stubs = {},
    mocks = {},
    ...options
} = {}) {
    const router = createRouter({
        history: createMemoryHistory(),
        routes: routes.length
            ? routes
            : [{ path: '/:pathMatch(.*)*', component: { template: '<div />' } }],
    })

    router.push(currentPath)

    return mount(component, {
        props,
        global: {
            plugins: [
                router,
                createTestingPinia({
                    initialState,
                    stubActions: true,
                    createSpy: jest.fn,
                }),
            ],
            stubs: { ...defaultStubs, ...stubs },
            mocks: { ...defaultMocks, ...mocks },
        },
        ...options,
    })
}

/**
 * Shallow mount with Pinia — child components are stubbed automatically.
 * Use for components with deeply nested or heavy children.
 */
export function shallowMountWithPinia(component, {
    initialState = {},
    props = {},
    mocks = {},
    ...options
} = {}) {
    return shallowMount(component, {
        props,
        global: {
            plugins: [
                createTestingPinia({
                    initialState,
                    stubActions: true,
                    createSpy: jest.fn,
                }),
            ],
            mocks: { ...defaultMocks, ...mocks },
        },
        ...options,
    })
}

/**
 * Wait for all pending promises and Vue DOM updates to settle.
 * Use after triggering events or calling store actions.
 */
export { flushPromises } from '@vue/test-utils'
