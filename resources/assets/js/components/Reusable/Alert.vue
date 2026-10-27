<template>
    <div v-if="visible" ref="alertEl" :class="`alert alert-${store.type} alert-dismissible d-flex align-items-center`" role="alert">
        <i v-if="store.type === 'success'" class="fas fa-circle-check me-2"></i>
        <i v-else-if="store.type === 'danger'"  class="fas fa-triangle-exclamation me-2"></i>
        <i v-else-if="store.type === 'warning'" class="fas fa-circle-exclamation me-2"></i>
        <i v-else                               class="fas fa-circle-info me-2"></i>
        <span v-html="store.message"></span> <!-- nosemgrep: javascript.vue.security.audit.xss.templates.avoid-v-html.avoid-v-html -->
        <button type="button" class="btn-close ms-auto" @click="store.unsetAlert()" />
    </div>
</template>

<script setup>
import { computed, ref, watch, nextTick, onUnmounted } from 'vue'
import { useAlertStore } from '@/core/stores/alert.js'

const props = defineProps({
    componentName: { type: String, default: '' },
})

const store = useAlertStore()
const alertEl = ref(null)

const visible = computed(() =>
    store.type !== '' && store.component_name === props.componentName
)

let timer = null

// Scroll only when the alert is off screen, and only as far as needed — an alert already
// in view shouldn't make the page jump.
function revealAlert() {
    const el = alertEl.value
    if (!el) return
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
    if (store.component_name === props.componentName) store.unsetAlert()
})
</script>

<style scoped>
div[role="alert"] {
    scroll-margin-top: 70px;
}
</style>
