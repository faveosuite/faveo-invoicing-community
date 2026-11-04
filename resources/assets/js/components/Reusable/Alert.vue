<template>
    <!-- app-alert hooks: the client panel (client.css) turns this into a floating toast; admin keeps the inline box. -->
    <div v-if="visible" ref="alertEl" :class="`app-alert app-alert-${store.type} alert alert-${store.type} alert-dismissible d-flex align-items-center`" role="alert">
        <i v-if="store.type === 'success'" class="app-alert-icon fas fa-circle-check me-2" aria-hidden="true"></i>
        <i v-else-if="store.type === 'danger'"  class="app-alert-icon fas fa-triangle-exclamation me-2" aria-hidden="true"></i>
        <i v-else-if="store.type === 'warning'" class="app-alert-icon fas fa-circle-exclamation me-2" aria-hidden="true"></i>
        <i v-else                               class="app-alert-icon fas fa-circle-info me-2" aria-hidden="true"></i>
        <span class="app-alert-message" v-html="store.message"></span> <!-- nosemgrep: javascript.vue.security.audit.xss.templates.avoid-v-html.avoid-v-html -->
        <button type="button" class="btn-close ms-auto" :aria-label="__('message.close')" @click="store.unsetAlert()" />
    </div>
</template>

<script>
import { reactive } from 'vue'

// Shared by every <AppAlert> (plain <script> runs once, <script setup> runs per instance).
// Several with the same name can be mounted at once (a page and a modal on it): only the
// newest one shows, so one alert never renders twice. Reactive, so the previous one takes
// over again when the newer one unmounts.
let uidCounter = 0
const mounted = reactive([]) // { uid, name } in mount order
const nextUid = () => ++uidCounter
</script>

<script setup>
import { computed, ref, watch, nextTick, onUnmounted } from 'vue'
import { useAlertStore } from '@/core/stores/alert.js'
import { __ } from '@/plugins/i18n'

const props = defineProps({
    componentName: { type: String, default: '' },
})

const store = useAlertStore()
const alertEl = ref(null)

const uid = nextUid()
mounted.push({ uid, name: props.componentName })
const newestUid = computed(() => mounted.findLast(m => m.name === props.componentName)?.uid)

// An empty message would show just a coloured box with a close button — skip it.
const visible = computed(() =>
    store.type !== '' && store.component_name === props.componentName
    && String(store.message ?? '').trim() !== '' && newestUid.value === uid
)

let timer = null

// Scroll only when the alert is off screen, and only as far as needed — an alert already
// in view shouldn't make the page jump.
function revealAlert() {
    const el = alertEl.value
    if (!el || getComputedStyle(el).position === 'fixed') return // a floating toast is always in view
    const { top, bottom } = el.getBoundingClientRect()
    if (top >= 70 && bottom <= window.innerHeight) return // 70 = scroll-margin-top (fixed header)
    const reduce = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches
    el.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'nearest' })
}

// Keyed on the message too, so a new alert replacing a shown one restarts its timer and is revealed.
watch(() => visible.value && store.message, (val) => {
    clearTimeout(timer)
    if (val) {
        const duration = store.duration || 7000
        timer = setTimeout(() => store.unsetAlert(), duration)
        nextTick(revealAlert)
    }
}, { immediate: true })

// Clear the alert when its own page goes away — but only if it still owns
// it. A page that sets an alert then navigates elsewhere (e.g. "saved" on
// the index after a create/edit redirect) reassigns component_name to the
// destination first, so this guard leaves that handoff alone.
onUnmounted(() => {
    clearTimeout(timer)
    const i = mounted.findIndex(m => m.uid === uid)
    if (i > -1) mounted.splice(i, 1)
    if (store.component_name === props.componentName) store.unsetAlert()
})
</script>

<style scoped>
div[role="alert"] {
    scroll-margin-top: 70px;
}
</style>
