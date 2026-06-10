<template>
    <div class="user-table-actions">
        <!-- Edit shows only for credit-balance payments (not linked to an invoice) -->
        <router-link
            v-if="!invoiceId"
            :to="`/users/${userId}/payments/${paymentId}/edit`"
            class="btn btn-light table_btn"
            :title="__('message.edit')"
            v-tooltip
        >
            <i class="fas fa-edit"></i>
        </router-link>

        <!-- Delete is available on every payment row -->
        <button
            class="btn btn-light table_btn"
            :title="__('message.Delete')"
            v-tooltip
            @click="showModal = true"
        >
            <i class="fas fa-trash"></i>
        </button>

        <DeleteModal
            v-if="showModal"
            :showModal="showModal"
            :onClose="() => showModal = false"
            :deleteUrl="`${baseUrl}/payments`"
            :deleteData="{ payment_ids: [paymentId] }"
            :title="__('message.confirm_delete') || 'Confirm Delete'"
            :message="__('message.are_you_sure') || 'Are you sure?'"
            componentName="user-show"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import DeleteModal from '@/themes/adminlte/components/common/DeleteModal.vue'

defineProps({
    paymentId: { type: [Number, String], required: true },
    invoiceId: { type: [Number, String], default: 0 },
    userId:    { type: [Number, String], required: true },
    baseUrl:   { type: String, default: '' },
})

const showModal = ref(false)
</script>
