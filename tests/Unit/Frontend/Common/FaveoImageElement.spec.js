import { mount } from '@vue/test-utils'
import FaveoImageElement from '@/components/Reusable/FaveoImageElement.vue'

const mountImage = (props = {}) =>
    mount(FaveoImageElement, {
        props: {
            id: 'test-img',
            sourceUrl: 'https://example.com/image.png',
            ...props,
        },
    })

describe('FaveoImageElement.vue', () => {
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
        expect(wrapper.attributes('id')).toBe('test-img')
    })

    it('sets the src to sourceUrl when provided', () => {
        expect(wrapper.attributes('src')).toBe('https://example.com/image.png')
    })

    it('falls back to default image when sourceUrl is empty', () => {
        wrapper = mountImage({ sourceUrl: '' })
        expect(wrapper.attributes('src')).toContain('default.png')
    })

    it('uses custom defaultImage prop', () => {
        wrapper = mountImage({ sourceUrl: '', defaultImage: 'logo.png' })
        expect(wrapper.attributes('src')).toContain('logo.png')
    })

    it('sets alt text from alternativeText prop', () => {
        wrapper = mountImage({ alternativeText: 'My Image' })
        expect(wrapper.attributes('alt')).toBe('My Image')
    })

    it('sets default alt to empty string', () => {
        expect(wrapper.attributes('alt')).toBe('')
    })

    it('sets width from imgWidth prop', () => {
        wrapper = mountImage({ imgWidth: 100 })
        expect(wrapper.attributes('width')).toBe('100')
    })

    it('sets height from imgHeight prop', () => {
        wrapper = mountImage({ imgHeight: 80 })
        expect(wrapper.attributes('height')).toBe('80')
    })

    it('defaults width to auto', () => {
        expect(wrapper.attributes('width')).toBe('auto')
    })

    it('defaults height to auto', () => {
        expect(wrapper.attributes('height')).toBe('auto')
    })

    it('applies classes from classes prop', () => {
        wrapper = mountImage({ classes: ['img-circle', 'profile-user-img'] })
        expect(wrapper.classes()).toContain('img-circle')
        expect(wrapper.classes()).toContain('profile-user-img')
    })

    it('replaces src on image load error', async () => {
        await wrapper.trigger('error')
        expect(wrapper.attributes('src')).toContain('default.png')
    })
})
