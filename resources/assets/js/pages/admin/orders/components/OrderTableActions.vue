<template>
    <div class="user-table-actions">
        <router-link
            :to="`/orders/${orderId}`"
            class="btn btn-default table_btn"
            :title="__('message.view')"
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
