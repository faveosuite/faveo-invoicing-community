<template>
    <div>
        <div class="card">
            <div class="card-body">
                <slot />
            </div>
            <div v-if="$slots.footer" class="card-footer">
                <slot name="footer" />
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: { type: String, default: '' },
})
</script>
