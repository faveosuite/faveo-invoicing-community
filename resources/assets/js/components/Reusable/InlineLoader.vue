<template>
    <div
        class="inline-loader-container"
        :style="{ minHeight: minHeight !== null ? minHeight + 'px' : '50vh' }"
    >
        <div class="inline-loader-overlay" />
        <div class="inline-loader-center">
            <div
                class="fulfilling-bouncing-circle-spinner"
                :style="{ width: size + 'px', height: size + 'px', '--spinner-color': color, '--spinner-duration': duration + 'ms' }"
            >
                <div class="circle"></div>
                <div class="orbit"></div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    duration:  { type: Number, default: 4000 },
    size:      { type: Number, default: 60 },
    color:     { type: String, default: '#1d78ff' },
    minHeight: { type: Number, default: null },
})
</script>

<style scoped>
.inline-loader-container {
    position: relative;
    width: 100%;
    /* min-height is driven by the inline style binding */
    display: flex;
    align-items: center;
    justify-content: center;
}

.inline-loader-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.8);
    filter: blur(2px);
    border-radius: inherit;
}

.inline-loader-center {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* FulfillingBouncingCircleSpinner */
.fulfilling-bouncing-circle-spinner {
    position: relative;
    animation: fbcs-rotate var(--spinner-duration, 4000ms) infinite ease;
}

.fulfilling-bouncing-circle-spinner .circle {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: calc(60px * 0.1) solid var(--spinner-color, #1d78ff);
    animation: fbcs-circle var(--spinner-duration, 4000ms) infinite ease;
}

.fulfilling-bouncing-circle-spinner .orbit {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: calc(60px * 0.03) solid var(--spinner-color, #1d78ff);
    animation: fbcs-orbit var(--spinner-duration, 4000ms) infinite ease;
}

@keyframes fbcs-rotate {
    0%   { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@keyframes fbcs-orbit {
    0%     { transform: scale(1); }
    62.5%  { transform: scale(0.8); }
    75%    { transform: scale(1); }
    87.5%  { transform: scale(0.8); }
    100%   { transform: scale(1); }
}

@keyframes fbcs-circle {
    0%     { border-top-color: transparent; border-bottom-color: transparent; border-right-color: transparent; border-left-color: var(--spinner-color, #1d78ff); transform: rotate(0deg); }
    16.7%  { border-top-color: transparent; border-bottom-color: transparent; border-right-color: var(--spinner-color, #1d78ff); border-left-color: var(--spinner-color, #1d78ff); transform: rotate(0deg); }
    33.4%  { border-top-color: var(--spinner-color, #1d78ff); border-bottom-color: transparent; border-right-color: var(--spinner-color, #1d78ff); border-left-color: var(--spinner-color, #1d78ff); transform: rotate(0deg); }
    50%    { border-color: var(--spinner-color, #1d78ff); transform: rotate(0deg); }
    62.5%  { border-color: var(--spinner-color, #1d78ff); transform: rotate(90deg); }
    75%    { border-color: var(--spinner-color, #1d78ff); transform: rotate(180deg); }
    87.5%  { border-color: var(--spinner-color, #1d78ff); transform: rotate(270deg); }
    100%   { border-top-color: transparent; border-bottom-color: transparent; border-right-color: transparent; border-left-color: var(--spinner-color, #1d78ff); transform: rotate(360deg); }
}
</style>
