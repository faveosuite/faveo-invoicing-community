import { SEO_ITEM_SHORTCODES } from '@/core/utils/seoShortcodes'

describe('seoShortcodes', () => {
    it('exports exactly two shortcodes', () => {
        expect(SEO_ITEM_SHORTCODES).toHaveLength(2)
    })

    it('every shortcode has a code, label, and description', () => {
        SEO_ITEM_SHORTCODES.forEach((sc) => {
            expect(typeof sc.code).toBe('string')
            expect(typeof sc.label).toBe('string')
            expect(typeof sc.description).toBe('string')
        })
    })

    it('includes the {name} shortcode labeled "Title"', () => {
        const nameShortcode = SEO_ITEM_SHORTCODES.find(sc => sc.code === '{name}')
        expect(nameShortcode).toBeTruthy()
        expect(nameShortcode.label).toBe('Title')
    })

    it('includes the {company} shortcode labeled "Company"', () => {
        const companyShortcode = SEO_ITEM_SHORTCODES.find(sc => sc.code === '{company}')
        expect(companyShortcode).toBeTruthy()
        expect(companyShortcode.label).toBe('Company')
    })
})
