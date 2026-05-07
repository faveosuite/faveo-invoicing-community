<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">{{ plugin?.name ?? pluginSlug }} Settings</h4>
                <RouterLink to="/settings/payment-gateway" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </RouterLink>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else-if="!plugin">
                <div class="card-body">
                    <div class="alert alert-warning">Payment gateway not found.</div>
                </div>
            </template>

            <template v-else>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Name</th>
                                    <td>{{ plugin.name }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ plugin.description }}</td>
                                </tr>
                                <tr>
                                    <th>Author</th>
                                    <td>{{ plugin.author }}</td>
                                </tr>
                                <tr>
                                    <th>Version</th>
                                    <td>{{ plugin.version }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span :class="plugin.status ? 'badge bg-success' : 'badge bg-secondary'">
                                            {{ plugin.status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="plugin.supported_currencies?.length">
                                    <th>Currencies</th>
                                    <td>{{ plugin.supported_currencies.join(', ') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div v-if="plugin.settings" class="alert alert-info">
                        <i class="fas fa-circle-info me-2"></i>
                        Configure detailed settings for this payment gateway at:
                        <a :href="`${baseUrl}/${plugin.settings}`" class="ms-2 fw-bold">
                            {{ plugin.settings }} <i class="fas fa-arrow-up-right-from-square ms-1"></i>
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import http from '@/plugins/axios'
import { errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'payment-gateway-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route = useRoute()
const pluginSlug = route.params.id

const loading = ref(true)
const plugin = ref(null)

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/payment-gateway-list`)
        const list = res.data?.data ?? []
        plugin.value = list.find(p => p.name === pluginSlug) ?? null
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
})
</script>
