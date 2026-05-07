<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h5 class="card-title">{{ __('message.configuring_demo') }}</h5>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-sm-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" :value="true" v-model="status" id="enableStatus" />
                                <label class="form-check-label" for="enableStatus">{{ __('message.enable') }}</label>
                            </div>
                        </div>
                        <div class="col-12 col-sm-3 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" :value="false" v-model="status" id="disableStatus" />
                                <label class="form-check-label" for="disableStatus">{{ __('message.disable') }}</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="save" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        {{ __('message.save') }}
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'demo-page'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving = ref(false)
const status = ref(false)

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/demo`)
        status.value = res.data?.data?.status ?? false
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save() {
    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/save/demo`, { status: status.value })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
