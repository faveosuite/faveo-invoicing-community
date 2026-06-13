<template>
    <teleport to="body">
        <div class="modal-backdrop fade show"></div>
        <div class="modal d-block" tabindex="-1" @mousedown.self="onClose">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">{{ title }}</h5>
                        <button type="button" class="btn-close" @click="onClose" />
                    </div>

                    <div class="modal-body">
                        <AppAlert componentName="delete-modal" />
                        <p class="mb-0">{{ message }}</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" :disabled="loading" @click="onClose">
                            Cancel
                        </button>
                        <button type="button" :class="`btn btn-${btnVariant}`" :disabled="loading" @click="onSubmit">
                            <spinner-loader v-if="loading" :size="18" />
                            <i v-else :class="`fas ${btnIcon} me-1`"></i>
                            {{ btnLabel }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </teleport>
</template>

<script setup>
import { ref } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const props = defineProps({
    showModal:     { type: Boolean, default: false },
    onClose:       { type: Function, required: true },
    deleteUrl:     { type: String, required: true },
    deleteData:    { type: Object, default: () => ({}) },
    title:         { type: String, default: 'Delete' },
    message:       { type: String, default: 'Are you sure you want to delete this record?' },
    componentName: { type: String, default: 'users-index' },
    btnLabel:      { type: String, default: 'Delete' },
    btnVariant:    { type: String, default: 'danger' },
    btnIcon:       { type: String, default: 'fa-trash' },
    method:        { type: String, default: 'delete' },
})

const emit = defineEmits(['deleted'])

const loading = ref(false)

async function onSubmit() {
    loading.value = true
    try {
        const res = props.method === 'post'
            ? await http.post(props.deleteUrl, props.deleteData)
            : await http.delete(props.deleteUrl, { data: props.deleteData })
        successHandler(res, props.componentName)
        emit('deleted')
        window.emitter?.emit('refreshData')
        props.onClose()
    } catch (e) {
        errorHandler(e, 'delete-modal')
    } finally {
        loading.value = false
    }
}
</script>
