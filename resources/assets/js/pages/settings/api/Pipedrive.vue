<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.pipedrive') }}</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.status') }}</label>
                            <div class="form-check form-switch mt-2">
                                <input id="pipedriveStatus" class="form-check-input" type="checkbox" v-model="form.status" />
                                <label class="form-check-label" for="pipedriveStatus">
                                    {{ form.status ? __('message.enable') : __('message.disable') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label fw-bold">{{ __('message.pipedrive') }} API Key</label>
                            <input class="form-control" type="password" v-model="form.pipedrive_key" autocomplete="new-password" />
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input id="pipedriveVerify" class="form-check-input" type="checkbox" v-model="form.require_pipedrive_user_verification" />
                                <label class="form-check-label" for="pipedriveVerify">
                                    Require Pipedrive user verification
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="save" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                        {{ __('message.update') }}
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'pipedrive-settings'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving = ref(false)
const form = reactive({
    status: false,
    pipedrive_key: '',
    require_pipedrive_user_verification: false,
})

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/pipedrive`)
        Object.assign(form, res.data?.data ?? {})
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save() {
    saving.value = true
    try {
        const res = await http.patch(`${baseUrl}/settings/pipedrive`, {
            status: form.status ? 1 : 0,
            pipedrive_key: form.pipedrive_key,
            require_pipedrive_user_verification: form.require_pipedrive_user_verification ? 1 : 0,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
