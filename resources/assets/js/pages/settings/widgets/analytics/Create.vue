<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Add Analytics Widget</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <TextField name="name" label="Name *" :value="form.name" :onChange="onChange" />
                    </div>
                    <div class="col-md-4 mb-3">
                        <TextField name="type" label="Type *" :value="form.type" :onChange="onChange" placeholder="e.g. google-analytics" />
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
                    <TinyMCE name="content" id="editor-widget" :value="form.content" :onChange="onChange" />
                </div>
            </div>

            <div class="card-footer">
                <button class="btn btn-primary" @click="submit" :disabled="saving">
                    <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Create
                </button>
                <RouterLink to="/settings/widgets/analytics" class="btn btn-secondary ms-2">Cancel</RouterLink>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'analytics-create'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router = useRouter()

const saving = ref(false)
const form = reactive({
    name: '', type: '', publish: true, content: '',
    allow_mailchimp: false, allow_social_media: false,
})

function onChange(val, name) { form[name] = val }

async function submit() {
    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/widgets/create`, {
            name:               form.name,
            type:               form.type,
            publish:            form.publish ? 1 : 0,
            content:            form.content,
            allow_mailchimp:    form.allow_mailchimp ? 1 : 0,
            allow_social_media: form.allow_social_media ? 1 : 0,
        })
        successHandler(res, COMPONENT)
        router.push('/settings/widgets/analytics')
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
