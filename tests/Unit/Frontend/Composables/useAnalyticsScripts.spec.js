import { defineComponent } from 'vue'
import { mount } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'
import { createTestingPinia } from '@pinia/testing'
import { useAnalyticsScripts } from '@/core/composables/useAnalyticsScripts.js'

// The `injected` Set is module-level. We use unique script IDs per test to avoid
// cross-test interference. We clean up injected <script> elements in beforeEach.

let _scriptIdCounter = 100

function nextId() {
    return ++_scriptIdCounter
}

function buildRouter(extraRoutes = []) {
    return createRouter({
        history: createMemoryHistory(),
        routes:  [
            ...extraRoutes,
            { path: '/',      component: { template: '<div />' } },
            { path: '/login', component: { template: '<div />' } },
            { path: '/:pathMatch(.*)*', component: { template: '<div />' } },
        ],
    })
}

// Mount the analytics composable, then navigate to the target path AFTER mount
// so that route.meta is reactive and populated correctly.
async function mountAnalytics(scriptsJson, targetPath = '/', extraRoutes = []) {
    document.body.innerHTML = `<div id="app-client" data-scripts='${JSON.stringify(scriptsJson)}'></div>`
    const router = buildRouter(extraRoutes)

    const TestComponent = defineComponent({
        setup() { useAnalyticsScripts() },
        template: '<div></div>',
    })

    const wrapper = mount(TestComponent, {
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

describe('useAnalyticsScripts', () => {
    beforeEach(() => {
        // Remove any scripts injected into head by previous tests
        document.head.querySelectorAll('script').forEach(s => s.remove())
    })

    it('mounts without error when scripts dataset is empty', async () => {
        const { wrapper } = await mountAnalytics([])
        expect(wrapper.exists()).toBe(true)
    })

    it('mounts without error when app-client element is absent', async () => {
        document.body.innerHTML = ''
        const router = buildRouter()

        const TestComponent = defineComponent({
            setup() { useAnalyticsScripts() },
            template: '<div></div>',
        })
        const wrapper = mount(TestComponent, {
            global: {
                plugins: [
                    router,
                    createTestingPinia({ stubActions: true, createSpy: jest.fn }),
                ],
            },
        })
        await router.push('/')
        await flushPromises()
        expect(wrapper.exists()).toBe(true)
    })

    it('injects plain JS snippet on mount for every-page scripts', async () => {
        const id = nextId()
        const marker = `__injected_${id}`
        const scripts = [
            { id, script: `window.${marker} = true;`, on_every_page: true, on_registration: false },
        ]
        await mountAnalytics(scripts, '/')
        const injectedEls = Array.from(document.head.querySelectorAll('script'))
        expect(injectedEls.some(s => s.textContent.includes(marker))).toBe(true)
    })

    it('does not inject on_every_page script twice (deduplication)', async () => {
        const id = nextId()
        const marker = `__dedup_${id}`
        const scriptsJson = JSON.stringify([
            { id, script: `window.${marker} = 1;`, on_every_page: true, on_registration: false },
        ])

        document.body.innerHTML = `<div id="app-client" data-scripts='${scriptsJson}'></div>`
        const router = buildRouter()
        const globalCfg = {
            plugins: [
                router,
                createTestingPinia({ stubActions: true, createSpy: jest.fn }),
            ],
        }

        const TestComponent = defineComponent({
            setup() { useAnalyticsScripts() },
            template: '<div></div>',
        })

        // Mount twice with the same module instance — dedup relies on module-level Set
        mount(TestComponent, { global: globalCfg })
        mount(TestComponent, { global: globalCfg })
        await router.push('/')
        await flushPromises()

        const allScripts = Array.from(document.head.querySelectorAll('script'))
        const matches = allScripts.filter(s => s.textContent.includes(marker))
        expect(matches.length).toBe(1)
    })

    it('skips injection when script content is empty', async () => {
        const id = nextId()
        const scripts = [
            { id, script: '   ', on_every_page: true, on_registration: false },
        ]
        const countBefore = document.head.querySelectorAll('script').length
        await mountAnalytics(scripts, '/')
        expect(document.head.querySelectorAll('script').length).toBe(countBefore)
    })

    it('injects on_registration scripts when navigating to /login', async () => {
        const id = nextId()
        const marker = `__registration_${id}`
        const scripts = [
            { id, script: `window.${marker} = true;`, on_every_page: false, on_registration: true },
        ]
        await mountAnalytics(scripts, '/login')
        const injectedEls = Array.from(document.head.querySelectorAll('script'))
        expect(injectedEls.some(s => s.textContent.includes(marker))).toBe(true)
    })

    it('does not inject on_registration scripts on non-login paths', async () => {
        const id = nextId()
        const marker = `__reg_only_${id}`
        const scripts = [
            { id, script: `window.${marker} = true;`, on_every_page: false, on_registration: true },
        ]
        await mountAnalytics(scripts, '/')
        const injectedEls = Array.from(document.head.querySelectorAll('script'))
        expect(injectedEls.some(s => s.textContent.includes(marker))).toBe(false)
    })

    it('extracts <script> tags from HTML content and appends each to head', async () => {
        const id = nextId()
        const marker = `__html_script_${id}`
        // Avoid the string literal "</script>" being parsed by the JS engine
        const htmlContent = `<script>window.${marker} = 1;<` + '/script>'
        const scripts = [
            { id, script: htmlContent, on_every_page: true, on_registration: false },
        ]
        await mountAnalytics(scripts, '/')
        const injectedEls = Array.from(document.head.querySelectorAll('script'))
        expect(injectedEls.some(s => s.textContent.includes(marker))).toBe(true)
    })

    it('does not inject on_every_page scripts when route has standalone meta', async () => {
        const id = nextId()
        const marker = `__standalone_${id}`
        const scripts = [
            { id, script: `window.${marker} = true;`, on_every_page: true, on_registration: false },
        ]
        const extraRoutes = [
            { path: '/embed', component: { template: '<div />' }, meta: { standalone: true } },
        ]

        // For the standalone check (in onMounted), we need the router already at /embed
        // when the component mounts. Push before mount and await ready.
        document.body.innerHTML = `<div id="app-client" data-scripts='${JSON.stringify(scripts)}'></div>`
        const router = buildRouter(extraRoutes)
        await router.push('/embed')
        await router.isReady()

        const TestComponent = defineComponent({
            setup() { useAnalyticsScripts() },
            template: '<div></div>',
        })
        mount(TestComponent, {
            global: {
                plugins: [
                    router,
                    createTestingPinia({ stubActions: true, createSpy: jest.fn }),
                ],
            },
        })
        await flushPromises()

        const injectedEls = Array.from(document.head.querySelectorAll('script'))
        expect(injectedEls.some(s => s.textContent.includes(marker))).toBe(false)
    })
})
