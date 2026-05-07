<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Add Social Media</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <TextField name="name" label="Name *" :value="form.name" :onChange="onChange" placeholder="e.g. Twitter" />
                    </div>
                    <div class="col-md-4 mb-3">
                        <TextField name="link" label="Link *" :value="form.link" :onChange="onChange" placeholder="https://twitter.com/..." />
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <button class="btn btn-primary" @click="submit" :disabled="saving">
                    <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Create
                </button>
                <RouterLink to="/settings/widgets/social-media" class="btn btn-secondary ms-2">Cancel</RouterLink>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'social-media-create'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router = useRouter()

const saving = ref(false)
const form = reactive({ name: '', link: '' })

function onChange(val, name) { form[name] = val }

async function submit() {
    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/social-media/create`, form)
        successHandler(res, COMPONENT)
        router.push('/settings/widgets/social-media')
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
