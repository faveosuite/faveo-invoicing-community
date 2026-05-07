<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.localized_license') }}</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <div v-else class="card-body table-responsive p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('message.license_file_name') }}</th>
                            <th>{{ __('message.order_no') }}</th>
                            <th class="text-center">{{ __('message.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="file in files" :key="file.file_name">
                            <td><code>{{ file.file_name }}</code></td>
                            <td>{{ file.order_number || '--' }}</td>
                            <td class="text-center">
                                <a :href="file.download_url" class="btn btn-sm btn-secondary me-1">
                                    <i class="fas fa-download me-1"></i>License
                                </a>
                                <a :href="file.private_key_url" class="btn btn-sm btn-secondary me-1">
                                    <i class="fas fa-key me-1"></i>Key
                                </a>
                                <button class="btn btn-light table_btn" @click="remove(file)" :disabled="deleting === file.file_name">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!files.length">
                            <td colspan="3" class="text-center text-muted py-4">{{ __('message.no-record') }}</td>
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

const COMPONENT = 'localized-license'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const deleting = ref(null)
const files = ref([])

onMounted(load)

async function load() {
    loading.value = true
    try {
        const res = await http.get(`${baseUrl}/localized-license/files`)
        files.value = res.data?.data?.files ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
}

async function remove(file) {
    if (!confirm(`Delete ${file.file_name}?`)) return
    deleting.value = file.file_name
    try {
        const res = await http.delete(`${baseUrl}/localized-license/files`, { data: { file_name: file.file_name } })
        successHandler(res, COMPONENT)
        await load()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        deleting.value = null
    }
}
</script>
