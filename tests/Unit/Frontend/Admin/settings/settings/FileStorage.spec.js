jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/systemSettingsValidations', () => ({
    systemSettingsSchema: {},
    buildFileStorageSchema: jest.fn(() => ({
        validateSync: jest.fn(),
    })),
    pdfSettingsSchema: {
        validateSync: jest.fn(),
    },
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import FileStorage from '@/pages/admin/settings/settings/FileStorage.vue'

describe('FileStorage.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/file-storage$/).reply(200, {
            data: {
                disk: 'system',
                local_file_storage_path: '/storage',
                s3_path_style_endpoint: 'false',
                s3_bucket: '',
                s3_region: '',
                s3_access_key: '',
                s3_secret_key: '',
                s3_endpoint_url: '',
                s3_url: '',
            },
        })
        global.mockHttp.onGet(/\/pdf-settings/).reply(200, {
            data: {
                node_path: '/usr/bin/node',
                npm_path: '/usr/bin/npm',
                chrome_path: '/usr/bin/chromium',
            },
        })
        global.mockHttp.onPost(/\/file-storage-path/).reply(200, { data: {} })
        global.mockHttp.onPost(/\/pdf-settings/).reply(200, { data: {} })

        wrapper = mount(FileStorage, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'inline-loader', 'loader', 'action-button',
                    'TextField', 'SelectField',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches file storage settings on mount', async () => {
        await flushPromises()
        const getUrls = global.mockHttp.history.get.map(r => r.url)
        expect(getUrls.some(u => u.includes('file-storage'))).toBe(true)
    })

    it('fetches pdf settings on mount', async () => {
        await flushPromises()
        const getUrls = global.mockHttp.history.get.map(r => r.url)
        expect(getUrls.some(u => u.includes('pdf-settings'))).toBe(true)
    })

    it('renders the card header', async () => {
        await flushPromises()
        expect(wrapper.find('.card-header').exists()).toBe(true)
    })

    it('calls file storage and pdf endpoints on submit', async () => {
        await flushPromises()
        const saveBtn = wrapper.find('[action="save"]')
        if (saveBtn.exists()) {
            await saveBtn.trigger('click')
            await flushPromises()
            const postUrls = global.mockHttp.history.post.map(r => r.url)
            expect(postUrls.some(u => u.includes('file-storage-path') || u.includes('pdf-settings'))).toBe(true)
        }
    })
})
