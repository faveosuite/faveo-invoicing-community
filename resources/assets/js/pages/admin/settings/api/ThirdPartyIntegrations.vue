<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.third_party_integrations') }}</h4>
            </div>
            <div class="card-body">
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
import { h, ref, reactive } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'third-party-integrations'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const apiUrl  = `${baseUrl}/module-settings`

const router    = useRouter()
const dtRef     = ref(null)
const savingKey = ref(null)
const columns   = ['name', 'description']

async function toggle(row, newVal) {
    // Enabling a module that requires configuration → go to settings page first.
    // The settings page is responsible for saving config and enabling the module.
    if (newVal && row.route) {
        dtRef.value?.refresh()   // revert the switch back to off
        router.push(row.route)
        return
    }

    savingKey.value = row.key
    try {
        const payload = { [row.key]: newVal ? 1 : 0 }
        const res = await http.post(`${baseUrl}/licenseStatus`, payload)
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingKey.value = null
        dtRef.value?.refresh()
    }
}

const tableOptions = reactive({
    headings: {
        name:        'Plugin',
        description: 'Description',
    },
    columnsClasses: {
        name:        'dt-name',
        description: 'dt-description',
    },
    templates: {
        name: (f, row) => {
            const links = []

            // Activate/Deactivate link
            links.push(
                h('a', {
                    href: 'javascript:;',
                    class: 'plugin-link activate-link',
                    onClick: () => toggle(row, !row.enabled)
                }, row.enabled ? 'Deactivate' : 'Activate')
            )

            // Settings link (if enabled and route is present)
            if (row.enabled && row.route) {
                links.push(h('span', { class: 'text-muted mx-1' }, '|'))
                links.push(
                    h(RouterLink, {
                        to: row.route,
                        class: 'plugin-link'
                    }, () => 'Settings')
                )
            }

            return h('div', {}, [
                h('p', { class: 'mb-0 fw-semibold text-dark' }, row.name),
                h('div', { class: 'plugin-action-links mt-1' }, links)
            ])
        },
        description: (f, row) => {
            return h('p', { class: 'mb-0 text-muted' }, row.description)
        },
    },
    sortable:   [],
    filterable: true,
    requestAdapter(data) {
        return {
            'search-query': (data.query ?? '').trim(),
            page:           data.page,
            limit:          data.limit,
        }
    },
})
</script>

<style scoped>
/* Scoped custom classes for plugin action links */
.plugin-action-links {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
}

.plugin-link {
    text-decoration: none;
    color: #2271b1;
    cursor: pointer;
    font-weight: normal;
}

.plugin-link:hover {
    text-decoration: underline;
    color: #135e96;
}

.dt-name {
    width: 250px;
}
</style>
