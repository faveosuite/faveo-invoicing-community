<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Deployment Settings</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <div class="row align-items-start">

                        <!-- Enable / disable toggle -->
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label class="form-label">
                                    Client Deployments
                                    <ToolTip :message="__('message.deployment_enabled_tooltip')" size="small" />
                                </label>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <status-switch
                                        name="deployment_enabled"
                                        :value="form.deployment_enabled"
                                        :onChange="(val) => form.deployment_enabled = val"
                                    />
                                    <span class="text-muted small">{{ form.deployment_enabled ? 'Enabled' : 'Disabled' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Install Script URL -->
                        <div class="col-md-4">
                            <TextField
                                name="install_script_url"
                                :label="__('message.install_script_url_label')"
                                :required="true"
                                :hint="__('message.install_script_url_tooltip')"
                                :value="form.install_script_url"
                                placeholder="https://example.com/install.sh"
                                :error="errors.install_script_url"
                                :onChange="(val, key) => { setFieldError(key, undefined); form[key] = val }"
                            />
                        </div>

                        <!-- Manual Install Guide URL -->
                        <div class="col-md-4">
                            <TextField
                                name="manual_install_guide_url"
                                :label="__('message.manual_install_guide_url')"
                                :required="true"
                                :hint="__('message.manual_install_guide_url_tooltip')"
                                :value="form.manual_install_guide_url"
                                placeholder="https://docs.example.com/install"
                                :error="errors.manual_install_guide_url"
                                :onChange="(val, key) => { setFieldError(key, undefined); form[key] = val }"
                            />
                        </div>

                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="save" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { validateForm } from '@/helpers/formUtils.js'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import ToolTip from '@/components/Reusable/Tooltip.vue'
import { deploymentSchema } from '@/validations/admin/deploymentValidations'

const COMPONENT = 'deployment-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const { errors, setErrors, setFieldError } = useForm()
const loading = ref(true)
const saving  = ref(false)
const form    = reactive({
    deployment_enabled:       true,
    install_script_url:       '',
    manual_install_guide_url: '',
})

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/deployment`)
        const d   = res.data?.data ?? {}
        form.deployment_enabled       = d.deployment_enabled ?? true
        form.install_script_url       = d.install_script_url ?? ''
        form.manual_install_guide_url = d.manual_install_guide_url ?? ''
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save() {
    if (! await validateForm(deploymentSchema, form, setErrors)) return

    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/settings/deployment`, {
            deployment_enabled:       form.deployment_enabled,
            install_script_url:       form.install_script_url || null,
            manual_install_guide_url: form.manual_install_guide_url || null,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
