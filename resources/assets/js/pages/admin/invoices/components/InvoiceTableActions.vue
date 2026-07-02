<template>
    <div class="user-table-actions">
        <router-link :to="`/invoices/${invoiceId}`" class="btn btn-default table_btn" v-tooltip="__('message.view')">
            <i class="fas fa-eye"></i>
        </router-link>

        <button
            v-if="!isExecuted"
            class="btn btn-light table_btn"
            v-tooltip="__('message.order_execute')"
            @click="execute"
        >
            <i class="fas fa-play"></i>
        </button>

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
                :componentName="componentName"
            />
        </template>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import http from '@/plugins/axios'
import { errorHandler, successHandler } from '@/helpers/responseHandler.js'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const props = defineProps({
    invoiceId:     { type: [Number, String], required: true },
    isExecuted:    { type: Boolean, default: false },
    showDelete:    { type: Boolean, default: false },
    componentName: { type: String, default: 'invoices-index' },
})

const baseUrl = useBaseUrl()
const showModal = ref(false)

async function execute() {
    try {
        const res = await http.post(`/invoices/${props.invoiceId}/execute`)
        successHandler(res, props.componentName)
    } catch (e) {
        errorHandler(e, props.componentName)
    }
}
</script>
