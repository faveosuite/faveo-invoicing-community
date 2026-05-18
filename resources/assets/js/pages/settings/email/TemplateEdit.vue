<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title mb-0">{{ form.name || 'Edit Template' }}</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">

                    <!-- Collapsible shortcodes card -->
                    <div v-if="form.codes" class="card card-light mb-3" :class="{ 'collapsed-card': shortcodesCollapsed }">
                        <div class="card-header">
                            <h5 class="card-title mb-0">List of Available Shortcodes</h5>
                            <div class="card-tools">
                                <button
                                    type="button"
                                    class="btn btn-tool"
                                    @click="shortcodesCollapsed = !shortcodesCollapsed"
                                    :title="shortcodesCollapsed ? 'Expand' : 'Collapse'"
                                >
                                    <i :class="shortcodesCollapsed ? 'fas fa-plus' : 'fas fa-minus'"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info alert-dismissible fade show mb-3" role="alert">
                                <i class="fas fa-info-circle me-1"></i>
                                Copy a shortcode and paste it into the template content where you want the value to appear.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <ul class="list-inline mb-0">
                                <li
                                    v-for="(shortcode, label) in form.codes"
                                    :key="label"
                                    class="list-inline-item mb-1"
                                    style="width: 23%"
                                >
                                    <span v-tooltip :title="label">{{ shortcode }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <TextField
                                name="name"
                                label="Name *"
                                :value="form.name"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>
                        <div class="col-md-4">
                            <SelectField
                                name="type"
                                label="Type *"
                                :elements="typeOptions"
                                :value="selectedType"
                                :onChange="(val) => form.type = val?.id ?? ''"
                                :searchable="true"
                                :clearable="false"
                                placeholder="Select a type"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField
                                name="reply_to"
                                label="Reply Email"
                                :value="form.reply_to"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Content *</label>
                        <TinyMCE
                            name="data"
                            id="editor-template"
                            :value="form.data"
                            :onChange="(val, key) => form[key] = val"
                        />
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="save" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Update
                    </button>
                    <RouterLink to="/settings/email/templates" class="btn btn-secondary ms-2">
                        Cancel
                    </RouterLink>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import SelectField from '@/themes/adminlte/components/forms/SelectField.vue'

const COMPONENT = 'template-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route  = useRoute()
const router = useRouter()
const id = route.params.id

const loading  = ref(true)
const saving   = ref(false)
const shortcodesCollapsed = ref(false)

const form = reactive({
    name: '', type: '', reply_to: '', data: '', codes: null,
})

const typeOptions = ref([])

const LABEL_OVERRIDES = {
    welcome_mail:                   'Welcome Mail',
    forgot_password_mail:           'Forgot Password',
    subscription_going_to_end_mail: 'Subscription Going To End',
    subscription_over_mail:         'Subscription Over',
    invoice_mail:                   'Invoice',
    order_mail:                     'Order Mail',
    auto_subscription_going_to_end: 'Auto Renewal Reminder',
    payment_successfull:            'Auto Payment Successful',
    payment_failed:                 'Auto Payment Failed',
    cloud_deleted:                  'Order Deleted',
    cloud_created:                  'New Instance Created',
    contact_us:                     'Contact Us',
    demo_request:                   'Request a Demo',
    registration_mail:              'Register Mail',
    sales_manager_email:            'New Sales Manager',
    account_manager_email:          'New Account Manager',
}

function toLabel(name) {
    return LABEL_OVERRIDES[name]
        ?? name.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}

const selectedType = computed(() =>
    typeOptions.value.find(t => t.id === String(form.type)) ?? null
)

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/template/edit/${id}`)
        const d = res.data?.data ?? {}

        form.name    = d.template?.name     ?? ''
        form.type    = String(d.template?.type ?? '')
        form.reply_to = d.template?.reply_to ?? ''
        form.data    = d.template?.data      ?? ''
        form.codes   = d.codes               ?? null

        typeOptions.value = Object.entries(d.type ?? {}).map(([id, name]) => ({
            id,
            name: toLabel(name),
        }))
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save() {
    saving.value = true
    try {
        const res = await http.put(`${baseUrl}/template/update/${id}`, {
            name:     form.name,
            type:     form.type,
            reply_to: form.reply_to,
            data:     form.data,
        })
        successHandler(res, COMPONENT)
        router.push('/settings/email/templates')
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
