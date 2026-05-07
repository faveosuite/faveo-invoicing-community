<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h4 class="card-title">Edit Page</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <TextField name="name" label="Name *" :value="form.name" :onChange="onChange" />
                        </div>
                        <div class="col-md-6">
                            <TextField name="slug" label="Slug *" :value="form.slug" :onChange="onChange" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Type</label>
                                <select class="form-select" v-model="form.type" @change="onTypeChange">
                                    <option value="">Custom</option>
                                    <option value="contactus">Contact Us</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <TextField name="url" label="URL *" :value="form.url" :onChange="onChange" :disabled="form.type === 'contactus'" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <DynamicSelect
                                name="parent_page_id"
                                label="Parent Page"
                                :apiEndpoint="`${baseUrl}/pages`"
                                dataKey="data"
                                :value="form.parentObj"
                                :onChange="onChange"
                                placeholder="Select parent page"
                            />
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Publishing Date *</label>
                                <input type="date" class="form-control" v-model="form.created_at_date" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold d-block">Publish</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" v-model="form.publish" id="publish" />
                                    <label class="form-check-label" for="publish">{{ form.publish ? 'Active' : 'Inactive' }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold d-block">Set as Default Page</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" v-model="form.is_default" id="isDefault" />
                                    <label class="form-check-label" for="isDefault">{{ form.is_default ? 'Yes' : 'No' }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Content *</label>
                        <TinyMCE name="content" id="editor-content" :value="form.content" :onChange="onChange" />
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="submit" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Update
                    </button>
                    <router-link to="/pages" class="btn btn-secondary ms-2">Cancel</router-link>
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

const COMPONENT = 'pages-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route = useRoute()
const router = useRouter()

const loading = ref(true)
const saving = ref(false)

const form = reactive({
    name:           '',
    slug:           '',
    url:            '',
    type:           '',
    publish:        false,
    content:        '',
    parent_page_id: null,
    parentObj:      null,
    created_at_date: '',
    is_default:     false,
})

function onChange(val, name) {
    if (name === 'parent_page_id') {
        form.parentObj = val
        form.parent_page_id = val?.id ?? null
    } else {
        form[name] = val
    }
}

function onTypeChange() {
    if (form.type === 'contactus') {
        form.url = `${baseUrl}/contact-us`
    }
}

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/page/${route.params.id}`)
        const p = res.data?.data ?? res.data

        form.name           = p.name ?? ''
        form.slug           = p.slug ?? ''
        form.url            = p.url ?? ''
        form.type           = p.type ?? ''
        form.publish        = Boolean(p.publish)
        form.content        = p.content ?? ''
        form.parent_page_id = p.parent_page_id ?? null
        form.is_default     = Boolean(p.is_default)

        if (p.parent) {
            form.parentObj = { id: p.parent_page_id, name: p.parent.name }
        }

        if (p.created_at) {
            const d = new Date(p.created_at)
            form.created_at_date = d.toISOString().substring(0, 10)
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
        let created_at = ''
        if (form.created_at_date) {
            const [y, m, d] = form.created_at_date.split('-')
            created_at = `${m}/${d}/${y}`
        }

        const res = await http.put(`${baseUrl}/page/${route.params.id}`, {
            name:           form.name,
            slug:           form.slug,
            url:            form.url,
            type:           form.type,
            publish:        form.publish ? 1 : 0,
            content:        form.content,
            parent_page_id: form.parent_page_id,
            created_at,
            default_page_id: form.is_default ? route.params.id : null,
        })
        successHandler(res, COMPONENT)
        router.push('/pages')
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
