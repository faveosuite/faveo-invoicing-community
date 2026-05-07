<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.third_party_integrations') }}</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <div v-else class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('message.options') }}</th>
                            <th>{{ __('message.description') }}</th>
                            <th class="text-center">{{ __('message.status') }}</th>
                            <th class="text-center">{{ __('message.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="module in modules" :key="module.slug">
                            <td class="fw-semibold">{{ module.name }}</td>
                            <td>{{ module.description }}</td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        :id="`module-${module.slug}`"
                                        v-model="module.enabled"
                                        :disabled="savingKey === module.key"
                                        @change="toggle(module)"
                                    />
                                </div>
                            </td>
                            <td class="text-center">
                                <RouterLink v-if="module.route && module.enabled" :to="module.route" class="btn btn-light table_btn">
                                    <i class="fas fa-pen"></i>
                                </RouterLink>
                                <span v-else class="text-muted">--</span>
                            </td>
                        </tr>
                        <tr v-if="!modules.length">
                            <td colspan="4" class="text-center text-muted py-4">{{ __('message.no-record') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'third-party-integrations'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const savingKey = ref(null)
const modules = ref([])

onMounted(load)

async function load() {
    loading.value = true
    try {
        const res = await http.get(`${baseUrl}/module-settings`)
        modules.value = res.data?.data?.modules ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
}

async function toggle(module) {
    savingKey.value = module.key
    try {
        const payload = { [module.key]: module.enabled ? 1 : 0 }
        const res = await http.post(`${baseUrl}/licenseStatus`, payload)
        successHandler(res, COMPONENT)
    } catch (e) {
        module.enabled = !module.enabled
        errorHandler(e, COMPONENT)
    } finally {
        savingKey.value = null
    }
}
</script>
