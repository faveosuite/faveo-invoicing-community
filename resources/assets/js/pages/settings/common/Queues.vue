<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Queue Management</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <!-- Cron command info -->
                    <div v-if="cronPath" class="alert alert-info mb-4">
                        <strong>Artisan Path:</strong>
                        <code class="ms-2">{{ cronPath }}</code>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Queue</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="q in queues" :key="q.id">
                                    <td>
                                        <template v-if="q.QueueDetails?.name?.link">
                                            <a :href="q.QueueDetails.name.link">{{ q.QueueDetails.name.text }}</a>
                                        </template>
                                        <template v-else>{{ q.QueueDetails?.name?.text }}</template>
                                    </td>
                                    <td>
                                        <span
                                            :class="q.QueueDetails?.status?.code === 1 ? 'badge bg-success' : 'badge bg-secondary'"
                                        >{{ q.QueueDetails?.status?.label }}</span>
                                    </td>
                                    <td>
                                        <button
                                            v-if="q.QueueDetails?.action?.type !== 'activated'"
                                            class="btn btn-sm btn-primary"
                                            :disabled="activating === q.id"
                                            @click="activate(q.id)"
                                        >
                                            <span v-if="activating === q.id" class="spinner-border spinner-border-sm me-1"></span>
                                            Activate
                                        </button>
                                        <span v-else class="text-success fw-bold">
                                            <i class="fas fa-circle-check me-1"></i>Active
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="!queues.length">
                                    <td colspan="3" class="text-center text-muted">No queues found</td>
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
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'queues'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const activating = ref(null)
const cronPath = ref('')
const queues = ref([])

onMounted(async () => {
    await loadQueues()
})

async function loadQueues() {
    loading.value = true
    try {
        const res = await http.get(`${baseUrl}/queue/list`)
        const d = res.data?.data ?? {}
        cronPath.value = d.cron_path ?? ''
        queues.value = d.queues?.data ?? []
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
}

async function activate(id) {
    activating.value = id
    try {
        const res = await http.post(`${baseUrl}/queue/${id}/activate`)
        successHandler(res, COMPONENT)
        await loadQueues()
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { activating.value = null }
}
</script>
