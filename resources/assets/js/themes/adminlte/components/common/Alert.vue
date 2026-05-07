<template>
    <div v-if="visible" :class="`alert alert-${store.type} alert-dismissible d-flex align-items-center`" role="alert">
        <i v-if="store.type === 'success'" class="fas fa-circle-check me-2"></i>
        <i v-else-if="store.type === 'danger'"  class="fas fa-triangle-exclamation me-2"></i>
        <i v-else-if="store.type === 'warning'" class="fas fa-circle-exclamation me-2"></i>
        <i v-else                               class="fas fa-circle-info me-2"></i>
        <span>{{ store.message }}</span>
        <button type="button" class="btn-close ms-auto" @click="store.unsetAlert()" />
    </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useAlertStore } from '@/core/stores/alert.js'

const props = defineProps({
    componentName: { type: String, default: '' },
})

const store = useAlertStore()

const visible = computed(() =>
    store.type !== '' && store.component_name === props.componentName
)

let timer = null

watch(visible, (val) => {
    clearTimeout(timer)
    if (val) {
        timer = setTimeout(() => store.unsetAlert(), 7000)
    }
})
</script>
