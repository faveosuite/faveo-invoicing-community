<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.cloud_hub') }}</h4>
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
                                <input id="cloudButton" class="form-check-input" type="checkbox" v-model="form.cloud_button" />
                                <label class="form-check-label" for="cloudButton">
                                    {{ form.cloud_button ? __('message.enable') : __('message.disable') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.cloud_central_domain') }}</label>
                            <input class="form-control" v-model="form.cloud_central_domain" placeholder="https://example.com" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">{{ __('message.cloud_cname') }}</label>
                            <input class="form-control" v-model="form.cloud_cname" />
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

const COMPONENT = 'cloud-details'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving = ref(false)
const form = reactive({
    cloud_central_domain: '',
    cloud_cname: '',
    cloud_button: false,
})

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/cloud-details`)
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
        const [detailsRes] = await Promise.all([
            http.post(`${baseUrl}/cloud-details`, {
                cloud_central_domain: form.cloud_central_domain,
                cloud_cname: form.cloud_cname,
            }),
            http.post(`${baseUrl}/enable/cloud`, { debug: form.cloud_button ? 'true' : 'false' }),
        ])
        successHandler(detailsRes, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
