<template>
    <div class="p-0 d-flex justify-content-center">
        <FulfillingBouncingCircleSpinner :animationDuration="duration" :size="size" :color="color" />
    </div>
</template>

<script setup>
import { FulfillingBouncingCircleSpinner } from 'epic-spinners'

defineProps({
    duration: { type: Number, default: 4000 },
    size:     { type: Number, default: 60 },
    color:    { type: String, default: '#1d78ff' },
})
</script>
