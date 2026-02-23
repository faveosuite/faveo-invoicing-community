<template>
    <nav class="app-header navbar navbar-expand bg-body" role="banner">
        <div class="container-fluid">

            <!-- ── Left ───────────────────────────────────────────────────── -->
            <ul class="navbar-nav" role="navigation" aria-label="Primary navigation">

                <!-- Sidebar toggle -->
                <li class="nav-item">
                    <button type="button"
                            class="nav-link btn btn-link"
                            :aria-expanded="isOpen"
                            aria-controls="app-sidebar"
                            aria-label="Toggle sidebar"
                            @click="toggle">
                        <i class="bi bi-list" aria-hidden="true"></i>
                    </button>
                </li>

                <!-- Go to client panel -->
                <li class="nav-item d-none d-md-block">
                    <a :href="baseUrl"
                       class="nav-link"
                       target="_blank"
                       rel="noopener noreferrer"
                       aria-label="Go to client panel (opens in new tab)">
                        <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>
                        Go to Client Panel
                    </a>
                </li>
            </ul>

            <!-- ── Right ──────────────────────────────────────────────────── -->
            <ul class="navbar-nav ms-auto" role="navigation" aria-label="User navigation">

                <!-- Language -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-1"
                       data-bs-toggle="dropdown"
                       href="#"
                       role="button"
                       :aria-label="`Change language, current: ${currentLocale}`"
                       aria-haspopup="true">
                        <span :class="`fi fi-${flagCode(currentLocale)}`" :title="currentLocale.toUpperCase()"></span>
                        <span class="d-none d-md-inline">{{ currentLocale.toUpperCase() }}</span>
                    </a>
                    <ul ref="langMenu"
                        class="dropdown-menu dropdown-menu-end lang-dropdown"
                        role="listbox"
                        aria-label="Languages"
                        @scroll="onMenuScroll">
                        <li v-for="lang in languages" :key="lang.id" role="option">
                            <a class="dropdown-item d-flex align-items-center gap-2"
                               href="#"
                               :class="{ active: lang.locale === currentLocale }"
                               :aria-current="lang.locale === currentLocale ? 'true' : undefined"
                               @click.prevent="selectLang(lang)">
                                <span :class="`fi fi-${flagCode(lang.locale)}`"></span>
                                <span>{{ lang.name }}{{ nativeName(lang.locale) ? ` (${nativeName(lang.locale)})` : '' }}</span>
                            </a>
                        </li>
                        <li v-if="loadingLangs" class="d-flex justify-content-center py-2">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading…</span>
                            </div>
                        </li>
                    </ul>
                </li>

                <!-- Profile -->
                <li class="nav-item dropdown user-menu">
                    <a href="#"
                       class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                       data-bs-toggle="dropdown"
                       role="button"
                       :aria-label="`User menu for ${userName}`"
                       aria-haspopup="true">
                        <img :src="avatarUrl"
                             class="user-image rounded-circle shadow"
                             alt=""
                             @error="e => e.target.src = fallbackAvatar" />
                        <span class="d-none d-md-inline">{{ userName }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-label="User menu">
                        <!-- Header -->
                        <li class="user-header text-bg-primary">
                            <img :src="avatarUrl"
                                 class="rounded-circle shadow"
                                 :alt="`${userName} avatar`"
                                 @error="e => e.target.src = fallbackAvatar" />
                            <p>
                                {{ userName }}
                                <small v-if="userEmail">{{ userEmail }}</small>
                            </p>
                        </li>
                        <!-- Actions -->
                        <li class="user-footer">
                            <a href="#" class="btn btn-default btn-flat">
                                <i class="bi bi-person me-1" aria-hidden="true"></i>
                                Profile
                            </a>
                            <a href="#"
                               class="btn btn-default btn-flat float-end text-danger"
                               @click.prevent="logout">
                                <i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>
                                Sign out
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </nav>
</template>

<script setup>
import { ref, onMounted }    from 'vue'
import { asset }             from '@/core/utils/asset.js'
import { useSidebar }        from '@/core/composables/useSidebar.js'
import { useNotification }   from '@/core/composables/useNotification.js'
import http, { parseErrorMessage } from '@/core/services/http.js'

const { isOpen, toggle } = useSidebar()
const { notify }         = useNotification()

const el = document.getElementById('app-root')

// ── URLs ──────────────────────────────────────────────────────────────────────
const baseUrl = el?.dataset?.baseUrl ?? '/'

// ── Logout ────────────────────────────────────────────────────────────────────
async function logout() {
    await http.get('auth/logout')
    window.location.href = baseUrl + '/login'
}

// ── User ──────────────────────────────────────────────────────────────────────
const userName       = el?.dataset?.userName  ?? 'Admin'
const userEmail      = el?.dataset?.userEmail ?? ''
const avatarUrl      = el?.dataset?.userAvatar || asset('themes/adminlte/assets/img/avatar.png')
const fallbackAvatar = asset('themes/adminlte/assets/img/avatar.png')

// ── Language ──────────────────────────────────────────────────────────────────
const LIMIT = 10

// locale stored in lowercase (e.g. "en", "ar")
const currentLocale = ref((el?.dataset?.locale ?? 'en').toLowerCase())

const languages    = ref([])
const loadingLangs = ref(false)
const hasMore      = ref(true)
const page         = ref(1)
const langMenu     = ref(null)

// Maps language locale codes → ISO 3166-1 alpha-2 country codes used by flag-icons.
// Needed because locale codes identify languages (en, zh, ar) while flag-icons
// needs country codes (us, cn, sa) — they don't always match.
const LOCALE_TO_CC = {
    'en': 'us', 'en-us': 'us', 'en-gb': 'gb', 'en-uk': 'gb',
    'ar': 'sa',
    'fr': 'fr', 'de': 'de', 'es': 'es',
    // Chinese — handle hyphens, underscores and script subtags
    'zh': 'cn',
    'zh-cn': 'cn', 'zh_cn': 'cn',
    'zh-hans': 'cn', 'zh_hans': 'cn',
    'zh-tw': 'tw', 'zh_tw': 'tw',
    'zh-hant': 'tw', 'zh_hant': 'tw',
    'ja': 'jp', 'ko': 'kr',
    'pt': 'pt', 'pt-br': 'br',
    'ru': 'ru', 'it': 'it', 'nl': 'nl', 'pl': 'pl',
    'tr': 'tr', 'vi': 'vn', 'hi': 'in', 'ta': 'in',
    'he': 'il', 'id': 'id', 'ms': 'my', 'th': 'th',
    'bs': 'ba', 'no': 'no', 'nb': 'no', 'sv': 'se',
    'da': 'dk', 'fi': 'fi', 'hu': 'hu', 'cs': 'cz',
    'sk': 'sk', 'ro': 'ro', 'bg': 'bg', 'hr': 'hr',
    'sl': 'si', 'uk': 'ua', 'sr': 'rs', 'mt': 'mt',
}

function flagCode(locale) {
    // Normalise to lowercase and try the full locale first,
    // then the bare 2-letter language code as a last resort.
    const lc = locale.toLowerCase()
    return LOCALE_TO_CC[lc] ?? LOCALE_TO_CC[lc.slice(0, 2)] ?? 'un'
}

function nativeName(locale) {
    try {
        const display = new Intl.DisplayNames([locale], { type: 'language' })
        return display.of(locale) ?? ''
    } catch {
        return ''
    }
}

async function loadLanguages() {
    if (loadingLangs.value || !hasMore.value) return
    loadingLangs.value = true
    try {
        const { data } = await http.get('languages', {
            params: { 'sort-order': 'asc', limit: LIMIT, page: page.value },
        })
        const batch           = data?.data?.languages?.data ?? []
        const defaultLocale   = data?.data?.default_language
        languages.value.push(...batch)
        hasMore.value = batch.length === LIMIT
        page.value++
        // Use API default locale if blade locale not set
        if (page.value === 2 && defaultLocale && !el?.dataset?.locale) {
            currentLocale.value = defaultLocale.toLowerCase()
        }
    } catch (err) {
        console.error('Failed to load languages', err)
    } finally {
        loadingLangs.value = false
    }
}

function onMenuScroll() {
    const menu = langMenu.value
    if (!menu) return
    const nearBottom = menu.scrollTop + menu.clientHeight >= menu.scrollHeight - 60
    if (nearBottom) loadLanguages()
}

async function selectLang(lang) {
    currentLocale.value = lang.locale.toLowerCase()
    try {
        const { data } = await http.post('language-toggle', { locale: lang.locale, status: true })
        notify(data.message, 'success')
    } catch (err) {
        notify(parseErrorMessage(err), 'danger')
    }
}

onMounted(() => {
    loadLanguages()
})
</script>

<style scoped>
.lang-dropdown {
    max-height: 300px;
    overflow-y: auto;
    min-width: 260px;
}
</style>
