import { mount } from '@vue/test-utils'
import ImageElement from '@/components/Reusable/ImageElement.vue'

const mountImage = (props = {}) =>
    mount(ImageElement, {
        props: {
            id: 'profile-pic',
            sourceUrl: 'https://example.com/photo.jpg',
            ...props,
        },
    })

describe('ImageElement.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mountImage()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders an img element', () => {
        expect(wrapper.element.tagName).toBe('IMG')
    })

    it('sets the id attribute', () => {
        expect(wrapper.attributes('id')).toBe('profile-pic')
    })

    it('sets src from sourceUrl when provided', () => {
        expect(wrapper.attributes('src')).toBe('https://example.com/photo.jpg')
    })

    it('falls back to default image path when sourceUrl is empty', () => {
        wrapper = mountImage({ sourceUrl: '' })
        expect(wrapper.attributes('src')).toContain('default.png')
    })

    it('uses custom defaultImage', () => {
        wrapper = mountImage({ sourceUrl: '', defaultImage: 'placeholder.png' })
        expect(wrapper.attributes('src')).toContain('placeholder.png')
    })

    it('sets alt text', () => {
        wrapper = mountImage({ alternativeText: 'User photo' })
        expect(wrapper.attributes('alt')).toBe('User photo')
    })

    it('sets default alt to empty string', () => {
        expect(wrapper.attributes('alt')).toBe('')
    })

    it('sets width attribute', () => {
        wrapper = mountImage({ imgWidth: 150 })
        expect(wrapper.attributes('width')).toBe('150')
    })

    it('sets height attribute', () => {
        wrapper = mountImage({ imgHeight: 150 })
        expect(wrapper.attributes('height')).toBe('150')
    })

    it('uses auto as default width', () => {
        expect(wrapper.attributes('width')).toBe('auto')
    })

    it('uses auto as default height', () => {
        expect(wrapper.attributes('height')).toBe('auto')
    })

    it('applies classes array', () => {
        wrapper = mountImage({ classes: ['img-circle', 'img-responsive'] })
        expect(wrapper.classes()).toContain('img-circle')
    })

    it('handles image load error by replacing src with default', async () => {
        await wrapper.trigger('error')
        expect(wrapper.attributes('src')).toContain('default.png')
    })

    it('sets styleObject on the element', () => {
        wrapper = mountImage({ styleObject: { border: '1px solid red' } })
        expect(wrapper.element.style.border).toBe('1px solid red')
    })
})
