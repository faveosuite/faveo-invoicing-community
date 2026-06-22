import { createPinia, setActivePinia } from 'pinia'
import { config, flushPromises } from '@vue/test-utils'
import MockAdapter from 'axios-mock-adapter'
import http from './resources/assets/js/plugins/axios.js'
import { registerDefaultHandlers } from './tests/Unit/Frontend/mocks/handlers/index.js'

// ── Globals available in every test file ─────────────────────────────────────
global.flushPromises = flushPromises
// Make __() available at module load time (some components call it eagerly in
// script setup outside any function, e.g. to build static option arrays).
global.__ = (key) => key

// ── DOM seed ──────────────────────────────────────────────────────────────────
// Must be set before any module-level store code reads data-* attributes.
// auth.js, cart.js and axios.js all read from #app-root or #app-client at
// module load time — this seed ensures those reads find valid values.
document.body.innerHTML = `
    <div
        id="app-root"
        data-base-url=""
        data-theme="adminlte"
        data-token="test-token"
        data-authenticated="false"
        data-user-id=""
        data-user-first-name="Test"
        data-user-last-name="User"
        data-user-username="testuser"
        data-user-email="test@example.com"
        data-user-role="admin"
        data-user-timezone="UTC"
    ></div>
`
const meta = document.createElement('meta')
meta.setAttribute('name', 'csrf-token')
meta.setAttribute('content', 'test-csrf-token')
document.head.appendChild(meta)

// ── Pinia ─────────────────────────────────────────────────────────────────────
// Fresh isolated store for every test.
// NOTE: component tests should pass createTestingPinia() directly to mount()
//       so actions are auto-stubbed and state can be preset via initialState.
beforeEach(() => {
    setActivePinia(createPinia())
})

// ── HTTP mock (axios-mock-adapter) ────────────────────────────────────────────
// A single shared MockAdapter is created once. Before every test we reset it
// and re-register the default happy-path handlers. Tests that need a different
// response can override with global.mockHttp.onGet(...).replyOnce(...).
global.mockHttp = new MockAdapter(http, { onNoMatch: 'throwException' })

beforeEach(() => {
    global.mockHttp.reset()
    registerDefaultHandlers(global.mockHttp)
})

// ── Vue Test Utils global config ──────────────────────────────────────────────
config.global.directives = {
    tooltip: () => {},
}

config.global.stubs = {
    'router-link': { template: '<a><slot /></a>' },
    'router-view': { template: '<div />' },
    Teleport: { template: '<div><slot /></div>' },
    Transition: false,
    TransitionGroup: false,
}

config.global.mocks = {
    $t: (key) => key,
    __: (key) => key,
}

// ── Suppress known noisy Vue warnings and jsdom errors ───────────────────────
const SUPPRESSED_WARN = [
    'Failed to resolve component',
    '[Vue Router warn]',
    'Extraneous non-emits event listeners',
    'injection "Symbol(router)" not found',
    'Invalid prop: type check failed for prop',
    'Invalid vnode type when creating vnode: undefined',
    'Component is missing template or render function',
    // cropperjs (VueCropper) tries to use canvas/Image APIs not supported in jsdom
    'Cannot read properties of undefined',
    'Write operation failed: computed value is readonly',
]

const SUPPRESSED_ERROR = [
    // jsdom does not implement navigation — fires whenever axios interceptor
    // sets window.location.href on a 401 response
    'Not implemented: navigation',
    // Component catch blocks that log 500 errors intentionally tested
    'Failed to fetch dashboard data',
    'AxiosError',
    // jsdom does not implement window.confirm/alert/prompt
    'Not implemented: window.confirm',
    'Not implemented: window.alert',
    'Not implemented: window.prompt',
]

// eslint-disable-next-line no-console
const _warn = console.warn.bind(console)
// eslint-disable-next-line no-console
const _error = console.error.bind(console)
beforeAll(() => {
    jest.spyOn(console, 'warn').mockImplementation((msg, ...args) => {
        if (SUPPRESSED_WARN.some(s => String(msg).includes(s))) return
        _warn(msg, ...args)
    })
    jest.spyOn(console, 'error').mockImplementation((msg, ...args) => {
        if (SUPPRESSED_ERROR.some(s => String(msg).includes(s))) return
        _error(msg, ...args)
    })
})

// ── Cleanup after each test ───────────────────────────────────────────────────
afterEach(() => {
    jest.clearAllMocks()
    jest.useRealTimers()
})

afterAll(() => {
    // eslint-disable-next-line no-console
    console.warn.mockRestore?.()
    // eslint-disable-next-line no-console
    console.error.mockRestore?.()
    global.mockHttp.restore()
})
