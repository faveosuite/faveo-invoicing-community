<template>
    <div class="user-table-actions">
        <router-link
            :to="`/orders/${orderId}`"
            class="btn btn-light table_btn"
            v-tooltip="__('message.view')"
        >
            <i class="fas fa-eye"></i>
        </router-link>

        <router-link
            v-if="canRenew"
            :to="`/orders/${orderId}/renew`"
            class="btn btn-light table_btn"
            v-tooltip="__('message.renew')"
        >
            <i class="fas fa-sync-alt"></i>
        </router-link>

        <template v-if="showDelete">
            <button class="btn btn-light table_btn" v-tooltip="__('message.Delete')" @click="showModal = true">
                <i class="fas fa-trash"></i>
            </button>
            <DeleteModal
                v-if="showModal"
                :showModal="showModal"
                :onClose="() => showModal = false"
                :deleteUrl="`${resolvedBaseUrl}/orders`"
                :deleteData="{ order_ids: [orderId] }"
                :title="__('message.confirm_delete') || 'Confirm Delete'"
                :message="__('message.are_you_sure') || 'Are you sure?'"
                :componentName="componentName"
            />
        </template>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const props = defineProps({
    orderId:       { type: Number, required: true },
    canRenew:      { type: Boolean, default: false },
    baseUrl:       { type: String, default: '' },
    showDelete:    { type: Boolean, default: false },
    componentName: { type: String, default: 'orders-index' },
})

const resolvedBaseUrl = props.baseUrl || useBaseUrl()
const showModal = ref(false)
</script>
