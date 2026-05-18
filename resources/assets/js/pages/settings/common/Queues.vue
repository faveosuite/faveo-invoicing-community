<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <!-- Queue list -->
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.queue') }}</h4>
            </div>
            <div class="card-body">
                <!-- Cron command – only shown when Database queue is active -->
                <div v-if="activeQueueName === 'Database'" class="card p-3 bg-light mb-3">
                    <div class="row align-items-center g-2">
                        <div class="col-sm-2">
                            <span class="fs-5 text-muted fw-bold">* &nbsp;* &nbsp;* &nbsp;* &nbsp;*</span>
                        </div>
                        <div class="col-sm-4">
                            <SelectField
                                v-if="phpPath !== 'other'"
                                name="php_path"
                                label=""
                                :elements="phpPathOptions"
                                :value="phpPathOptions.find(o => o.id === phpPath) ?? null"
                                :onChange="(val) => phpPath = val?.id ?? ''"
                                :clearable="false"
                                :searchable="false"
                                :placeholder="__('message.specify_php_executable')"
                            />
                            <div v-else class="input-group">
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="customPhpPath"
                                    :placeholder="__('message.specify_php_executable')"
                                />
                                <button class="btn btn-outline-secondary" type="button" @click="clearPhpPath">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-sm-5">
                            <code class="text-break" style="color: inherit;">{{ cronCommand }}</code>
                        </div>
                        <div class="col-sm-1 text-center">
                            <span
                                v-if="!copying"
                                style="cursor:pointer"
                                :title="__('message.verify_and_copy_command')"
                                @click="copyCommand"
                            >
                                <i class="far fa-clipboard fa-2x text-secondary"></i>
                            </span>
                            <span v-else>
                                <i class="fas fa-circle-notch fa-spin fa-2x text-secondary"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <DataTable
                    ref="dtRef"
                    :url="apiUrl"
                    :dataColumns="columns"
                    :option="tableOptions"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { h, ref, computed, reactive } from 'vue'
import { RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'queues'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl  = `${baseUrl}/queue/list`

const dtRef           = ref(null)
const activating      = ref(null)
const copying         = ref(false)
const cronPath        = ref('')
const phpPaths        = ref([])
const phpPath         = ref('')
const customPhpPath   = ref('')
const activeQueueName = ref('')

const phpPathOptions = computed(() => [
    ...phpPaths.value.map(p => ({ id: p, name: p })),
    { id: 'other', name: __('message.other') },
])

const cronCommand = computed(() => {
    const path = phpPath.value === 'other' ? customPhpPath.value : phpPath.value
    return path
        ? `${path} -q ${cronPath.value} queue:work`
        : `${cronPath.value} queue:work`
})

function clearPhpPath() {
    phpPath.value       = ''
    customPhpPath.value = ''
}

async function copyCommand() {
    const path = phpPath.value === 'other' ? customPhpPath.value : phpPath.value
    copying.value = true
    try {
        const res = await http.post(`${baseUrl}/verify-php-path`, { path })
        await navigator.clipboard.writeText(`* * * * * ${cronCommand.value}`)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        copying.value = false
    }
}

async function activate(id) {
    activating.value = id
    try {
        const res = await http.post(`${baseUrl}/queue/${id}/activate`)
        successHandler(res, COMPONENT)
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        activating.value = null
    }
}

const columns = ['name', 'status', 'action']

const tableOptions = reactive({
    headings: {
        name:   __('message.name_page'),
        status: __('message.status'),
        action: __('message.action'),
    },
    templates: {
        name: (f, row) => {
            const id   = row.QueueDetails?.id
            const link = row.QueueDetails?.name?.link
            const text = row.QueueDetails?.name?.text ?? '—'
            if (link && id) return h(RouterLink, { to: `/settings/common/queue/${id}` }, () => text)
            return text
        },
        status: (f, row) => h('span', {
            class: row.QueueDetails?.status?.code === 1 ? 'btn btn-success btn-sm' : 'btn btn-danger btn-sm',
            style: 'cursor:default',
        }, row.QueueDetails?.status?.label ?? '—'),
        action: (f, row) => {
            const id         = row.QueueDetails?.id
            const isActivated = row.QueueDetails?.action?.type === 'activated'
            const busy        = activating.value === id
            return h('button', {
                class:    'btn btn-sm btn-primary',
                disabled: isActivated || busy,
                onClick:  () => activate(id),
            }, busy
                ? [h('span', { class: 'spinner-border spinner-border-sm me-1' }), __('message.activate')]
                : [h('i', { class: 'fas fa-check-circle me-1' }), __('message.activate')]
            )
        },
    },
    sortable:   [],
    filterable: false,
    requestAdapter(data) {
        return {
            'sort_field':   data.orderBy  ?? 'name',
            'sort_order':   data.ascending ? 'asc' : 'desc',
            'search-query': (data.query   ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
    responseAdapter({ data }) {
        const d = data?.data ?? {}
        cronPath.value        = d.cron_path       ?? ''
        phpPaths.value        = d.php_paths        ?? []
        if (!phpPath.value && phpPaths.value.length) phpPath.value = phpPaths.value[0]
        activeQueueName.value = d.active_queue?.name ?? ''
        if (activeQueueName.value !== 'Database') clearPhpPath()
        const queues = d.queues ?? {}
        return {
            data:  queues.data  ?? [],
            count: queues.total ?? queues.data?.length ?? 0,
        }
    },
    orderBy: { column: 'name', ascending: true },
})
</script>
