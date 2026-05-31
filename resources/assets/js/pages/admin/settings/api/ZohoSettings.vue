<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.zoho_integration') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <div v-else class="card-body">
                <div v-if="!integrations.length" class="text-center text-muted py-4">
                    {{ __('message.no_record_found') }}
                </div>

                <div class="row">
                    <div v-for="item in integrations" :key="item.id" class="col-md-4 mb-4">
                        <ZohoCard
                            :integration="item"
                            :icon-class="platformIcon(item.platform)"
                            @connect="openModal"
                            @mapping="goToMapping"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Credentials modal -->
        <AppModal :showModal="showModal" :onClose="closeModal" classname="modal-md">
            <template #title>
                <h4>{{ __('message.connect') }} Zoho <span class="text-uppercase">{{ form.platform }}</span></h4>
            </template>

            <template #fields>
                <div class="mb-3">
                    <TextField
                        name="client_id"
                        :label="__('message.client_id')"
                        :value="form.client_id"
                        :error="errors.client_id"
                        :onChange="(val, key) => form[key] = val"
                    />
                </div>
                <div class="mb-3">
                    <TextField
                        name="client_secret"
                        type="password"
                        :label="__('message.client_secret')"
                        :value="form.client_secret"
                        :error="errors.client_secret"
                        :onChange="(val, key) => form[key] = val"
                    />
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('message.redirect_uri') }}</label>
                    <div class="input-group">
                        <input type="text" class="form-control bg-light" :value="redirectUri" readonly style="cursor: default;">
                        <button type="button" class="btn btn-light border" :title="__('message.copy')" @click="copyRedirectUri">
                            <i class="fa" :class="uriCopied ? 'fa-check text-success' : 'fa-copy'"></i>
                        </button>
                    </div>
                    <small class="text-muted">{{ __('message.zoho_redirect_uri_help') }}</small>
                </div>
                <div class="mb-3">
                    <SelectField
                        name="region"
                        :label="__('message.region')"
                        :elements="regionOptions"
                        :value="selectedRegion"
                        :error="errors.region"
                        :onChange="(val) => form.region = val?.id ?? null"
                    />
                </div>
            </template>

            <template #controls>
                <action-button action="save" :label="__('message.save_and_continue')" :loading="saving" @click="save" />
            </template>
        </AppModal>
    </div>
</template>

<script setup>
import { reactive, ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { useAlertStore } from '@/core/stores/alert.js'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { useForm } from 'vee-validate'
import { zohoCredentialsSchema } from '@/validations/admin/zohoValidations.js'
import TextField from '@/themes/adminlte/components/forms/TextField.vue'
import SelectField from '@/themes/adminlte/components/forms/SelectField.vue'
import ZohoCard from './ZohoCard.vue'

const COMPONENT = 'zoho-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route   = useRoute()
const router  = useRouter()

const PLATFORM_ICONS = {
    crm:       'fas fa-address-book',
    campaigns: 'fas fa-bullhorn',
}

function platformIcon(platform) {
    return PLATFORM_ICONS[platform] ?? 'fas fa-plug'
}

function goToMapping(item) {
    router.push(`/settings/api/zoho/${item.platform}/contacts/mapping`)
}

const { errors, setErrors } = useForm()

const loading      = ref(true)
const saving       = ref(false)
const showModal    = ref(false)
const integrations = ref([])
const uriCopied    = ref(false)

const regionOptions = [
    { id: 'in', name: 'India' },
    { id: 'us', name: 'US' },
    { id: 'eu', name: 'Europe' },
    { id: 'au', name: 'Australia' },
]

const form = reactive({
    integration_id: null,
    platform:       '',
    client_id:      '',
    client_secret:  '',
    region:         'in',
})

const selectedRegion = computed(() => regionOptions.find(o => o.id === form.region) ?? null)

// There is a single callback route, so the redirect URI is fixed and derived —
// not editable. The admin registers this exact value in the Zoho API console.
const redirectUri = `${baseUrl}/zoho/oauth/callback`

async function copyRedirectUri() {
    try {
        await navigator.clipboard.writeText(redirectUri)
        uriCopied.value = true
        setTimeout(() => { uriCopied.value = false }, 2000)
    } catch {
        /* clipboard unavailable — the field is selectable for manual copy */
    }
}

async function loadIntegrations() {
    loading.value = true
    try {
        const res = await http.get(`${baseUrl}/zoho/integrations`)
        integrations.value = res.data?.data ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    // Show the result of the OAuth callback redirect, if any.
    if (route.query.zoho_status) {
        const type = route.query.zoho_status === 'success' ? 'success' : 'danger'
        useAlertStore().setAlert({
            type,
            message: route.query.message ?? '',
            component_name: COMPONENT,
        })
    }
    await loadIntegrations()
})

async function openModal(item) {
    setErrors({})
    Object.assign(form, {
        integration_id: item.id,
        platform:       item.platform,
        client_id:      '',
        client_secret:  '',
        region:         'in',
    })
    showModal.value = true

    try {
        const res = await http.get(`${baseUrl}/zoho/getKeys/${item.id}`)
        const d   = res.data?.data
        if (d) {
            Object.assign(form, {
                client_id:     d.client_id     ?? '',
                client_secret: d.client_secret ?? '',
                region:        d.region        ?? 'in',
            })
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    }
}

function closeModal() {
    showModal.value = false
}

async function save() {
    try {
        zohoCredentialsSchema.validateSync(form, { abortEarly: false })
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        return
    }

    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/zoho/saveKeys`, {
            integration_id: form.integration_id,
            client_id:      form.client_id,
            client_secret:  form.client_secret,
            region:         form.region,
        })

        const redirectUrl = res.data?.data?.redirect_url
        if (redirectUrl) {
            // Hand off to Zoho's OAuth consent screen.
            window.location.href = redirectUrl
            return
        }
        successHandler(res, COMPONENT)
        closeModal()
        await loadIntegrations()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
