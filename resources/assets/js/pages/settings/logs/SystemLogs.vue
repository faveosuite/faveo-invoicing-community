<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.log_setting') }}</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('message.error-log') }}</label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" :value="true" v-model="form.error_log" id="errorLogYes" />
                                    <label class="form-check-label" for="errorLogYes">{{ __('message.yes') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" :value="false" v-model="form.error_log" id="errorLogNo" />
                                    <label class="form-check-label" for="errorLogNo">{{ __('message.no') }}</label>
                                </div>
                            </div>
                            <small class="text-muted">{{ __('message.enable-error-logging') }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">{{ __('message.error-email') }}</label>
                            <input type="email" class="form-control" v-model="form.error_email" placeholder="error@example.com" />
                            <small class="text-muted">{{ __('message.provide-error-reporting-email') }}</small>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="save" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
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

const COMPONENT = 'system-logs'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving = ref(false)

const form = reactive({ error_log: false, error_email: '' })

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/error`)
        const d = res.data?.data ?? {}
        form.error_log   = d.error_log   ?? false
        form.error_email = d.error_email ?? ''
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
})

async function save() {
    saving.value = true
    try {
        const res = await http.patch(`${baseUrl}/settings/error`, {
            error_log:   form.error_log ? 1 : 0,
            error_email: form.error_email,
        })
        successHandler(res, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
