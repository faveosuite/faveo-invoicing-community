<template>
    <div v-if="totalPages > 1" class="simple-pagination pt-2">
        <button class="btn btn-primary" @click="go(currentPage - 1)" :disabled="currentPage <= 1">
            <i class="fas fa-arrow-left"></i> &nbsp; Previous
        </button>
        <button class="btn btn-primary" @click="go(currentPage + 1)" :disabled="currentPage >= totalPages">
            Next &nbsp; <i class="fas fa-arrow-right"></i>
        </button>
    </div>
</template>

<script setup>
const props = defineProps({
    currentPage: { type: Number, required: true },
    totalPages:  { type: Number, required: true },
})
const emit = defineEmits(['change'])

function go(page) {
    if (page < 1 || page > props.totalPages || page === props.currentPage) return
    emit('change', page)
}
</script>
