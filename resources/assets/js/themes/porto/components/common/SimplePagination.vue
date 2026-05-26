<template>
    <div class="simple-pagination pt-2">
        <button class="btn btn-primary" @click="$emit('paginate', 'prev')" :disabled="!prevPage">
            <i class="fas fa-arrow-left"></i> &nbsp; Previous
        </button>
        <button class="btn btn-primary" @click="$emit('paginate', 'next')" :disabled="!nextPage">
            Next &nbsp; <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</template>

<script setup>
defineProps({
    nextPage: { type: String, default: null },
    prevPage: { type: String, default: null },
})
defineEmits(['paginate'])
</script>
