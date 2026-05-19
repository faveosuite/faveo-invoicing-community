<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">{{ __('message.system_manager_settings') }}</h3>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="autoAssignAccountSwitch" v-model="form.autoAssignAccount" />
                                <label class="form-check-label" for="autoAssignAccountSwitch">
                                    <strong>{{ __('message.enable_account_manager') }}</strong>
                                    <small class="text-muted d-block">{{ __('message.account_upon_creation') }}</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="autoAssignSalesSwitch" v-model="form.autoAssignSales" />
                                <label class="form-check-label" for="autoAssignSalesSwitch">
                                    <strong>{{ __('message.enable_sales_manager') }}</strong>
                                    <small class="text-muted d-block">{{ __('message.auto_assign_sales_managers_desc') }}</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <DynamicSelect
                                name="existingAccManager"
                                :label="__('message.current_account_manager')"
                                :elements="accountManagers"
                                :value="form.existingAccManager"
                                :onChange="(val) => form.existingAccManager = val"
                                :placeholder="__('message.pipe_select_option')"
                            />
                        </div>
                        <div class="col-md-6">
                            <DynamicSelect
                                name="newAccManager"
                                :label="__('message.select_replacement_manager')"
                                :apiEndpoint="`${baseUrl}/search-admins`"
                                :value="form.newAccManager"
                                :onChange="(val) => form.newAccManager = val"
                                :placeholder="__('message.search')"
                            />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <DynamicSelect
                                name="existingSaleManager"
                                :label="__('message.current_sales_manager')"
                                :elements="salesManagers"
                                :value="form.existingSaleManager"
                                :onChange="(val) => form.existingSaleManager = val"
                                :placeholder="__('message.pipe_select_option')"
                            />
                        </div>
                        <div class="col-md-6">
                            <DynamicSelect
                                name="newSaleManager"
                                :label="__('message.select_replacement_sales_manager')"
                                :apiEndpoint="`${baseUrl}/search-admins`"
                                :value="form.newSaleManager"
                                :onChange="(val) => form.newSaleManager = val"
                                :placeholder="__('message.search')"
                            />
                        </div>
                    </div>

                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="submit" />
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
    existingAccManager: null,
    newAccManager: null,
    autoAssignAccount: false,
    existingSaleManager: null,
    newSaleManager: null,
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
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
})

async function submit() {
    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/updateSystemManager`, {
            existingAccManager: form.existingAccManager?.id || null,
            newAccManager: form.newAccManager?.id || null,
            autoAssignAccount: form.autoAssignAccount ? 1 : 0,
            existingSaleManager: form.existingSaleManager?.id || null,
            newSaleManager: form.newSaleManager?.id || null,
            autoAssignSales: form.autoAssignSales ? 1 : 0,
        })
        successHandler(res, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
