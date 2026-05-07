<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h4 class="card-title">Create Product Group</h4>
            </div>

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
                    Save
                </button>
                <router-link to="/products/groups" class="btn btn-secondary ms-2">Cancel</router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'groups-create'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router = useRouter()

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

async function submit() {
    saving.value = true
    try {
        const res = await http.put(`${baseUrl}/group`, {
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
