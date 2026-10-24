<template>
    <div class="user-table-actions">
        <router-link
            :to="`/orders/${orderId}`"
            class="btn btn-light table_btn"
            v-tooltip="__('message.view')"
        >
            <i class="fas fa-eye"></i>
        </router-link>

        <template v-if="canRenew">
            <!-- Blocked (deleted instance / unpaid invoice): keep it visible and say why.
                 A disabled button fires no hover events, so the tooltip sits on a wrapper. -->
            <span v-if="renewBlockedReason" class="d-inline-block" tabindex="0" v-tooltip="renewBlockedReason">
                <button type="button" class="btn btn-light table_btn" disabled :aria-label="renewBlockedReason">
                    <i class="fas fa-sync-alt" aria-hidden="true"></i>
                </button>
            </span>
            <router-link
                v-else
                :to="`/orders/${orderId}/renew`"
                class="btn btn-light table_btn"
                v-tooltip="__('message.renew')"
            >
                <i class="fas fa-sync-alt"></i>
            </router-link>
        </template>

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
                :message="__('message.order_delete')"
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
    renewBlockedReason: { type: String, default: '' },
    baseUrl:       { type: String, default: '' },
    showDelete:    { type: Boolean, default: false },
    componentName: { type: String, default: 'orders-index' },
})

const resolvedBaseUrl = props.baseUrl || useBaseUrl()
const showModal = ref(false)
</script>
