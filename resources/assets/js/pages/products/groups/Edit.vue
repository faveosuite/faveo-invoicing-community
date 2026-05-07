<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h4 class="card-title">Edit Product Group</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <TextField name="name" label="Name *" :value="form.name" :onChange="onChange" />
                        </div>
                        <div class="col-md-4">
                            <TextField name="headline" label="Headline" :value="form.headline" :onChange="onChange" />
                        </div>
                        <div class="col-md-4">
                            <TextField name="tagline" label="Tagline" :value="form.tagline" :onChange="onChange" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <DynamicSelect
                                name="pricing_templates_id"
                                label="Design Template *"
                                :apiEndpoint="`${baseUrl}/dependency/pricing-templates`"
                                dataKey="pricing_templates"
                                :value="form.templateObj"
                                :onChange="onChange"
                                placeholder="Select template"
                            />
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <div class="d-flex gap-3 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" :value="1" v-model="form.status" id="statusActive" />
                                        <label class="form-check-label" for="statusActive">Active</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" :value="0" v-model="form.status" id="statusInactive" />
                                        <label class="form-check-label" for="statusInactive">Inactive</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Hidden Group</label>
                                <div class="form-check mt-1">
                                    <input class="form-check-input" type="checkbox" :value="1" v-model="form.hidden" id="hiddenCheck" />
                                    <label class="form-check-label" for="hiddenCheck">Check this if this is a hidden group</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="submit" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Update
                    </button>
                    <router-link to="/products/groups" class="btn btn-secondary ms-2">Cancel</router-link>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'groups-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route = useRoute()
const router = useRouter()

const loading = ref(true)
const saving = ref(false)
const form = reactive({
    name: '',
    headline: '',
    tagline: '',
    hidden: 0,
    pricing_templates_id: null,
    templateObj: null,
    status: 0,
})

function onChange(val, name) {
    if (name === 'pricing_templates_id') {
        form.templateObj = val
        form.pricing_templates_id = val?.id ?? null
    } else {
        form[name] = val
    }
}

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/group/${route.params.id}`)
        const g = res.data
        form.name = g.name ?? ''
        form.headline = g.headline ?? ''
        form.tagline = g.tagline ?? ''
        form.hidden = g.hidden ?? 0
        form.status = g.status ?? 0
        form.pricing_templates_id = g.pricing_templates_id ?? null
        const pt = g.pricing_template ?? g.pricingTemplate
        if (pt) {
            form.templateObj = { id: pt.id, name: pt.name }
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function submit() {
    saving.value = true
    try {
        const res = await http.patch(`${baseUrl}/group/${route.params.id}`, {
            name:                form.name,
            headline:            form.headline || null,
            tagline:             form.tagline || null,
            hidden:              form.hidden ? 1 : 0,
            pricing_templates_id: form.pricing_templates_id,
            status:              form.status,
        })
        successHandler(res, COMPONENT)
        router.push('/products/groups')
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
