import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ImageField from '@/components/Reusable/FormField/ImageField.vue'

const onChangeMock = jest.fn()

const mountImageField = (props = {}) =>
    mount(ImageField, {
        props: {
            label: 'Profile Photo',
            name: 'photo',
            onChange: onChangeMock,
            componentName: 'image-field-test',
            ...props,
        },
        global: {
            plugins: [createTestingPinia()],
            stubs: {
                AppModal: {
                    template: '<div class="app-modal-stub" v-if="showModal"><slot name="title" /><slot name="fields" /><slot name="controls" /></div>',
                    props: ['showModal', 'onClose'],
                },
                VueCropper: {
                    template: '<div class="vue-cropper-stub"></div>',
                    props: ['src', 'guides', 'viewMode', 'dragMode', 'autoCrop', 'autoCropArea', 'background', 'rotatable', 'imgStyle', 'aspectRatio'],
                    methods: { replace: jest.fn(), getCroppedCanvas: () => ({ toDataURL: () => 'data:image/png;base64,abc' }), rotate: jest.fn() },
                },
            },
        },
    })

describe('ImageField.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        wrapper = mountImageField()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the outer div container', () => {
        expect(wrapper.find('div').exists()).toBe(true)
    })

    it('renders the file input', () => {
        expect(wrapper.find('input[type="file"]').exists()).toBe(true)
    })

    it('file input accepts image types', () => {
        const input = wrapper.find('input[type="file"]')
        expect(input.attributes('accept')).toContain('image/')
    })

    it('renders label text', () => {
        expect(wrapper.find('label').text()).toContain('Profile Photo')
    })

    it('renders required asterisk when required is true', () => {
        wrapper = mountImageField({ required: true })
        expect(wrapper.find('.text-danger').text()).toBe('*')
    })

    it('does not show modal initially', () => {
        expect(wrapper.find('.app-modal-stub').exists()).toBe(false)
    })

    it('shows preview image when value is provided', () => {
        wrapper = mountImageField({ value: 'https://example.com/existing.jpg' })
        expect(wrapper.find('img').exists()).toBe(true)
    })

    it('does not show preview image when value is empty', () => {
        // previewUrl is empty, so img preview area won't exist
        expect(wrapper.find('.d-flex img').exists()).toBe(false)
    })

    it('updates preview when value prop changes', async () => {
        await wrapper.setProps({ value: 'https://example.com/new.jpg' })
        expect(wrapper.vm.previewUrl).toBe('https://example.com/new.jpg')
    })

    // ── onFileSelected branches ───────────────────────────────────────
    it('onFileSelected returns early when no file selected', () => {
        const event = { target: { files: [] } }
        wrapper.vm.onFileSelected(event)
        expect(wrapper.vm.selectedFile).toBeNull()
    })

    it('onFileSelected shows alert and resets for non-image file', () => {
        const file = new File(['data'], 'doc.pdf', { type: 'application/pdf' })
        wrapper.vm.onFileSelected({ target: { files: [file] } })
        expect(wrapper.vm.selectedFile).toBeNull()
    })

    it('onFileSelected shows alert for unsupported image type (webp)', () => {
        const file = new File(['data'], 'img.webp', { type: 'image/webp' })
        wrapper.vm.onFileSelected({ target: { files: [file] } })
        expect(wrapper.vm.selectedFile).toBeNull()
    })

    it('onFileSelected shows alert when file is too large (>2MB)', () => {
        const big = new File([new ArrayBuffer(2097153)], 'big.png', { type: 'image/png' })
        wrapper.vm.onFileSelected({ target: { files: [big] } })
        expect(wrapper.vm.selectedFile).toBeNull()
    })

    it('onFileSelected sets selectedFile for valid png (FileReader not fired)', () => {
        // Stub FileReader so the async onload never fires and avoids jsdom/cropperjs leak
        const origFileReader = global.FileReader
        global.FileReader = class {
            readAsDataURL() {}
            set onload(_) {}
        }
        const file = new File(['fake'], 'photo.png', { type: 'image/png' })
        Object.defineProperty(file, 'size', { value: 1024 })
        wrapper.vm.onFileSelected({ target: { files: [file] } })
        expect(wrapper.vm.selectedFile).toBe(file)
        global.FileReader = origFileReader
    })

    // ── cropImage ─────────────────────────────────────────────────────
    it('cropImage updates cropImg when cropper is set', () => {
        const mockCropper = { getCroppedCanvas: () => ({ toDataURL: () => 'data:image/png;base64,abc' }), rotate: jest.fn() }
        wrapper.vm.cropper = mockCropper
        wrapper.vm.cropImage()
        expect(wrapper.vm.cropImg).toBe('data:image/png;base64,abc')
    })

    it('cropImage does nothing when cropper is null', () => {
        wrapper.vm.cropper = null
        expect(() => wrapper.vm.cropImage()).not.toThrow()
    })

    // ── rotateImage ───────────────────────────────────────────────────
    it('rotateImage calls cropper.rotate(90) when cropper is set', () => {
        const mockCropper = { rotate: jest.fn(), getCroppedCanvas: () => ({ toDataURL: () => 'data:x' }) }
        wrapper.vm.cropper = mockCropper
        wrapper.vm.rotateImage()
        expect(mockCropper.rotate).toHaveBeenCalledWith(90)
    })

    // ── onClose ───────────────────────────────────────────────────────
    it('onClose sets showModal to false', () => {
        wrapper.vm.showModal = true
        wrapper.vm.onClose()
        expect(wrapper.vm.showModal).toBe(false)
    })

    // ── onSubmit ──────────────────────────────────────────────────────
    it('onSubmit calls onChange with cropped data when cropper is set', () => {
        const mockCropper = { getCroppedCanvas: () => ({ toDataURL: () => 'data:image/png;base64,abc' }) }
        wrapper.vm.cropper = mockCropper
        wrapper.vm.selectedFile = new File(['f'], 'test.png', { type: 'image/png' })
        wrapper.vm.onSubmit()
        expect(onChangeMock).toHaveBeenCalledWith(
            expect.objectContaining({ name: 'test.png' }),
            'photo'
        )
    })

    it('onSubmit calls onClose when cropImg is empty', () => {
        wrapper.vm.cropper = null
        wrapper.vm.cropImg = ''
        wrapper.vm.showModal = true
        wrapper.vm.onSubmit()
        expect(wrapper.vm.showModal).toBe(false)
    })

    // ── b64toBlob ─────────────────────────────────────────────────────
    it('b64toBlob returns a Blob with the correct type', () => {
        const blob = wrapper.vm.b64toBlob(btoa('hello'), 'image/png')
        expect(blob).toBeInstanceOf(Blob)
        expect(blob.type).toBe('image/png')
    })

    it('b64toBlob works with custom sliceSize', () => {
        const blob = wrapper.vm.b64toBlob(btoa('a'.repeat(600)), 'image/jpeg', 256)
        expect(blob).toBeInstanceOf(Blob)
    })

    // ── changeRatio ───────────────────────────────────────────────────
    it('changeRatio sets aspectRatio and calls cropper.setAspectRatio', () => {
        const mockCropper = { setAspectRatio: jest.fn() }
        wrapper.vm.cropper = mockCropper
        wrapper.vm.changeRatio(16 / 9)
        expect(mockCropper.setAspectRatio).toHaveBeenCalledWith(16 / 9)
        expect(wrapper.vm.aspectRatio).toBeCloseTo(16 / 9)
    })

    it('changeRatio works when cropper is null', () => {
        wrapper.vm.cropper = null
        expect(() => wrapper.vm.changeRatio(1)).not.toThrow()
        expect(wrapper.vm.aspectRatio).toBe(1)
    })

    // ── resetInput ────────────────────────────────────────────────────
    it('resetInput clears fileInput value', () => {
        const input = document.createElement('input')
        input.value = 'file.png'
        wrapper.vm.fileInput = input
        wrapper.vm.resetInput()
        expect(input.value).toBe('')
    })

    it('resetInput does nothing when fileInput is null', () => {
        wrapper.vm.fileInput = null
        expect(() => wrapper.vm.resetInput()).not.toThrow()
    })
})
