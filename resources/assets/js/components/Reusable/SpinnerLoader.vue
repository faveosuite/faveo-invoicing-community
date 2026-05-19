<template>
    <div
        class="spinner-loader-wrapper"
        :style="{ width: size + 'px', height: size + 'px', '--spinner-color': color, '--spinner-duration': duration + 'ms' }"
    >
        <div class="fulfilling-bouncing-circle-spinner">
            <div class="circle"></div>
            <div class="orbit"></div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    duration: { type: Number, default: 4000 },
    size:     { type: Number, default: 36 },
    color:    { type: String, default: '#1d78ff' },
})
</script>

<style scoped>
.spinner-loader-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.fulfilling-bouncing-circle-spinner {
    width: 100%;
    height: 100%;
    position: relative;
    animation: fbcs-rotate var(--spinner-duration, 4000ms) infinite ease;
}

.fulfilling-bouncing-circle-spinner .circle {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: calc(var(--spinner-size, 36px) * 0.1) solid var(--spinner-color, #1d78ff);
    animation: fbcs-circle var(--spinner-duration, 4000ms) infinite ease;
}

.fulfilling-bouncing-circle-spinner .orbit {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: calc(var(--spinner-size, 36px) * 0.03) solid var(--spinner-color, #1d78ff);
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
