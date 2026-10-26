import { mount, flushPromises } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { createRouter, createMemoryHistory } from 'vue-router'
import Navbar from '@/themes/porto/components/common/Navbar.vue'
import { useAuthStore } from '@/core/stores/auth'

jest.mock('@/themes/porto/components/store/BookDemoModal.vue', () => ({ template: '<div/>' }))
jest.mock('@/themes/porto/components/store/CloudTrialModal.vue', () => ({
    __esModule: true,
    PENDING_TRIAL_KEY: 'pendingCloudTrial',
    default: { name: 'CloudTrialModal', props: ['show'], template: '<div/>' },
}))

async function trialModalShown({ loggedIn, cloud = true, pending = true }) {
    document.body.innerHTML = `<div id="app-client" data-cloud="${cloud}"></div>`
    sessionStorage.clear()
    if (pending) sessionStorage.setItem('pendingCloudTrial', '{"domain":"acme"}')

    const router = createRouter({ history: createMemoryHistory(), routes: [{ path: '/', component: { template: '<div/>' } }] })
    await router.push('/')
    const pinia = createTestingPinia({ stubActions: true })
    useAuthStore(pinia).user = loggedIn ? { id: 1, role: 'user' } : null

    const wrapper = mount(Navbar, { global: { plugins: [pinia, router] }, attachTo: document.body })
    await flushPromises()
    return wrapper.findComponent({ name: 'CloudTrialModal' }).props('show')
}

describe('Navbar resumes a guest-started free trial', () => {
    it('reopens the trial modal once the user is logged in', async () => {
        expect(await trialModalShown({ loggedIn: true })).toBe(true)
    })

    it('stays closed while still a guest', async () => {
        expect(await trialModalShown({ loggedIn: false })).toBe(false)
    })

    it('stays closed when no trial was started', async () => {
        expect(await trialModalShown({ loggedIn: true, pending: false })).toBe(false)
    })

    it('stays closed when cloud trials are turned off', async () => {
        expect(await trialModalShown({ loggedIn: true, cloud: false })).toBe(false)
    })
})
