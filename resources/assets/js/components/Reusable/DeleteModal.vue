<template>
    <teleport to="body">
        <div class="modal-backdrop fade show"></div>
        <div class="modal d-block" tabindex="-1" @mousedown.self="onClose">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">{{ modalTitle ? lang(modalTitle) : lang('delte') }}</h5>
                        <button type="button" class="btn-close" @click="onClose"></button>
                    </div>

                    <div class="modal-body">
                        <AppAlert componentName="delete-modal" />
                        <div v-if="loading" class="d-flex justify-content-center my-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <p v-if="!loading" class="mb-0">
                            {{ modalMessage ? lang(modalMessage) : lang('are_you_sure') }}
                        </p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="onClose" :disabled="isDisabled">
                            {{ lang('cancel') }}
                        </button>
                        <button type="button" class="btn btn-danger" @click="onSubmit()" :disabled="isDisabled">
                            <i :class="btnTitle === 'restore' ? 'fas fa-sync-alt' : 'fas fa-trash'" class="me-1"></i>
                            {{ btnTitle ? lang(btnTitle) : lang('delte') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/plugins/axios'
import { lang } from '@/helpers/extraLogics'
import { errorHandler, successHandler } from '@/helpers/responseHandler'

const props = defineProps({
    showModal:          { type: Boolean, default: false },
    deleteUrl:          { type: String },
    onClose:            { type: Function },
    alertComponentName: { type: String,           default: 'dataTableModal' },
    redirectUrl:        { type: String,           default: '' },
    modalTitle:         { type: String,           default: '' },
    modalMessage:       { type: String,           default: '' },
    btnTitle:           { type: String,           default: '' },
    componentTitle:     { type: String,           default: '' },
    keyVal:             { type: String,           default: '' },
    idVal:              { type: [String, Number], default: '' },
    softDelete:         { type: Boolean,          default: false },
})

const router = useRouter()
const loading = ref(false)
const isDisabled = ref(false)

async function onSubmit() {
    loading.value = true
    isDisabled.value = true

    const data = {}
    data[props.keyVal] = props.idVal
    if (props.softDelete) data.soft_delete = 0

    try {
        const res = await axios.post(props.deleteUrl, data)
        successHandler(res, props.alertComponentName)
        afterRespond()
    } catch (err) {
        errorHandler(err, 'delete-modal')
        loading.value = false
        isDisabled.value = false
    }
}

function afterRespond() {
    if (props.redirectUrl) {
        setTimeout(() => router.push({ path: props.redirectUrl }), 3000)
    } else {
        window.emitter.emit('refreshData')
    }
    props.onClose()
    loading.value = false
    isDisabled.value = false
}
</script>
