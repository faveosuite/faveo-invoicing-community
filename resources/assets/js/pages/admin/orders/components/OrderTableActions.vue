<template>
    <div class="user-table-actions">
        <router-link
            :to="`/orders/${orderId}`"
            class="btn btn-default table_btn"
            v-tooltip="__('message.view')"
        >
            <i class="fas fa-eye"></i>
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
                componentName="user-show"
            />
        </template>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import DeleteModal from '@/themes/adminlte/components/common/DeleteModal.vue'

const props = defineProps({
    orderId:    { type: Number, required: true },
    baseUrl:    { type: String, default: '' },
    showDelete: { type: Boolean, default: false },
})

const resolvedBaseUrl = props.baseUrl || document.getElementById('app-root')?.dataset?.baseUrl || ''
const showModal = ref(false)
</script>
