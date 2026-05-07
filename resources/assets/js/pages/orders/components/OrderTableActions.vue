<template>
    <div class="d-inline-flex gap-1">
        <router-link
            :to="`/orders/${orderId}`"
            class="btn btn-light table_btn"
            title="View"
            v-tooltip
        >
            <i class="fas fa-eye"></i>
        </router-link>
    </div>
</template>

<script setup>
defineProps({
    orderId: { type: Number, required: true },
    baseUrl: { type: String, default: '' },
})
</script>
