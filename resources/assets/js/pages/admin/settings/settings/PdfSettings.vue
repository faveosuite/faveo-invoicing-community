<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.pdf_settings') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <TextField
                                name="node_path"
                                :label="__('message.node_path')"
                                :value="form.node_path"
                                :onChange="onChange"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField
                                name="npm_path"
                                :label="__('message.npm_path')"
                                :value="form.npm_path"
                                :onChange="onChange"
                            />
                        </div>
                        <div class="col-md-4">
                            <TextField
                                name="chrome_path"
                                :label="__('message.chrome_path')"
                                :value="form.chrome_path"
                                :onChange="onChange"
                            />
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="submit" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'pdf-settings'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving  = ref(false)

const form = reactive({
    node_path:   '',
    npm_path:    '',
    chrome_path: '',
})

function onChange(val, name) {
    form[name] = val
}

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/pdf-settings`)
        const d = res.data?.data ?? {}
        Object.assign(form, {
            node_path:   d.node_path ?? '',
            npm_path:    d.npm_path ?? '',
            chrome_path: d.chrome_path ?? '',
        })
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
})

async function submit() {
    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/pdf-settings`, { ...form })
        successHandler(res, COMPONENT)
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { saving.value = false }
}
</script>
