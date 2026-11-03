import { computed, ref } from 'vue'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { useAlertStore } from '@/core/stores/alert'

// Maps language locale codes → ISO 3166-1 alpha-2 country codes used by flag-icons.
const localeMap = {
  ar: 'sa', bsn: 'ba', bs: 'ba', de: 'de', en: 'us', 'en-gb': 'gb', 'en-us': 'us', es: 'es',
  fr: 'fr', id: 'id', it: 'it', ko: 'kr', kr: 'kr', mt: 'mt', nl: 'nl', no: 'no',
  pt: 'pt', 'pt-br': 'br', ru: 'ru', vi: 'vn', zh: 'cn', 'zh-hans': 'cn', 'zh-hant': 'tw', 'zh-cn': 'cn', 'zh-tw': 'tw',
  ja: 'jp', ta: 'in', hi: 'in', he: 'il', tr: 'tr', pl: 'pl', sv: 'se', da: 'dk', cs: 'cz', uk: 'ua',
}

// Switching language reloads the page; this keeps what the user typed (text fields only —
// never passwords, hidden or honeypot-empty fields) for the same page, once.
const STASH_KEY = 'lang-switch-form'
const SKIP_TYPES = ['password', 'hidden', 'checkbox', 'radio', 'file', 'submit', 'button']

function stashForm() {
  try {
    const fields = [...document.querySelectorAll('#main-content input[name], #main-content textarea[name]')]
      .filter(f => !SKIP_TYPES.includes(f.type) && f.value)
    if (fields.length) {
      sessionStorage.setItem(STASH_KEY, JSON.stringify({
        path: location.pathname,
        values: Object.fromEntries(fields.map(f => [f.name, f.value])),
      }))
    }
  } catch { /* storage unavailable: just lose the input */ }
}

// Puts stashed values back (fires `input` so v-model picks them up). Call after the form mounts.
export function restoreStashedForm() {
  try {
    const saved = JSON.parse(sessionStorage.getItem(STASH_KEY) || 'null')
    sessionStorage.removeItem(STASH_KEY)
    if (!saved || saved.path !== location.pathname) return
    const fields = [...document.querySelectorAll('#main-content input[name], #main-content textarea[name]')]
    for (const [name, value] of Object.entries(saved.values)) {
      const el = fields.find(f => f.name === name && !SKIP_TYPES.includes(f.type))
      if (el && !el.value) {
        el.value = value
        el.dispatchEvent(new Event('input', { bubbles: true }))
      }
    }
  } catch { /* nothing to restore */ }
}

export function flagCodeFor(loc) {
  const lc = String(loc ?? '').toLowerCase()
  return localeMap[lc] ?? localeMap[lc.slice(0, 2)] ?? 'un'
}

// Language picker state for the client shell (navbar + auth layout).
export function useLanguages() {
  const el = document.getElementById('app-client')
  const alertStore = useAlertStore()

  const currentLocale = computed(() => (el?.dataset?.locale ?? 'en').toLowerCase())
  const languages = ref(
    (JSON.parse(el?.dataset?.languages ?? '[]')).filter(l => Number(l.status) === 1)
  )
  const isCurrent = (lang) => lang.locale.toLowerCase() === currentLocale.value

  async function selectLang(lang) {
    try {
      stashForm()
      await http.post('lang/update', { language: lang.locale })
      globalThis.location.reload()
    } catch (e) {
      console.error('Language switch failed', e)
      alertStore.setAlert({ message: __('message.error_occurred'), type: 'danger', component_name: 'client-page' })
    }
  }

  return { currentLocale, languages, isCurrent, selectLang, flagCodeFor }
}
