<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.page_settings') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <StaticSelect
                                name="default_page_id"
                                :label="__('message.default-page')"
                                :hint="__('message.tt_default_page')"
                                :elements="pageOptions"
                                :value="form.default_page_id"
                                :onChange="(val) => form.default_page_id = val"
                                :hideEmptySelect="true"
                            />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold d-block">
                                {{ __('message.demo_request_button') }}
                                <ToolTip :message="__('message.tt_demo_request_button')" size="small" />
                            </label>
                            <Switch name="status" :value="form.status" :onChange="(val) => form.status = val" />
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
import Switch from '@/components/Reusable/FormField/Switch.vue'
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'
import ToolTip from '@/components/Reusable/Tooltip.vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { __ } from '@/plugins/i18n'

const COMPONENT = 'page-settings'

const loading = ref(true)
const saving = ref(false)

// '' represents "My Invoices" (no custom page picked) — never sent to the
// backend as-is, translated to null in save(). Custom pages are capped at
// 3 (see PageController::createPage), so no pagination/search is needed.
const pageOptions = ref([{ id: '', name: __('message.my_invoices') }])

const form = reactive({
    status: false,
    default_page_id: '',
})

onMounted(async () => {
    try {
        const [settingsRes, pagesRes] = await Promise.all([
            http.get('/page-settings'),
            http.get('/pages'),
        ])

        const pages = pagesRes.data?.data?.data ?? []
        pageOptions.value = [
            { id: '', name: __('message.my_invoices') },
            ...pages.map(p => ({ id: p.id, name: p.name })),
        ]

        const d = settingsRes.data?.data ?? {}
        form.status = d.status ?? false
        form.default_page_id = d.default_page_id ?? ''
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save() {
    saving.value = true
    try {
        const res = await http.post('/page-settings', {
            status: form.status,
            default_page_id: form.default_page_id || null,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
