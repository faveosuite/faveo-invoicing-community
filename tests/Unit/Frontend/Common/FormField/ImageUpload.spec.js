import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ImageUpload from '@/components/Reusable/FormField/ImageUpload.vue'

const onChangeMock = jest.fn()

const mountImageUpload = (props = {}) =>
    mount(ImageUpload, {
        props: {
            label: 'Avatar',
            name: 'avatar',
            onChange: onChangeMock,
            componentName: 'image-upload-test',
            ...props,
        },
        global: {
            plugins: [createTestingPinia()],
            stubs: {
                FormFieldTemplate: {
                    template: '<div class="form-field-template-stub"><slot /></div>',
                    props: ['label', 'labelStyle', 'name', 'classname', 'hint', 'required'],
                },
                ImageElement: {
                    template: '<img class="image-element-stub" :src="sourceUrl" />',
                    props: ['id', 'classes', 'sourceUrl', 'title', 'styleObject'],
                },
                AppModal: {
                    template: '<div class="app-modal-stub" v-if="showModal"><slot name="title" /><slot name="fields" /><slot name="controls" /></div>',
                    props: ['showModal', 'onClose'],
                },
                VueCropper: {
                    template: '<div class="vue-cropper-stub"></div>',
                    props: ['src', 'guides', 'viewMode', 'dragMode', 'autoCrop', 'autoCropArea', 'background', 'rotatable', 'imgStyle', 'aspectRatio'],
                    methods: {
                        replace: jest.fn(),
                        getCroppedCanvas: () => ({ toDataURL: () => 'data:image/png;base64,abc' }),
                        rotate: jest.fn(),
                        setAspectRatio: jest.fn(),
                    },
                },
            },
        },
    })

describe('ImageUpload.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        wrapper = mountImageUpload()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders FormFieldTemplate stub', () => {
        expect(wrapper.find('.form-field-template-stub').exists()).toBe(true)
    })

    it('renders hidden file input', () => {
        const input = wrapper.find('input[type="file"]')
        expect(input.exists()).toBe(true)
        // style may be "display:none" or "display: none;" depending on browser
        expect(input.attributes('style').replace(/\s/g, '')).toContain('display:none')
    })

    it('renders image element', () => {
        expect(wrapper.find('.image-element-stub').exists()).toBe(true)
    })

    it('renders camera icon', () => {
        expect(wrapper.find('.fa-camera').exists()).toBe(true)
    })

    it('does not show modal initially', () => {
        expect(wrapper.find('.app-modal-stub').exists()).toBe(false)
    })

    it('sets previewUrl from value prop', () => {
        wrapper = mountImageUpload({ value: 'https://example.com/avatar.jpg' })
        expect(wrapper.vm.previewUrl).toBe('https://example.com/avatar.jpg')
    })

    it('updates previewUrl when value prop changes', async () => {
        await wrapper.setProps({ value: 'https://example.com/new.jpg' })
        expect(wrapper.vm.previewUrl).toBe('https://example.com/new.jpg')
    })

    it('disables file input when is_default is true', () => {
        wrapper = mountImageUpload({ is_default: true })
        expect(wrapper.find('input[type="file"]').attributes('disabled')).toBeDefined()
    })

    it('renders required asterisk when required is true', () => {
        wrapper = mountImageUpload({ required: true })
        expect(wrapper.find('.is-danger').exists()).toBe(true)
    })

    it('renders label text', () => {
        expect(wrapper.find('h6').text()).toContain('Avatar')
    })

    // ── onFileSelected branches ───────────────────────────────────────
    it('onFileSelected does not update selectedFile for non-image file', () => {
        const initial = wrapper.vm.selectedFile
        const file = new File(['data'], 'doc.pdf', { type: 'application/pdf' })
        wrapper.vm.onFileSelected({ target: { files: [file], value: '' } })
        expect(wrapper.vm.selectedFile).toBe(initial)
    })

    it('onFileSelected does not update selectedFile for unsupported image type (webp)', () => {
        const initial = wrapper.vm.selectedFile
        const file = new File(['data'], 'img.webp', { type: 'image/webp' })
        wrapper.vm.onFileSelected({ target: { files: [file], value: '' } })
        expect(wrapper.vm.selectedFile).toBe(initial)
    })

    it('onFileSelected does not update selectedFile when file is too large (>2MB)', () => {
        const initial = wrapper.vm.selectedFile
        const big = new File([new ArrayBuffer(2097153)], 'big.png', { type: 'image/png' })
        wrapper.vm.onFileSelected({ target: { files: [big], value: '' } })
        expect(wrapper.vm.selectedFile).toBe(initial)
    })

    it('onFileSelected sets selectedFile for valid png (FileReader not fired)', () => {
        // Stub FileReader so the async onload never fires and avoids jsdom/cropperjs leak
        const origFileReader = globalThis.FileReader
        globalThis.FileReader = class {
            readAsDataURL() {}
            set onload(_) {}
        }
        const file = new File(['fake'], 'photo.png', { type: 'image/png' })
        Object.defineProperty(file, 'size', { value: 1024 })
        wrapper.vm.onFileSelected({ target: { files: [file], value: '' } })
        expect(wrapper.vm.selectedFile).toBe(file)
        globalThis.FileReader = origFileReader
    })

    // ── cropImage ─────────────────────────────────────────────────────
    it('cropImage updates cropImg when cropper is set', () => {
        const mockCropper = { getCroppedCanvas: () => ({ toDataURL: () => 'data:image/png;base64,abc' }) }
        wrapper.vm.cropper = mockCropper
        wrapper.vm.cropImage()
        expect(wrapper.vm.cropImg).toBe('data:image/png;base64,abc')
    })

    it('cropImage does nothing when cropper is null', () => {
        wrapper.vm.cropper = null
        expect(() => wrapper.vm.cropImage()).not.toThrow()
    })

    // ── rotateImage ───────────────────────────────────────────────────
    it('rotateImage calls cropper.rotate(90)', () => {
        const mockCropper = { rotate: jest.fn() }
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
    it('onSubmit calls onChange with cropped data', () => {
        const mockCropper = { getCroppedCanvas: () => ({ toDataURL: () => 'data:image/png;base64,abc' }) }
        wrapper.vm.cropper = mockCropper
        wrapper.vm.selectedFile = new File(['f'], 'test.png', { type: 'image/png' })
        wrapper.vm.onSubmit()
        expect(onChangeMock).toHaveBeenCalledWith(
            expect.objectContaining({ name: 'test.png' }),
            'avatar'
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
    it('b64toBlob returns Blob with correct type', () => {
        const blob = wrapper.vm.b64toBlob(btoa('hello'), 'image/png')
        expect(blob).toBeInstanceOf(Blob)
        expect(blob.type).toBe('image/png')
    })

    // ── changeRatio ───────────────────────────────────────────────────
    it('changeRatio sets aspectRatio and calls setAspectRatio', () => {
        const mockCropper = { setAspectRatio: jest.fn() }
        wrapper.vm.cropper = mockCropper
        wrapper.vm.changeRatio(4 / 3)
        expect(mockCropper.setAspectRatio).toHaveBeenCalledWith(4 / 3)
        expect(wrapper.vm.aspectRatio).toBeCloseTo(4 / 3)
    })

    it('changeRatio works when cropper is null', () => {
        wrapper.vm.cropper = null
        expect(() => wrapper.vm.changeRatio(1)).not.toThrow()
    })

    // ── tooltipValue ──────────────────────────────────────────────────
    it('tooltipValue extracts filename from URL string', () => {
        wrapper.vm.tooltipValue('https://example.com/avatar.png')
        expect(wrapper.vm.tooltip).toBe('avatar.png')
    })

    it('tooltipValue uses file.name when file is an object', () => {
        wrapper.vm.tooltipValue({ name: 'photo.jpg' })
        expect(wrapper.vm.tooltip).toBe('photo.jpg')
    })

    it('tooltipValue sets no_file when null', () => {
        wrapper.vm.tooltipValue(null)
        expect(wrapper.vm.tooltip).toBe('no_file')
    })

    it('tooltipValue sets background to black for logo.png', () => {
        wrapper.vm.tooltipValue('https://example.com/logo.png')
        expect(wrapper.vm.styleObj.background).toBe('black')
    })
})
