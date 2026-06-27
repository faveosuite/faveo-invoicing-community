<template>

  <nav class="app-header navbar navbar-expand bg-body" role="banner">

    <div class="container-fluid">

      <!-- ── Left ───────────────────────────────────────────────────── -->
      <ul class="navbar-nav" role="listbox" aria-label="Primary navigation">

        <!-- Sidebar toggle -->
        <li class="nav-item">
          <button type="button" class="nav-link btn btn-link" :aria-expanded="isOpen"
                  aria-controls="app-sidebar" aria-label="Toggle sidebar" @click="toggle">
            <i class="fas fa-bars" aria-hidden="true"></i>
          </button>
        </li>

        <!-- Go to client panel -->
        <li class="nav-item d-none d-md-block">
          <a :href="`${baseUrl}/client-dashboard`" class="nav-link" aria-label="Go to client panel">
            <i class="fas fa-arrow-up-right-from-square me-1" aria-hidden="true"></i>
            {{ __('message.go_to_client') }}
          </a>
        </li>
      </ul>

      <!-- ── Right ──────────────────────────────────────────────────── -->
      <ul class="navbar-nav ms-auto" role="listbox" aria-label="User navigation">

        <!-- Language -->
        <li class="nav-item dropdown">

          <a class="nav-link dropdown-toggle d-flex align-items-center gap-1" data-bs-toggle="dropdown"
             href="javascript:;" role="button" :aria-label="`Change language, current: ${currentLocale}`"
             aria-haspopup="true">
            <span :class="`fi fi-${flagCode(currentLocale)}`" v-tooltip="currentLocale.toUpperCase()"></span>
            <span class="d-none d-md-inline">{{ currentLocale.toUpperCase() }}</span>
          </a>

          <ul ref="langMenu" class="dropdown-menu dropdown-menu-end lang-dropdown" role="listbox" aria-label="Languages"
              @scroll="onMenuScroll">

            <li v-for="lang in languages" :key="lang.id" role="option">
              <a class="dropdown-item d-flex align-items-center gap-2" href="javascript:;"
                 :class="{ active: lang.locale === currentLocale }"
                 :aria-current="lang.locale === currentLocale ? 'true' : undefined"
                 @click.prevent="selectLang(lang)">
                <span :class="`fi fi-${flagCode(lang.locale)}`"></span>
                <span>{{ lang.name }}{{ nativeName(lang.locale) ? ` (${nativeName(lang.locale)})` : '' }}</span>
              </a>
            </li>

            <li v-if="loadingLangs" class="d-flex justify-content-center py-2">
              <spinner-loader :size="18"/>
            </li>
          </ul>
        </li>

        <!-- Profile -->
        <li class="nav-item dropdown">

          <a href="javascript:;" class="nav-link d-flex align-items-center gap-2" data-bs-toggle="dropdown"
             data-bs-offset="0,8" role="button" :aria-label="`User menu for ${userName}`" aria-haspopup="true">

            <span class="d-none d-md-inline">{{ userName }}</span>

            <img :src="avatarUrl" class="user-image rounded-circle" alt="Avatar"
                 @error="e => e.target.src = fallbackAvatar"/>
          </a>

          <ul class="dropdown-menu dropdown-menu-end user-dropdown" aria-label="User menu">

            <li>
              <RouterLink to="/profile" class="dropdown-item">
                <i class="far fa-user me-2" aria-hidden="true"></i>{{ __('message.profile') }}
              </RouterLink>
            </li>

            <li>
              <a href="javascript:;" class="dropdown-item" @click.prevent="logout">
                <i class="fas fa-right-from-bracket me-2" aria-hidden="true"></i>{{ __('message.log_out') }}
              </a>
            </li>
          </ul>
        </li>

      </ul>
    </div>
  </nav>
</template>

<script setup>
import {ref, onMounted} from 'vue'
import {asset} from '@/core/utils/asset.js'
import {useSidebar} from '@/core/composables/useSidebar.js'
import {useNotification} from '@/core/composables/useNotification.js'
import http, {parseErrorMessage} from '@/plugins/axios.js'

const {isOpen, toggle} = useSidebar()
const {notify} = useNotification()

const el = document.getElementById('app-root')

// ── URLs ──────────────────────────────────────────────────────────────────────
const baseUrl = el?.dataset?.baseUrl ?? '/'

// ── Logout ────────────────────────────────────────────────────────────────────
async function logout() {
  await http.get('auth/logout')
  window.location.href = baseUrl + '/login'
}

// ── User ──────────────────────────────────────────────────────────────────────
const userName = el?.dataset?.userName ?? 'Admin';
const avatarUrl = el?.dataset?.userAvatar || asset('themes/adminlte/assets/img/avatar.png')
const fallbackAvatar = asset('themes/adminlte/assets/img/avatar.png')

// ── Language ──────────────────────────────────────────────────────────────────
const LIMIT = 10

// locale stored in lowercase (e.g. "en", "ar")
const currentLocale = ref((el?.dataset?.locale ?? 'en').toLowerCase())

const languages = ref([])
const loadingLangs = ref(false)
const hasMore = ref(true)
const page = ref(1)
const langMenu = ref(null)

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
    const display = new Intl.DisplayNames([locale], {type: 'language'})
    return display.of(locale) ?? ''
  } catch {
    return ''
  }
}

async function loadLanguages() {
  if (loadingLangs.value || !hasMore.value) return
  loadingLangs.value = true
  try {
    const {data} = await http.get('languages', {
      params: {'sort-order': 'asc', limit: LIMIT, page: page.value},
    })
    const batch = data?.data?.data ?? []
    const defaultLocale = batch.find(l => l.is_default)?.locale
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
  try {
    await http.post('lang/update', {language: lang.locale})
    window.location.reload()
  } catch (err) {
    notify(parseErrorMessage(err), 'danger')
  }
}

onMounted(() => {
  loadLanguages()
})
</script>

<style scoped>
.nav-link.btn-link {
  color: inherit;
}

.lang-dropdown {
  max-height: 300px;
  overflow-y: auto;
  min-width: 260px;
}

@media (max-width: 576px) {
  .lang-dropdown {
    position: fixed !important;
    top: 57px !important;
    left: 10px !important;
    right: 10px !important;
    width: auto !important;
    min-width: unset !important;
    transform: none !important;
  }
}
</style>
