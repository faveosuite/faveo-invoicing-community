<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.queue') }}</h4>
            </div>
            <div class="card-body">
                <inline-loader v-if="loading" />
                <template v-else-if="fields.length">
                    <div class="row">
                        <div v-for="field in fields" :key="field.name" class="col-sm-6">
                            <TextField
                                :name="field.name"
                                :label="field.label"
                                :required="field.required"
                                :type="field.type ?? 'text'"
                                :value="field.value"
                                :onChange="(val) => field.value = val"
                                :placehold="field.placeholder ?? ''"
                            />
                        </div>
                    </div>
                </template>
                <div v-else class="text-muted">{{ __('message.no-record') }}</div>
            </div>
            <div v-if="!loading && fields.length" class="card-footer">
                <action-button action="save" :loading="saving" @click="save" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import TextField from '@/components/Reusable/FormField/TextField.vue'
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
