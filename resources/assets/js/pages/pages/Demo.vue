<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.configuring_demo') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold d-block">{{ __('message.status') }}</label>
                            <Checkbox
                                name="status"
                                :label="__('message.enable')"
                                :value="status"
                                :onChange="(val) => status = val"
                            />
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="save" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import Checkbox from '@/components/Reusable/FormField/Checkbox.vue'
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
