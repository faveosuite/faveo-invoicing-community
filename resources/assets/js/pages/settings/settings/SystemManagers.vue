<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">System Managers</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <!-- Account Managers -->
                    <h5 class="fw-bold mb-3">Account Managers</h5>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Existing Account Manager</label>
                            <select class="form-select" v-model="form.existingAccManager">
                                <option value="">— None —</option>
                                <option v-for="m in accountManagers" :key="m.id" :value="m.id">
                                    {{ m.name }} ({{ m.email }})
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">New Account Manager</label>
                            <DynamicSelect
                                :apiEndpoint="`${baseUrl}/search-admins`"
                                searchParam="q"
                                dataKey="data"
                                labelKey="name"
                                valueKey="id"
                                placeholder="Search admin users..."
                                :value="form.newAccManager"
                                @update:value="form.newAccManager = $event"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" v-model="form.autoAssignAccount" />
                                <label class="form-check-label">Auto-assign Account Manager</label>
                            </div>
                        </div>
                    </div>

                    <hr />

                    <!-- Sales Managers -->
                    <h5 class="fw-bold mb-3">Sales Managers</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Existing Sales Manager</label>
                            <select class="form-select" v-model="form.existingSaleManager">
                                <option value="">— None —</option>
                                <option v-for="m in salesManagers" :key="m.id" :value="m.id">
                                    {{ m.name }} ({{ m.email }})
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">New Sales Manager</label>
                            <DynamicSelect
                                :apiEndpoint="`${baseUrl}/search-admins`"
                                searchParam="q"
                                dataKey="data"
                                labelKey="name"
                                valueKey="id"
                                placeholder="Search admin users..."
                                :value="form.newSaleManager"
                                @update:value="form.newSaleManager = $event"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" v-model="form.autoAssignSales" />
                                <label class="form-check-label">Auto-assign Sales Manager</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="submit" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                        Save
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'system-managers'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving = ref(false)
const accountManagers = ref([])
const salesManagers = ref([])

const form = reactive({
    existingAccManager: '',
    newAccManager: '',
    autoAssignAccount: false,
    existingSaleManager: '',
    newSaleManager: '',
    autoAssignSales: false,
})

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/system-managers`)
        const d = res.data?.data ?? {}
        accountManagers.value = d.account_managers ?? []
        salesManagers.value = d.sales_managers ?? []
        form.autoAssignAccount = d.account_managers_auto_assign ?? false
        form.autoAssignSales = d.sales_managers_auto_assign ?? false
        if (accountManagers.value.length) form.existingAccManager = accountManagers.value[0].id
        if (salesManagers.value.length) form.existingSaleManager = salesManagers.value[0].id
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
})

async function submit() {
    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/updateSystemManager`, {
            existingAccManager: form.existingAccManager || null,
            newAccManager: form.newAccManager || null,
            autoAssignAccount: form.autoAssignAccount ? 1 : 0,
            existingSaleManager: form.existingSaleManager || null,
            newSaleManager: form.newSaleManager || null,
            autoAssignSales: form.autoAssignSales ? 1 : 0,
        })
        successHandler(res, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
