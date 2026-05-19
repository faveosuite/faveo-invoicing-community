<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.debugging_settings') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold d-block">{{ __('message.debug_mode') }}</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" v-model="debugEnabled" id="debugMode" />
                            <label class="form-check-label" for="debugMode">
                                {{ debugEnabled ? __('message.enabled') : __('message.disabled') }}
                            </label>
                        </div>
                        <small class="text-muted">{{ __('message.debug_mode_description') }}</small>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="submit" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'debugging-settings'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving = ref(false)
const debugEnabled = ref(false)

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/debugg`)
        debugEnabled.value = Boolean(res.data?.data?.debug)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function submit() {
    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/save/debugg`, { debug: debugEnabled.value })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
