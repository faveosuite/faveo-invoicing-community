import { config } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'

// setup fresh pinia before each test
beforeEach(() => {
    setActivePinia(createPinia())
})

// mock axios globally
jest.mock('../plugins/axios.js', () => ({
    default: {
        get: jest.fn(),
        post: jest.fn(),
        put: jest.fn(),
        delete: jest.fn(),
        interceptors: {
            request: { use: jest.fn() },
            response: { use: jest.fn() }
        }
    }
}))

// mock app-root for token/theme reading
document.body.innerHTML = `
    <div id="app-root" data-theme="adminlte" data-token="test-token"></div>
`
