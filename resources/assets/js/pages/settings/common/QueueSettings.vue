<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.queue') }}</h4>
            </div>
            <div class="card-body">
                <div v-if="loading" class="text-center py-4">
                    <span class="spinner-border text-secondary"></span>
                </div>
                <template v-else-if="fields.length">
                    <div class="row">
                        <div v-for="field in fields" :key="field.name" class="col-sm-6 mb-3">
                            <label class="form-label">
                                {{ field.label }}
                                <span v-if="field.required" class="text-danger">*</span>
                            </label>
                            <input
                                :type="field.type ?? 'text'"
                                class="form-control"
                                :placeholder="field.placeholder ?? ''"
                                v-model="field.value"
                            />
                        </div>
                    </div>
                </template>
                <div v-else class="text-muted">{{ __('message.no-record') }}</div>
            </div>
            <div v-if="!loading && fields.length" class="card-footer">
                <button type="button" class="btn btn-primary me-2" :disabled="saving" @click="save">
                    <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                    <i v-else class="fas fa-save me-1"></i>
                    {{ __('message.save') }}
                </button>
                <RouterLink to="/settings/common/queues" class="btn btn-secondary">
                    {{ __('message.cancel') }}
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'queue-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const route  = useRoute()
const id     = route.params.id

const loading = ref(true)
const saving  = ref(false)
const fields  = ref([])

async function load() {
    loading.value = true
    try {
        const res = await http.get(`${baseUrl}/queue/${id}/form`)
        fields.value = res.data?.data?.fields ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
}

async function save() {
    saving.value = true
    try {
        const data = {}
        fields.value.forEach(f => { data[f.name] = f.value })
        const res = await http.post(`${baseUrl}/queue/${id}`, data)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}

onMounted(load)
</script>
