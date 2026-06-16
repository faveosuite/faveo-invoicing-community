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

watch(visible, (val) => {
    clearTimeout(timer)
    if (val) {
        const duration = store.duration || 7000
        timer = setTimeout(() => store.unsetAlert(), duration)
        nextTick(() => {
            alertEl.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
        })
    }
}, { immediate: true })

onUnmounted(() => clearTimeout(timer))
</script>

<style scoped>
div[role="alert"] {
    scroll-margin-top: 70px;
}
</style>
