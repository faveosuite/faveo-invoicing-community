<template>
    <div class="d-inline-flex gap-1">
        <button
            class="btn btn-light table_btn"
            title="Restore"
            v-tooltip
            :disabled="restoring"
            @click="restore"
        >
            <span v-if="restoring" class="spinner-border spinner-border-sm"></span>
            <i v-else class="fas fa-rotate-left"></i>
        </button>

        <button
            class="btn btn-light table_btn"
            title="Delete"
            v-tooltip
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
        const res = await http.get(`${props.baseUrl}/user/restore/${props.userId}`)
        successHandler(res, props.componentName)
        emit('restored')
    } catch (e) {
        errorHandler(e, props.componentName)
    } finally {
        restoring.value = false
    }
}
</script>
