<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Footer Widget</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else-if="!widgetId">
                <div class="card-body">
                    <div class="alert alert-info">No footer widget found. Create one using the Analytics Widgets section with type <code>footer</code>.</div>
                </div>
            </template>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <TextField name="name" label="Name *" :value="form.name" :onChange="onChange" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Published</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" v-model="form.publish" />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" v-model="form.allow_mailchimp" id="allowMailchimp" />
                                <label class="form-check-label" for="allowMailchimp">Allow Mailchimp</label>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" v-model="form.allow_social_media" id="allowSocialMedia" />
                                <label class="form-check-label" for="allowSocialMedia">Allow Social Media</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Content</label>
                        <TinyMCE name="content" id="editor-footer" :value="form.content" :onChange="onChange" />
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="submit" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Update
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

const COMPONENT = 'footer-widget'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving = ref(false)
const widgetId = ref(null)
const form = reactive({
    name: '', publish: true, content: '',
    allow_mailchimp: false, allow_social_media: false,
})

function onChange(val, name) { form[name] = val }

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/widgets/list`, { params: { limit: 200 } })
        const pages = res.data?.data?.pages?.data ?? []
        const footer = pages.find(w => w.type === 'footer')
        if (footer) {
            widgetId.value = footer.id
            const detailRes = await http.get(`${baseUrl}/widgets/show/${footer.id}`)
            const d = detailRes.data?.data?.widget ?? {}
            Object.assign(form, {
                name:               d.name ?? '',
                publish:            Boolean(d.publish),
                content:            d.content ?? '',
                allow_mailchimp:    Boolean(d.allow_mailchimp),
                allow_social_media: Boolean(d.allow_social_media),
            })
        }
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
})

async function submit() {
    if (!widgetId.value) return
    saving.value = true
    try {
        const res = await http.put(`${baseUrl}/widgets/update/${widgetId.value}`, {
            name:               form.name,
            type:               'footer',
            publish:            form.publish ? 1 : 0,
            content:            form.content,
            allow_mailchimp:    form.allow_mailchimp ? 1 : 0,
            allow_social_media: form.allow_social_media ? 1 : 0,
        })
        successHandler(res, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
