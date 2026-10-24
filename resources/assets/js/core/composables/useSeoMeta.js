/**
 * useSeoMeta — upserts the <meta name="description"> tag on client-side
 * navigation. Secondary UX layer only: crawlers see the server-rendered tag
 * from client.blade.php (SeoMetaService); this keeps the DOM tag in sync for
 * users navigating between SPA routes without a full page reload.
 */
export function setMetaDescription(content) {
    if (!content) return

    let tag = document.querySelector('meta[name="description"]')
    if (!tag) {
        tag = document.createElement('meta')
        tag.setAttribute('name', 'description')
        document.head.appendChild(tag)
    }
    tag.setAttribute('content', content)
}
