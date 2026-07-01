<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h3 class="card-title">{{ __('message.system_manager_settings') }}</h3>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>{{ __('message.enable_account_manager') }}</strong>
                            <small class="text-muted d-block mb-1">{{ __('message.account_upon_creation') }}</small>
                            <Switch name="autoAssignAccount" :value="form.autoAssignAccount" :onChange="(val) => form.autoAssignAccount = val" />
                        </div>
                        <div class="col-md-6">
                            <strong>{{ __('message.enable_sales_manager') }}</strong>
                            <small class="text-muted d-block mb-1">{{ __('message.auto_assign_sales_managers_desc') }}</small>
                            <Switch name="autoAssignSales" :value="form.autoAssignSales" :onChange="(val) => form.autoAssignSales = val" />
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
import Switch from '@/components/Reusable/FormField/Switch.vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const COMPONENT = 'system-managers'
const baseUrl = useBaseUrl()

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
        const res = await http.get(`/system-managers`)
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
        const res = await http.post(`/updateSystemManager`, {
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
