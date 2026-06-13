<template>
    <Teleport to="body">
        <Transition name="loader-fade">
            <div v-if="loaderStore.showLoader" class="global-loader-overlay">
                <FulfillingBouncingCircleSpinner :animationDuration="duration" :size="size" :color="color" />
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { FulfillingBouncingCircleSpinner } from 'epic-spinners'
import { useLoaderStore } from '@/core/stores/loader.js'

const loaderStore = useLoaderStore()

defineProps({
    duration: { type: Number, default: 4000 },
    size:     { type: Number, default: 60 },
    color:    { type: String, default: '#1d78ff' },
})
</script>

<style scoped>
.global-loader-overlay {
    position: fixed;
    inset: 0;
    background: rgba(255, 255, 255, 0.55);
    backdrop-filter: blur(2px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.loader-fade-enter-active,
.loader-fade-leave-active {
    transition: opacity 0.25s ease;
}

.loader-fade-enter-from,
.loader-fade-leave-to {
    opacity: 0;
}
</style>
