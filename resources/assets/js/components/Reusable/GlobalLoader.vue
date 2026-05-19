<template>
    <Teleport to="body">
        <Transition name="loader-fade">
            <div v-if="loaderStore.showLoader" class="global-loader-overlay">
                <div class="global-loader-center">
                    <div
                        class="fulfilling-bouncing-circle-spinner"
                        :style="{ width: size + 'px', height: size + 'px', '--spinner-color': color, '--spinner-duration': duration + 'ms' }"
                    >
                        <div class="circle"></div>
                        <div class="orbit"></div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
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

/* FulfillingBouncingCircleSpinner — matches epic-spinners */
.fulfilling-bouncing-circle-spinner {
    position: relative;
    animation: fbcs-rotate var(--spinner-duration, 4000ms) infinite ease;
}

.fulfilling-bouncing-circle-spinner .circle {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: calc(var(--size, 60px) * 0.1) solid var(--spinner-color, #1d78ff);
    animation: fbcs-circle var(--spinner-duration, 4000ms) infinite ease;
}

.fulfilling-bouncing-circle-spinner .orbit {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: calc(var(--size, 60px) * 0.03) solid var(--spinner-color, #1d78ff);
    animation: fbcs-orbit var(--spinner-duration, 4000ms) infinite ease;
}

@keyframes fbcs-rotate {
    0%   { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes fbcs-orbit {
    0%     { transform: scale(1); }
    50%    { transform: scale(1); }
    62.5%  { transform: scale(0.8); }
    75%    { transform: scale(1); }
    87.5%  { transform: scale(0.8); }
    100%   { transform: scale(1); }
}

@keyframes fbcs-circle {
    0%     { border-top-color: transparent; border-bottom-color: transparent; border-right-color: transparent; border-left-color: var(--spinner-color, #1d78ff); transform: rotate(0deg); }
    16.7%  { border-top-color: transparent; border-bottom-color: transparent; border-right-color: var(--spinner-color, #1d78ff); border-left-color: var(--spinner-color, #1d78ff); transform: rotate(0deg); }
    33.4%  { border-top-color: var(--spinner-color, #1d78ff); border-bottom-color: transparent; border-right-color: var(--spinner-color, #1d78ff); border-left-color: var(--spinner-color, #1d78ff); transform: rotate(0deg); }
    50%    { border-top-color: var(--spinner-color, #1d78ff); border-bottom-color: var(--spinner-color, #1d78ff); border-right-color: var(--spinner-color, #1d78ff); border-left-color: var(--spinner-color, #1d78ff); transform: rotate(0deg); }
    62.5%  { transform: rotate(90deg); }
    75%    { transform: rotate(180deg); }
    87.5%  { transform: rotate(270deg); }
    100%   { border-top-color: transparent; border-bottom-color: transparent; border-right-color: transparent; border-left-color: var(--spinner-color, #1d78ff); transform: rotate(360deg); }
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
