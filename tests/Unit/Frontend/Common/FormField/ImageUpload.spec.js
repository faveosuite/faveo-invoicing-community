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
})
