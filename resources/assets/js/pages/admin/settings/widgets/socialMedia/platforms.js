// Known platforms -> their icon CSS classes, brand colors, categories, and helpers
// Admin can pick an icon visually, search easily, or customize freely.

export const CATEGORIES = [
    { id: 'all', label: 'All Icons', icon: 'fas fa-border-all' },
    { id: 'popular', label: 'Popular', icon: 'fas fa-fire' },
    { id: 'social', label: 'Social', icon: 'fas fa-share-nodes' },
    { id: 'chat', label: 'Chat & Messaging', icon: 'fas fa-comments' },
    { id: 'dev', label: 'Dev & Professional', icon: 'fas fa-briefcase' },
    { id: 'media', label: 'Media & Streaming', icon: 'fas fa-play' },
    { id: 'contact', label: 'Contact & Web', icon: 'fas fa-globe' },
    { id: 'custom', label: 'Custom Icon', icon: 'fas fa-sliders' },
]

export const POPULAR_PLATFORM_IDS = [
    'x', 'facebook', 'instagram', 'linkedin', 'youtube', 'tiktok',
    'whatsapp', 'telegram', 'discord', 'github', 'reddit', 'threads',
]

export const SOCIAL_PLATFORMS = {
    facebook: {
        label: 'Facebook',
        class: 'social-icons-facebook',
        fa_class: 'fab fa-facebook-f',
        color: '#1877f2',
        category: 'social',
        urlTemplate: 'https://facebook.com/',
        keywords: ['fb', 'facebook', 'meta'],
    },
    x: {
        label: 'X (Twitter)',
        class: 'social-icons-x',
        fa_class: 'fab fa-x-twitter',
        color: '#000000',
        category: 'social',
        urlTemplate: 'https://x.com/',
        keywords: ['twitter', 'tweet', 'x'],
    },
    instagram: {
        label: 'Instagram',
        class: 'social-icons-instagram',
        fa_class: 'fab fa-instagram',
        color: '#e4405f',
        category: 'social',
        urlTemplate: 'https://instagram.com/',
        keywords: ['insta', 'ig', 'photo', 'reels'],
    },
    linkedin: {
        label: 'LinkedIn',
        class: 'social-icons-linkedin',
        fa_class: 'fab fa-linkedin-in',
        color: '#0a66c2',
        category: 'dev',
        urlTemplate: 'https://linkedin.com/in/',
        keywords: ['linkedin', 'job', 'work', 'career'],
    },
    youtube: {
        label: 'YouTube',
        class: 'social-icons-youtube',
        fa_class: 'fab fa-youtube',
        color: '#ff0000',
        category: 'media',
        urlTemplate: 'https://youtube.com/@',
        keywords: ['video', 'yt', 'channel', 'stream'],
    },
    tiktok: {
        label: 'TikTok',
        class: 'social-icons-tiktok',
        fa_class: 'fab fa-tiktok',
        color: '#000000',
        category: 'media',
        urlTemplate: 'https://tiktok.com/@',
        keywords: ['video', 'shorts', 'tiktok'],
    },
    whatsapp: {
        label: 'WhatsApp',
        class: 'social-icons-whatsapp',
        fa_class: 'fab fa-whatsapp',
        color: '#25d366',
        category: 'chat',
        urlTemplate: 'https://wa.me/',
        keywords: ['chat', 'phone', 'message', 'wa'],
    },
    telegram: {
        label: 'Telegram',
        class: 'social-icons-telegram',
        fa_class: 'fab fa-telegram',
        color: '#229ed9',
        category: 'chat',
        urlTemplate: 'https://t.me/',
        keywords: ['chat', 'tg', 'channel', 'bot'],
    },
    discord: {
        label: 'Discord',
        class: 'social-icons-discord',
        fa_class: 'fab fa-discord',
        color: '#5865f2',
        category: 'chat',
        urlTemplate: 'https://discord.gg/',
        keywords: ['chat', 'server', 'gaming', 'voice'],
    },
    github: {
        label: 'GitHub',
        class: 'social-icons-github',
        fa_class: 'fab fa-github',
        color: '#24292e',
        category: 'dev',
        urlTemplate: 'https://github.com/',
        keywords: ['git', 'code', 'repo', 'open source'],
    },
    reddit: {
        label: 'Reddit',
        class: 'social-icons-reddit',
        fa_class: 'fab fa-reddit-alien',
        color: '#ff4500',
        category: 'social',
        urlTemplate: 'https://reddit.com/r/',
        keywords: ['forum', 'community', 'upvote'],
    },
    threads: {
        label: 'Threads',
        class: 'social-icons-threads',
        fa_class: 'fab fa-threads',
        color: '#000000',
        category: 'social',
        urlTemplate: 'https://threads.net/@',
        keywords: ['threads', 'meta', 'instagram'],
    },
    bluesky: {
        label: 'Bluesky',
        class: 'social-icons-bluesky',
        fa_class: 'fab fa-bluesky',
        color: '#0285ff',
        category: 'social',
        urlTemplate: 'https://bsky.app/profile/',
        keywords: ['bsky', 'sky', 'butterfly'],
    },
    pinterest: {
        label: 'Pinterest',
        class: 'social-icons-pinterest',
        fa_class: 'fab fa-pinterest-p',
        color: '#cc2127',
        category: 'social',
        urlTemplate: 'https://pinterest.com/',
        keywords: ['pin', 'board', 'ideas', 'photo'],
    },
    twitch: {
        label: 'Twitch',
        class: 'social-icons-twitch',
        fa_class: 'fab fa-twitch',
        color: '#9146ff',
        category: 'media',
        urlTemplate: 'https://twitch.tv/',
        keywords: ['stream', 'live', 'gaming'],
    },
    slack: {
        label: 'Slack',
        class: 'social-icons-slack',
        fa_class: 'fab fa-slack',
        color: '#4a154b',
        category: 'chat',
        urlTemplate: 'https://join.slack.com/',
        keywords: ['work', 'chat', 'channel'],
    },
    gitlab: {
        label: 'GitLab',
        class: 'social-icons-gitlab',
        fa_class: 'fab fa-gitlab',
        color: '#fc6d26',
        category: 'dev',
        urlTemplate: 'https://gitlab.com/',
        keywords: ['git', 'repo', 'devops'],
    },
    spotify: {
        label: 'Spotify',
        class: 'social-icons-spotify',
        fa_class: 'fab fa-spotify',
        color: '#1ed760',
        category: 'media',
        urlTemplate: 'https://open.spotify.com/',
        keywords: ['music', 'podcast', 'audio'],
    },
    medium: {
        label: 'Medium',
        class: 'social-icons-medium',
        fa_class: 'fab fa-medium',
        color: '#000000',
        category: 'social',
        urlTemplate: 'https://medium.com/@',
        keywords: ['blog', 'article', 'write'],
    },
    dribbble: {
        label: 'Dribbble',
        class: 'social-icons-dribbble',
        fa_class: 'fab fa-dribbble',
        color: '#ea4c89',
        category: 'dev',
        urlTemplate: 'https://dribbble.com/',
        keywords: ['design', 'ui', 'ux', 'art'],
    },
    behance: {
        label: 'Behance',
        class: 'social-icons-behance',
        fa_class: 'fab fa-behance',
        color: '#1769ff',
        category: 'dev',
        urlTemplate: 'https://behance.net/',
        keywords: ['design', 'portfolio', 'adobe'],
    },
    skype: {
        label: 'Skype',
        class: 'social-icons-skype',
        fa_class: 'fab fa-skype',
        color: '#00aff0',
        category: 'chat',
        urlTemplate: 'skype:',
        keywords: ['call', 'chat', 'microsoft'],
    },
    vimeo: {
        label: 'Vimeo',
        class: 'social-icons-vimeo',
        fa_class: 'fab fa-vimeo-v',
        color: '#1ab7ea',
        category: 'media',
        urlTemplate: 'https://vimeo.com/',
        keywords: ['video', 'film'],
    },
    wechat: {
        label: 'WeChat',
        class: 'social-icons-wechat',
        fa_class: 'fab fa-weixin',
        color: '#07c160',
        category: 'chat',
        urlTemplate: 'https://weixin.qq.com/',
        keywords: ['weixin', 'china', 'chat'],
    },
    mastodon: {
        label: 'Mastodon',
        class: 'social-icons-mastodon',
        fa_class: 'fab fa-mastodon',
        color: '#6364ff',
        category: 'social',
        urlTemplate: 'https://mastodon.social/@',
        keywords: ['fediverse', 'social'],
    },
    patreon: {
        label: 'Patreon',
        class: 'social-icons-patreon',
        fa_class: 'fab fa-patreon',
        color: '#ff424d',
        category: 'media',
        urlTemplate: 'https://patreon.com/',
        keywords: ['support', 'membership', 'creator'],
    },
    tumblr: {
        label: 'Tumblr',
        class: 'social-icons-tumblr',
        fa_class: 'fab fa-tumblr',
        color: '#35465c',
        category: 'social',
        urlTemplate: 'https://tumblr.com/',
        keywords: ['blog', 'post'],
    },
    vk: {
        label: 'VKontakte',
        class: 'social-icons-vk',
        fa_class: 'fab fa-vk',
        color: '#4c75a3',
        category: 'social',
        urlTemplate: 'https://vk.com/',
        keywords: ['vk', 'russia'],
    },
    xing: {
        label: 'Xing',
        class: 'social-icons-xing',
        fa_class: 'fab fa-xing',
        color: '#026466',
        category: 'dev',
        urlTemplate: 'https://xing.com/',
        keywords: ['network', 'work'],
    },
    email: {
        label: 'Email / Newsletter',
        class: 'social-icons-email',
        fa_class: 'fas fa-envelope',
        color: '#ea4335',
        category: 'contact',
        urlTemplate: 'mailto:',
        keywords: ['mail', 'newsletter', 'contact', 'envelope', 'message'],
    },
    website: {
        label: 'Website / Portfolio',
        class: 'social-icons-globe',
        fa_class: 'fas fa-globe',
        color: '#0077b6',
        category: 'contact',
        urlTemplate: 'https://',
        keywords: ['web', 'site', 'url', 'portfolio', 'globe', 'home'],
    },
    phone: {
        label: 'Phone / Support',
        class: 'social-icons-phone',
        fa_class: 'fas fa-phone',
        color: '#28a745',
        category: 'contact',
        urlTemplate: 'tel:',
        keywords: ['call', 'support', 'telephone', 'hotline'],
    },
    rss: {
        label: 'RSS Feed',
        class: 'social-icons-rss',
        fa_class: 'fas fa-rss',
        color: '#ff8201',
        category: 'contact',
        urlTemplate: 'https://',
        keywords: ['feed', 'syndication', 'blog'],
    },
}

// Shown in picker for manual icon specification
export const CUSTOM_PLATFORM = {
    id: 'custom',
    label: 'Custom Icon',
    class: 'social-icons-custom',
    fa_class: 'fas fa-icons',
    color: '#6c757d',
    category: 'custom',
    urlTemplate: '',
    keywords: ['custom', 'other', 'customizable'],
}

// Popular generic icons for quick selection in custom mode
export const COMMON_CUSTOM_ICONS = [
    { label: 'Globe', fa_class: 'fas fa-globe' },
    { label: 'Envelope', fa_class: 'fas fa-envelope' },
    { label: 'Phone', fa_class: 'fas fa-phone' },
    { label: 'Link', fa_class: 'fas fa-link' },
    { label: 'Share', fa_class: 'fas fa-share-nodes' },
    { label: 'RSS', fa_class: 'fas fa-rss' },
    { label: 'Comments', fa_class: 'fas fa-comments' },
    { label: 'Bullhorn', fa_class: 'fas fa-bullhorn' },
    { label: 'Podcast', fa_class: 'fas fa-podcast' },
    { label: 'Store', fa_class: 'fas fa-store' },
    { label: 'Heart', fa_class: 'fas fa-heart' },
    { label: 'Star', fa_class: 'fas fa-star' },
    { label: 'Location', fa_class: 'fas fa-location-dot' },
    { label: 'Calendar', fa_class: 'fas fa-calendar-days' },
    { label: 'Bookmark', fa_class: 'fas fa-bookmark' },
    { label: 'Shield', fa_class: 'fas fa-shield-halved' },
]

// Preset color palette for quick custom styling
export const PRESET_COLORS = [
    '#000000', '#1877f2', '#e4405f', '#0a66c2', '#ff0000',
    '#25d366', '#229ed9', '#5865f2', '#ff4500', '#0285ff',
    '#9146ff', '#1ed760', '#fc6d26', '#0077b6', '#ea4335',
    '#6c757d',
]

export const SOCIAL_PLATFORM_OPTIONS = [
    ...Object.entries(SOCIAL_PLATFORMS).map(([id, p]) => ({
        id,
        label: p.label,
        fa_class: p.fa_class,
        class: p.class,
        color: p.color,
        category: p.category,
        urlTemplate: p.urlTemplate,
        keywords: p.keywords,
    })),
    CUSTOM_PLATFORM,
]

// Reverse lookup so the Edit form can pre-select the right picker option
export function findPlatformByClass(cls, faCls = '') {
    if (!cls && !faCls) return 'custom'

    // Check exact match by class
    const foundByClass = Object.entries(SOCIAL_PLATFORMS).find(([, p]) => p.class === cls)
    if (foundByClass) return foundByClass[0]

    // Check by fa_class
    if (faCls) {
        const foundByFa = Object.entries(SOCIAL_PLATFORMS).find(([, p]) => p.fa_class === faCls)
        if (foundByFa) return foundByFa[0]
    }

    // Legacy / alias checks
    if (cls === 'social-icons-x' || cls === 'social-icons-twitter' || faCls === 'fab fa-twitter' || faCls === 'fab fa-x-twitter') {
        return 'x'
    }

    return 'custom'
}
