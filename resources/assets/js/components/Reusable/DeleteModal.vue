<template>
    <teleport to="body">
        <!-- v-if here (not just relying on the caller mounting/unmounting the
             whole component) matters for callers that keep DeleteModal always
             mounted and toggle `showModal` instead of v-if'ing the component
             itself - see ProductEdit.vue's versions-tab delete modal. -->
        <template v-if="showModal">
            <div class="modal-backdrop fade show"></div>
            <div class="modal d-block" tabindex="-1" @mousedown.self="onClose">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">{{ title }}</h5>
                            <button type="button" class="btn-close" @click="onClose" />
                        </div>

                        <div class="modal-body">
                            <AppAlert :componentName="modalAlertName" />
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
        </template>
    </teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
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

// Callers pass their own page's AppAlert name as componentName, expecting
// the *success* message to surface there once the modal closes. Showing
// errors under that same name would make them appear twice at once — once
// here (this modal has its own AppAlert so a failure is visible without
// having to close the modal first) and once behind it on the page, since
// both AppAlerts watch the same store entry. A distinct name keeps errors
// modal-only, while success still goes to the page as before.
const modalAlertName = computed(() => `${props.componentName}-delete-modal`)

async function onSubmit() {
    loading.value = true
    try {
        const res = props.method === 'post'
            ? await http.post(props.deleteUrl, props.deleteData)
            : await http.delete(props.deleteUrl, { data: props.deleteData })
        successHandler(res, props.componentName)
        emit('deleted')
        props.onClose()
        // Best-effort: tells every mounted DataTable to refresh (a page can
        // have more than one, e.g. tabs). Must run after onClose — a
        // throwing listener here must never be able to keep the modal open.
        try { globalThis.emitter?.emit('refreshData') } catch { /* non-fatal */ }
    } catch (e) {
        errorHandler(e, modalAlertName.value)
    } finally {
        loading.value = false
    }
}
</script>
