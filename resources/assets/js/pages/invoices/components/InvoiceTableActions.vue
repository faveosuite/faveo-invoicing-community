<template>
    <div class="user-table-actions">
        <router-link :to="`/invoices/${invoiceId}`" class="btn btn-default table_btn" :title="__('message.view')" v-tooltip>
            <i class="fas fa-eye"></i>
        </router-link>
        <router-link :to="`/invoices/${invoiceId}/edit`" class="btn btn-light table_btn" :title="__('message.edit')" v-tooltip>
            <i class="fas fa-edit"></i>
        </router-link>
    </div>
</template>

<script setup>
const props = defineProps({
    invoiceId: { type: [Number, String], required: true },
})
</script>
