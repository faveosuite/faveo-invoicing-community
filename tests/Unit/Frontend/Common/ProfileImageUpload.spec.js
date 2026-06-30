jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('vue-cropperjs', () => ({ default: { template: '<div />', name: 'VueCropper' } }))
jest.mock('cropperjs/dist/cropper.css', () => {})

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ProfileImageUpload from '@/themes/porto/components/common/ProfileImageUpload.vue'

const STUBS = ['AppModal', 'vue-cropper']

const mockCropperInstance = {
    getCroppedCanvas: jest.fn(() => ({ toDataURL: () => 'data:image/png;base64,abc123def456' })),
    rotate:           jest.fn(),
    setAspectRatio:   jest.fn(),
    replace:          jest.fn(),
}

describe('ProfileImageUpload.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        wrapper = mount(ProfileImageUpload, {
            props: { src: '', initials: 'JD', alt: 'John Doe' },
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
        wrapper.vm.cropper = mockCropperInstance
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('shows initials when no src is provided', () => {
        expect(wrapper.find('.client-avatar-initials').exists()).toBe(true)
    })

    it('shows img element when src prop is provided', async () => {
        const w = mount(ProfileImageUpload, {
            props: { src: 'http://example.com/avatar.png', initials: 'JD', alt: 'Avatar' },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        expect(w.find('.client-avatar-img').exists()).toBe(true)
    })

    it('showModal starts as false', () => {
        expect(wrapper.vm.showModal).toBe(false)
    })

    it('aspectRatio defaults to 1', () => {
        expect(wrapper.vm.aspectRatio).toBe(1)
    })

    // ── closeModal ───────────────────────────────────────────────────
    it('closeModal sets showModal to false', () => {
        wrapper.vm.showModal = true
        wrapper.vm.closeModal()
        expect(wrapper.vm.showModal).toBe(false)
    })

    // ── rotateImage ──────────────────────────────────────────────────
    it('rotateImage calls rotate(90) on the cropper', () => {
        wrapper.vm.rotateImage()
        expect(mockCropperInstance.rotate).toHaveBeenCalledWith(90)
    })

    // ── changeRatio ──────────────────────────────────────────────────
    it('changeRatio updates aspectRatio and calls setAspectRatio', () => {
        wrapper.vm.changeRatio(0)
        expect(wrapper.vm.aspectRatio).toBe(0)
        expect(mockCropperInstance.setAspectRatio).toHaveBeenCalledWith(0)
    })

    it('changeRatio to 16/9 sets the correct ratio', () => {
        wrapper.vm.changeRatio(16 / 9)
        expect(wrapper.vm.aspectRatio).toBeCloseTo(16 / 9)
    })

    // ── onCrop ───────────────────────────────────────────────────────
    it('onCrop calls getCroppedCanvas and stores the result', () => {
        wrapper.vm.onCrop()
        expect(mockCropperInstance.getCroppedCanvas).toHaveBeenCalled()
        expect(wrapper.vm.cropImg).toBe('data:image/png;base64,abc123def456')
    })

    it('onCrop does nothing when cropper is not set', () => {
        wrapper.vm.cropper = null
        expect(() => wrapper.vm.onCrop()).not.toThrow()
    })

    // ── onSubmit ─────────────────────────────────────────────────────
    it('onSubmit closes modal when no cropped image is available', () => {
        wrapper.vm.cropper = { getCroppedCanvas: () => null }
        wrapper.vm.showModal = true
        wrapper.vm.onSubmit()
        expect(wrapper.vm.showModal).toBe(false)
    })

    it('onSubmit emits change event with file and previewUrl', () => {
        wrapper.vm.onSubmit()
        const emitted = wrapper.emitted('change')
        expect(emitted).toBeTruthy()
        expect(emitted[0][0]).toHaveProperty('file')
        expect(emitted[0][0]).toHaveProperty('previewUrl')
    })

    it('onSubmit updates currentPreview with the cropped image URL', () => {
        wrapper.vm.onSubmit()
        expect(wrapper.vm.currentPreview).toBe('data:image/png;base64,abc123def456')
    })

    it('onSubmit closes the modal after emitting', () => {
        wrapper.vm.showModal = true
        wrapper.vm.onSubmit()
        expect(wrapper.vm.showModal).toBe(false)
    })

    // ── b64toBlob ─────────────────────────────────────────────────────
    it('b64toBlob returns a Blob with the specified content type', () => {
        const blob = wrapper.vm.b64toBlob('YWJj', 'image/png')
        expect(blob).toBeInstanceOf(Blob)
        expect(blob.type).toBe('image/png')
    })

    it('b64toBlob handles multi-chunk data larger than sliceSize', () => {
        const bigData = btoa('a'.repeat(1024))
        const blob = wrapper.vm.b64toBlob(bigData, 'image/jpeg', 100)
        expect(blob).toBeInstanceOf(Blob)
    })

    // ── onFileSelected ───────────────────────────────────────────────
    it('onFileSelected returns early when no file is in the event', async () => {
        await wrapper.vm.onFileSelected({ target: { files: [] } })
        expect(wrapper.vm.showModal).toBe(false)
    })

    it('onFileSelected shows alert and returns for non-image file types', async () => {
        const alertSpy = jest.spyOn(window, 'alert').mockImplementation(() => {})
        const file = new File(['content'], 'doc.pdf', { type: 'application/pdf' })
        await wrapper.vm.onFileSelected({ target: { files: [file], value: '' } })
        expect(alertSpy).toHaveBeenCalledWith('Only PNG and JPEG images are allowed.')
        alertSpy.mockRestore()
    })

    it('onFileSelected shows alert for files larger than 2 MB', async () => {
        const alertSpy = jest.spyOn(window, 'alert').mockImplementation(() => {})
        const bigContent = new Uint8Array(2097153)
        const file = new File([bigContent], 'big.png', { type: 'image/png' })
        await wrapper.vm.onFileSelected({ target: { files: [file], value: '' } })
        expect(alertSpy).toHaveBeenCalledWith('Image must be under 2 MB.')
        alertSpy.mockRestore()
    })

    it('onFileSelected reads valid PNG file and opens the modal', async () => {
        const MockFileReader = function () {
            this.readAsDataURL = jest.fn(() => {
                this.onload({ target: { result: 'data:image/png;base64,xyz' } })
            })
        }
        const OriginalFileReader = globalThis.FileReader
        globalThis.FileReader = MockFileReader

        const file = new File([''], 'avatar.png', { type: 'image/png' })
        await wrapper.vm.onFileSelected({ target: { files: [file], value: '' } })

        expect(wrapper.vm.imageSrc).toBe('data:image/png;base64,xyz')
        expect(wrapper.vm.showModal).toBe(true)

        globalThis.FileReader = OriginalFileReader
    })
})
