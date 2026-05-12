<template>
    <div class="user-table-actions">
        <router-link :to="`/invoices/${invoiceId}`" class="btn btn-default table_btn" title="View" v-tooltip>
            <i class="fas fa-eye"></i>
        </router-link>
        <router-link :to="`/invoices/${invoiceId}/edit`" class="btn btn-light table_btn" title="Edit" v-tooltip>
            <i class="fas fa-edit"></i>
        </router-link>
    </div>
</template>

<script setup>
const props = defineProps({
    invoiceId: { type: [Number, String], required: true },
})
</script>
