<template>
    <div>
        <AppAlert componentName="reports-settings" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Report Settings</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Records Per Export</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model.number="form.records"
                                    min="1"
                                    max="3000"
                                    placeholder="e.g. 3000"
                                />
                                <small class="text-muted">Maximum number of records to include in each export (1–3000).</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="submit" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Save
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

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving = ref(false)

const form = reactive({
    records: 3000,
})

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/reports/setting`)
        const data = res.data?.data ?? res.data
        form.records = data?.records ?? 3000
    } catch (e) {
        errorHandler(e, 'reports-settings')
    } finally {
        loading.value = false
    }
})

async function submit() {
    saving.value = true
    try {
        const res = await http.patch(`${baseUrl}/reports/setting`, { records: form.records })
        successHandler(res, 'reports-settings')
    } catch (e) {
        errorHandler(e, 'reports-settings')
    } finally {
        saving.value = false
    }
}
</script>
