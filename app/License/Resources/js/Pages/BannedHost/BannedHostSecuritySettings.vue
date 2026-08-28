<template>
    <div>
        <AppAlert componentName="banned-hosts" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ lang('security_settings') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <DynamicSelect
                                name="auto_ban_enabled"
                                :label="lang('auto_ban_enabled')"
                                :elements="enabledOptions"
                                :value="selectedEnabledOption"
                                :onChange="(val) => autoBanEnabled = !!val?.id"
                                :clearable="false"
                                :searchable="false"
                            />
                        </div>

                        <div class="col-md-4">
                            <DynamicSelect
                                name="failed_licensings_limit"
                                :label="lang('failed_licensings_limit')"
                                :tooltip="lang('failed_licensings_limit_tooltip')"
                                :elements="limitOptions"
                                :value="selectedLimitOption"
                                :onChange="(val) => failedLicensingsLimit = val?.id ?? 1"
                                :clearable="false"
                                :searchable="false"
                                :disabled="!autoBanEnabled"
                            />
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="saveSecuritySettings" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onBeforeMount } from 'vue'
import axios from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import { lang } from '@/helpers/extraLogics'
import DynamicSelect from '@/components/Reusable/FormField/DynamicSelect.vue'

const loading = ref(false)
const saving = ref(false)
const autoBanEnabled = ref(false)
const failedLicensingsLimit = ref(1)

const enabledOptions = [
    { id: 1, name: lang('enabled') },
    { id: 0, name: lang('disabled') },
]

const limitOptions = Array.from({ length: 10 }, (_, i) => {
    const attempts = i + 1
    return { id: attempts, name: `${attempts} ${attempts === 1 ? lang('attempt') : lang('attempts')}` }
})

const selectedEnabledOption = computed(() => enabledOptions.find(option => option.id === Number(autoBanEnabled.value)) ?? enabledOptions[1])
const selectedLimitOption = computed(() => limitOptions.find(option => option.id === failedLicensingsLimit.value) ?? limitOptions[0])

function saveSecuritySettings() {
    saving.value = true
    axios.post('/api/admin/bannedHosts/security-settings', {
        auto_ban_enabled: autoBanEnabled.value,
        failed_licensings_limit: failedLicensingsLimit.value,
    }).then(res => {
        successHandler(res, 'banned-hosts')
    }).catch(err => {
        errorHandler(err, 'banned-hosts')
    }).finally(() => {
        saving.value = false
    })
}

onBeforeMount(() => {
    loading.value = true
    axios.get('/api/admin/bannedHosts/security-settings').then(res => {
        autoBanEnabled.value = res.data.data.auto_ban_enabled
        // 0 predates the "attempts" dropdown (it used to mean disabled); the
        // toggle above now owns that, so treat it as "not yet configured" → 1.
        failedLicensingsLimit.value = res.data.data.failed_licensings_limit || 1
    }).catch(() => {}).finally(() => {
        loading.value = false
    })
})
</script>
