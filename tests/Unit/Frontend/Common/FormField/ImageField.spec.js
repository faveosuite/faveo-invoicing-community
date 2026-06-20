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
})
