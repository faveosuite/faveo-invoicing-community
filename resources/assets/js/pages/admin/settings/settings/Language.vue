<template>
    <div>
        <AppAlert componentName="language-index" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.language') }}</h4>
            </div>
            <div class="card-body">
                <DataTable
                    ref="dtRef"
                    :url="`${baseUrl}/languages`"
                    :dataColumns="columns"
                    :option="tableOptions"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { h, ref, reactive } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import LanguageTableActions from './LanguageTableActions.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const COMPONENT = 'language-index'
const baseUrl = useBaseUrl()

const dtRef          = ref(null)
const toggling       = ref(null)
const settingDefault = ref(null)

async function toggleStatus(row) {
    toggling.value = row.locale
    try {
        const res = await http.post(`/language-toggle`, {
            locale: row.locale,
            status: row.status ? 0 : 1,
        })
        successHandler(res, COMPONENT)
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        toggling.value = null
    }
}

async function setDefault(row) {
    settingDefault.value = row.locale
    try {
        const res = await http.post(`/language-set-default`, { locale: row.locale })
        successHandler(res, COMPONENT)
        dtRef.value?.refresh()
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        settingDefault.value = null
    }
}

const columns = ['name', 'translation', 'locale', 'default', 'action']

const tableOptions = reactive({
    headings: {
        name:        __('message.language'),
        translation: __('message.native_name'),
        locale:      __('message.iso_code'),
        default:     __('message.system_default'),
        action:      __('message.action'),
    },
    columnsClasses: {
        name: 'dt-name',
        translation: 'dt-text',
        locale: 'dt-code',
        default: 'dt-status',
        action: 'dt-action',
    },
    templates: {
        name:        (f, row) => row.name        || '—',
        translation: (f, row) => row.translation || '—',
        locale:  (f, row) => row.locale || '—',
        default: (f, row) => row.is_default
            ? h('span', { class: 'badge bg-success' }, __('message.yes'))
            : h('span', { class: 'badge bg-secondary' }, __('message.no')),
        action: (f, row) => h(LanguageTableActions, {
            status:         row.status,
            isDefault:      Boolean(row.is_default),
            toggling:       toggling.value === row.locale,
            settingDefault: settingDefault.value === row.locale,
            onToggle:       () => toggleStatus(row),
            onSetDefault:   () => setDefault(row),
        }),
    },
    sortable:   ['name', 'translation', 'locale'],
    filterable: true,
    requestAdapter: makeRequestAdapter('name'),
    responseAdapter({ data }) {
        const res = data?.data ?? {}
        return {
            data:  res.data  ?? [],
            count: res.total ?? 0,
        }
    },
    orderBy: { column: 'name', ascending: true },
})
</script>
