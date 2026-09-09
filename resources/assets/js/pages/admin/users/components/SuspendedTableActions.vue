<template>
    <div class="user-table-actions">
        <button
            class="btn btn-light table_btn"
            v-tooltip="__('message.restore')"
            :disabled="restoring"
            @click="restore"
        >
            <spinner-loader v-if="restoring" :size="18" />
            <i v-else class="fas fa-rotate-left"></i>
        </button>

        <button
            class="btn btn-light table_btn"
            v-tooltip="__('message.Delete')"
            @click="showModal = true"
        >
            <i class="fas fa-trash"></i>
        </button>

        <DeleteModal
            v-if="showModal"
            :showModal="showModal"
            :onClose="() => showModal = false"
            :deleteUrl="`${baseUrl}/permanent-delete-client`"
            :deleteData="{ user_ids: [userId] }"
            :componentName="componentName"
            @deleted="emit('deleted')"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const props = defineProps({
    userId:        { type: Number, required: true },
    baseUrl:       { type: String, default: '' },
    componentName: { type: String, default: 'suspended-index' },
})

const emit = defineEmits(['restored', 'deleted'])

const restoring = ref(false)
const showModal = ref(false)

async function restore() {
    restoring.value = true
    try {
        const res = await http.post(`${props.baseUrl}/user/restore/${props.userId}`)
        successHandler(res, props.componentName)
        emit('restored')
    } catch (e) {
        errorHandler(e, props.componentName)
    } finally {
        restoring.value = false
    }
}
</script>
