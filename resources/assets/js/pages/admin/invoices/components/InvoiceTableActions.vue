<template>
    <div class="user-table-actions">
        <router-link :to="`/invoices/${invoiceId}`" class="btn btn-default table_btn" v-tooltip="__('message.view')">
            <i class="fas fa-eye"></i>
        </router-link>
        <router-link :to="`/invoices/${invoiceId}/edit`" class="btn btn-light table_btn" v-tooltip="__('message.edit')">
            <i class="fas fa-edit"></i>
        </router-link>

        <template v-if="showDelete">
            <button class="btn btn-light table_btn" v-tooltip="__('message.Delete')" @click="showModal = true">
                <i class="fas fa-trash"></i>
            </button>
            <DeleteModal
                v-if="showModal"
                :showModal="showModal"
                :onClose="() => showModal = false"
                :deleteUrl="`${baseUrl}/invoices`"
                :deleteData="{ invoice_ids: [invoiceId] }"
                :title="__('message.confirm_delete') || 'Confirm Delete'"
                :message="__('message.are_you_sure') || 'Are you sure?'"
                componentName="user-show"
            />
        </template>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import DeleteModal from '@/themes/adminlte/components/common/DeleteModal.vue'

defineProps({
    invoiceId:  { type: [Number, String], required: true },
    showDelete: { type: Boolean, default: false },
})

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''
const showModal = ref(false)
</script>
