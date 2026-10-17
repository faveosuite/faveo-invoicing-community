import { setMetaDescription } from '@/core/composables/useSeoMeta.js'

describe('useSeoMeta — setMetaDescription', () => {
    afterEach(() => {
        document.querySelectorAll('meta[name="description"]').forEach(el => el.remove())
    })

    it('does nothing when content is falsy', () => {
        setMetaDescription('')
        expect(document.querySelector('meta[name="description"]')).toBeNull()
    })

    it('creates a meta[name="description"] tag when none exists', () => {
        setMetaDescription('Manage your billing online.')
        const tag = document.querySelector('meta[name="description"]')
        expect(tag).not.toBeNull()
        expect(tag.getAttribute('content')).toBe('Manage your billing online.')
    })

    it('appends the new tag to document.head', () => {
        setMetaDescription('Manage your billing online.')
        const tag = document.querySelector('meta[name="description"]')
        expect(tag.parentElement).toBe(document.head)
    })

    it('updates an existing tag instead of creating a duplicate', () => {
        setMetaDescription('First description')
        setMetaDescription('Second description')
        const tags = document.querySelectorAll('meta[name="description"]')
        expect(tags.length).toBe(1)
        expect(tags[0].getAttribute('content')).toBe('Second description')
    })
})
