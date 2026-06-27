jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: {}, query: {} }),
    RouterLink: { template: '<a><slot /></a>' },
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import Queues from '@/pages/admin/settings/common/Queues.vue'

describe('Queues.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(Queues, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'DataTable', 'SelectField', 'action-button',
                    'inline-loader', 'loader', 'router-link',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the queue card', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('handles verify-php-path POST on copyCommand', async () => {
        global.mockHttp.onPost(/\/verify-php-path/).reply(200, { message: 'OK' })
        Object.assign(navigator, {
            clipboard: { writeText: jest.fn().mockResolvedValue(undefined) },
        })
        await wrapper.vm.copyCommand()
        await flushPromises()
        expect(global.mockHttp.history.post.length).toBeGreaterThan(0)
    })

    it('handles activate POST to correct endpoint', async () => {
        global.mockHttp.onPost(/\/queue\/5\/activate/).reply(200, { message: 'Activated' })
        await wrapper.vm.activate(5)
        await flushPromises()
        expect(global.mockHttp.history.post.some(r => r.url.includes('/queue/5/activate'))).toBe(true)
        expect(successHandler).toHaveBeenCalled()
    })

    it('handles 500 error on activate', async () => {
        global.mockHttp.onPost(/\/queue\/99\/activate/).reply(500)
        await wrapper.vm.activate(99)
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    // ── clearPhpPath ─────────────────────────────────────────────────
    it('clearPhpPath resets phpPath and customPhpPath', () => {
        wrapper.vm.phpPath       = 'other'
        wrapper.vm.customPhpPath = '/usr/bin/php'
        wrapper.vm.clearPhpPath()
        expect(wrapper.vm.phpPath).toBe('')
        expect(wrapper.vm.customPhpPath).toBe('')
    })

    // ── cronCommand computed ─────────────────────────────────────────
    it('cronCommand uses phpPath when phpPath is not "other"', () => {
        wrapper.vm.phpPath     = '/usr/bin/php8'
        wrapper.vm.cronPath    = '/var/www/artisan'
        wrapper.vm.customPhpPath = ''
        expect(wrapper.vm.cronCommand).toBe('/usr/bin/php8 -q /var/www/artisan queue:work')
    })

    it('cronCommand uses customPhpPath when phpPath is "other"', () => {
        wrapper.vm.phpPath       = 'other'
        wrapper.vm.customPhpPath = '/opt/php/php'
        wrapper.vm.cronPath      = '/var/www/artisan'
        expect(wrapper.vm.cronCommand).toBe('/opt/php/php -q /var/www/artisan queue:work')
    })

    it('cronCommand omits the path prefix when both paths are empty', () => {
        wrapper.vm.phpPath       = ''
        wrapper.vm.customPhpPath = ''
        wrapper.vm.cronPath      = '/var/www/artisan'
        expect(wrapper.vm.cronCommand).toBe('/var/www/artisan queue:work')
    })

    // ── phpPathOptions computed ──────────────────────────────────────
    it('phpPathOptions includes an "other" entry at the end', () => {
        wrapper.vm.phpPaths = ['/usr/bin/php8.1', '/usr/bin/php8.2']
        const opts = wrapper.vm.phpPathOptions
        expect(opts[opts.length - 1].id).toBe('other')
        expect(opts.length).toBe(3)
    })

    // ── copyCommand — error path ────────────────────────────────────
    it('copyCommand calls errorHandler on verify failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onPost(/\/verify-php-path/).reply(500)
        await wrapper.vm.copyCommand()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        expect(wrapper.vm.copying).toBe(false)
    })

    // ── templates.name ───────────────────────────────────────────────
    it('templates.name returns plain text when no link', () => {
        const result = wrapper.vm.tableOptions.templates.name(null, {
            QueueDetails: { id: null, name: { text: 'SyncQueue', link: null } },
        })
        expect(result).toBe('SyncQueue')
    })

    it('templates.name returns — when no QueueDetails', () => {
        const result = wrapper.vm.tableOptions.templates.name(null, {})
        expect(result).toBe('—')
    })

    it('templates.name returns RouterLink when link and id are present', () => {
        const vnode = wrapper.vm.tableOptions.templates.name(null, {
            QueueDetails: { id: 3, name: { text: 'DatabaseQueue', link: '/queue/3' } },
        })
        expect(vnode).toBeTruthy()
        expect(typeof vnode).toBe('object')
    })

    // ── templates.status ────────────────────────────────────────────
    it('templates.status renders success button when status.code is 1', () => {
        const vnode = wrapper.vm.tableOptions.templates.status(null, {
            QueueDetails: { status: { code: 1, label: 'Active' } },
        })
        expect(vnode.props.class).toContain('btn-success')
    })

    it('templates.status renders danger button when status.code is not 1', () => {
        const vnode = wrapper.vm.tableOptions.templates.status(null, {
            QueueDetails: { status: { code: 0, label: 'Inactive' } },
        })
        expect(vnode.props.class).toContain('btn-danger')
    })

    it('templates.status renders — when no QueueDetails', () => {
        const vnode = wrapper.vm.tableOptions.templates.status(null, {})
        expect(vnode.children).toBe('—')
    })

    // ── templates.action ────────────────────────────────────────────
    it('templates.action renders a button for a non-activated queue', () => {
        const vnode = wrapper.vm.tableOptions.templates.action(null, {
            QueueDetails: { id: 1, action: { type: 'deactivated' } },
        })
        expect(vnode.props.disabled).toBe(false)
    })

    it('templates.action renders a disabled button for an activated queue', () => {
        const vnode = wrapper.vm.tableOptions.templates.action(null, {
            QueueDetails: { id: 1, action: { type: 'activated' } },
        })
        expect(vnode.props.disabled).toBe(true)
    })

    // ── requestAdapter ───────────────────────────────────────────────
    it('requestAdapter defaults to desc sort-order when no orderBy (latest first)', () => {
        const result = wrapper.vm.tableOptions.requestAdapter({ ascending: true, query: '', page: 1, limit: 10 })
        expect(result['sort-order']).toBe('desc')
    })

    it('requestAdapter maps ascending:false to desc sort-order', () => {
        const result = wrapper.vm.tableOptions.requestAdapter({ ascending: false, query: '', page: 1, limit: 10 })
        expect(result['sort-order']).toBe('desc')
    })

    it('requestAdapter defaults sort-field to name', () => {
        const result = wrapper.vm.tableOptions.requestAdapter({ ascending: true, query: '', page: 1, limit: 10 })
        expect(result['sort-field']).toBe('name')
    })

    // ── responseAdapter ──────────────────────────────────────────────
    it('responseAdapter sets activeQueueName and populates phpPath from data', () => {
        wrapper.vm.tableOptions.responseAdapter({
            data: {
                data: {
                    cron_path:    '/var/www/artisan',
                    php_paths:    ['/usr/bin/php8.1'],
                    active_queue: { name: 'Database' },
                    queues:       { data: [], total: 0 },
                },
            },
        })
        expect(wrapper.vm.activeQueueName).toBe('Database')
        expect(wrapper.vm.cronPath).toBe('/var/www/artisan')
        expect(wrapper.vm.phpPaths).toEqual(['/usr/bin/php8.1'])
    })

    it('responseAdapter clears phpPath when active_queue is not Database', () => {
        wrapper.vm.phpPath = '/usr/bin/php'
        wrapper.vm.tableOptions.responseAdapter({
            data: {
                data: {
                    cron_path:    '/var/www/artisan',
                    php_paths:    [],
                    active_queue: { name: 'Sync' },
                    queues:       { data: [], total: 0 },
                },
            },
        })
        expect(wrapper.vm.phpPath).toBe('')
        expect(wrapper.vm.activeQueueName).toBe('Sync')
    })

    it('responseAdapter returns correct data and count', () => {
        const result = wrapper.vm.tableOptions.responseAdapter({
            data: {
                data: {
                    cron_path:    '',
                    php_paths:    [],
                    active_queue: { name: 'Sync' },
                    queues:       { data: [{ id: 1 }], total: 5 },
                },
            },
        })
        expect(result.data).toEqual([{ id: 1 }])
        expect(result.count).toBe(5)
    })

    it('responseAdapter handles empty data gracefully', () => {
        expect(() => wrapper.vm.tableOptions.responseAdapter({ data: {} })).not.toThrow()
    })
})
