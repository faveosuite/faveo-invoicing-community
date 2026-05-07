<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">License Permissions</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <p class="text-muted mb-3">Assign permissions to each license type.</p>
                    <div v-for="license in licenseTypes" :key="license.id" class="card card-outline card-secondary mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ license.name }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div v-for="perm in license.all_permissions" :key="perm.id" class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            :id="`perm-${license.id}-${perm.id}`"
                                            :checked="perm.assigned"
                                            @change="togglePermission(license, perm, $event.target.checked)"
                                        />
                                        <label class="form-check-label" :for="`perm-${license.id}-${perm.id}`">
                                            {{ perm.permissions }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-primary btn-sm" @click="savePermissions(license)" :disabled="saving === license.id">
                                <span v-if="saving === license.id" class="spinner-border spinner-border-sm me-1"></span>
                                Save
                            </button>
                        </div>
                    </div>

                    <div v-if="!licenseTypes.length" class="text-muted text-center py-4">
                        No license types found.
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

const COMPONENT = 'license-permissions'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving = ref(null)
const licenseTypes = ref([])

onMounted(async () => {
    await loadPermissions()
})

async function loadPermissions() {
    loading.value = true
    try {
        const res = await http.get(`${baseUrl}/get-license-permission`, { params: { limit: 200 } })
        licenseTypes.value = res.data?.data?.license_types?.data ?? []
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
}

function togglePermission(license, perm, checked) {
    perm.assigned = checked
}

async function savePermissions(license) {
    saving.value = license.id
    const assignedIds = license.all_permissions
        .filter(p => p.assigned)
        .map(p => p.id)
    try {
        const res = await http.delete(`${baseUrl}/add-permission`, {
            data: { licenseId: license.id, permissionid: assignedIds },
        })
        successHandler(res, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = null }
}
</script>
