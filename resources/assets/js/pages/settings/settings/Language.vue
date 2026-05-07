<template>
    <div>
        <AppAlert componentName="language-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Languages</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <div v-else class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Locale</th>
                                <th>Default</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="lang in languages" :key="lang.id">
                                <td>{{ lang.name }}</td>
                                <td>{{ lang.locale }}</td>
                                <td>
                                    <span v-if="lang.locale === defaultLanguage" class="badge bg-info">Default</span>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            :checked="Boolean(lang.status)"
                                            :disabled="toggling === lang.locale"
                                            @change="toggleStatus(lang)"
                                        />
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!languages.length">
                                <td colspan="4" class="text-center text-muted">No languages found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const toggling = ref(null)
const languages = ref([])
const defaultLanguage = ref('')

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/languages`, { params: { limit: 100 } })
        const d = res.data?.data ?? {}
        languages.value = d.languages?.data ?? []
        defaultLanguage.value = d.default_language ?? ''
    } catch (e) {
        errorHandler(e, 'language-index')
    } finally {
        loading.value = false
    }
})

async function toggleStatus(lang) {
    toggling.value = lang.locale
    try {
        const res = await http.post(`${baseUrl}/language-toggle`, {
            locale: lang.locale,
            status: !lang.status,
        })
        lang.status = !lang.status
        successHandler(res, 'language-index')
    } catch (e) {
        errorHandler(e, 'language-index')
    } finally {
        toggling.value = null
    }
}
</script>
