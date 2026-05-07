<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Payment Gateways</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Version</th>
                                    <th>Currencies</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="plugin in plugins" :key="plugin.name">
                                    <td class="fw-bold">{{ plugin.name }}</td>
                                    <td>{{ plugin.description }}</td>
                                    <td>{{ plugin.version }}</td>
                                    <td>
                                        <span v-if="plugin.supported_currencies?.length">
                                            {{ plugin.supported_currencies.join(', ') }}
                                        </span>
                                        <span v-else class="text-muted">All</span>
                                    </td>
                                    <td>
                                        <span :class="plugin.status ? 'badge bg-success' : 'badge bg-secondary'">
                                            {{ plugin.status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button
                                            class="btn btn-sm me-1"
                                            :class="plugin.status ? 'btn-warning' : 'btn-success'"
                                            :disabled="toggling === plugin.name"
                                            @click="toggleStatus(plugin)"
                                            :title="plugin.status ? 'Deactivate' : 'Activate'"
                                        >
                                            <span v-if="toggling === plugin.name" class="spinner-border spinner-border-sm"></span>
                                            <template v-else>
                                                <i :class="plugin.status ? 'fas fa-toggle-on' : 'fas fa-toggle-off'"></i>
                                            </template>
                                        </button>
                                        <RouterLink
                                            v-if="plugin.status"
                                            :to="`/settings/payment-gateway/${plugin.name}/edit`"
                                            class="btn btn-sm btn-light"
                                            title="Settings"
                                        >
                                            <i class="fas fa-gear"></i>
                                        </RouterLink>
                                    </td>
                                </tr>
                                <tr v-if="!plugins.length">
                                    <td colspan="6" class="text-center text-muted">No payment gateways found</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'payment-gateway-index'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const toggling = ref(null)
const plugins = ref([])

onMounted(async () => {
    await loadPlugins()
})

async function loadPlugins() {
    loading.value = true
    try {
        const res = await http.get(`${baseUrl}/payment-gateway-list`)
        plugins.value = res.data?.data ?? []
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
}

async function toggleStatus(plugin) {
    toggling.value = plugin.name
    try {
        const res = await http.post(`${baseUrl}/updatePaymentStatus`, {
            name:   plugin.name,
            status: plugin.status ? 0 : 1,
        })
        successHandler(res, COMPONENT)
        await loadPlugins()
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { toggling.value = null }
}
</script>
