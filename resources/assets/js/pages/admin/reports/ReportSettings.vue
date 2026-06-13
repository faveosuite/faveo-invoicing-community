<template>
    <div>
        <AppAlert componentName="reports-settings" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.report_settings') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <NumberField
                                name="records"
                                :label="__('message.records_per_export')"
                                :value="form.records"
                                :onChange="(val) => form.records = Number(val)"
                                :max="3000"
                                placeholder="e.g. 3000"
                            />
                            <small class="text-muted">{{ __('message.records_per_export_desc') }}</small>
                        </div>
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
import { reactive, ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import NumberField from '@/components/Reusable/FormField/NumberField.vue'

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
