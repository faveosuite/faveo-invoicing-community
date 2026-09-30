import { mount } from '@vue/test-utils'
import UploadStatus from '@/components/Reusable/UploadStatus.vue'

describe('UploadStatus.vue', () => {
    it('is a vue instance', () => {
        const wrapper = mount(UploadStatus)
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders nothing extra by default (not uploading, no error, no uploadedName)', () => {
        const wrapper = mount(UploadStatus)
        expect(wrapper.find('.progress').exists()).toBe(false)
        expect(wrapper.find('.invalid-feedback').exists()).toBe(false)
        expect(wrapper.find('.text-success').exists()).toBe(false)
    })

    it('shows the progress bar and percentage while uploading', () => {
        const wrapper = mount(UploadStatus, { props: { uploading: true, progress: 42 } })
        expect(wrapper.find('.progress').exists()).toBe(true)
        expect(wrapper.find('.progress-bar').attributes('style')).toContain('width: 42%')
        expect(wrapper.text()).toContain('42%')
    })

    it('hides the progress bar once uploading finishes', () => {
        const wrapper = mount(UploadStatus, { props: { uploading: false, progress: 100 } })
        expect(wrapper.find('.progress').exists()).toBe(false)
    })

    it('shows the error message when error is set', () => {
        const wrapper = mount(UploadStatus, { props: { error: 'Something went wrong' } })
        expect(wrapper.find('.invalid-feedback').text()).toBe('Something went wrong')
    })

    it('shows the uploaded file name when set and there is no error', () => {
        const wrapper = mount(UploadStatus, { props: { uploadedName: 'build.zip' } })
        expect(wrapper.find('.text-success').text()).toBe('build.zip')
    })

    it('prefers the error message over the uploaded file name when both are set', () => {
        const wrapper = mount(UploadStatus, { props: { error: 'Upload failed', uploadedName: 'build.zip' } })
        expect(wrapper.find('.invalid-feedback').exists()).toBe(true)
        expect(wrapper.find('.text-success').exists()).toBe(false)
    })
})
