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
import { RouterLink } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const COMPONENT = 'third-party-integrations'
const apiUrl  = `/module-settings`

const dtRef     = ref(null)
const savingKey = ref(null)
const columns   = ['name', 'description']

async function toggle(row, newVal) {
    savingKey.value = row.key
    try {
        const payload = { [row.key]: newVal ? 1 : 0 }
        const res = await http.post(`/licenseStatus`, payload)
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

            // Settings-only modules (e.g. Zoho) are configured entirely on their
            // own page (per-platform OAuth), so they expose just a Settings link.
            if (row.settings_only) {
                links.push(
                    h(RouterLink, {
                        to: row.route,
                        class: 'plugin-link'
                    }, () => 'Settings')
                )

                return h('div', {}, [
                    h('p', { class: 'mb-0 fw-semibold text-dark' }, row.name),
                    h('div', { class: 'plugin-action-links mt-1' }, links)
                ])
            }

            // Activate/Deactivate link
            links.push(
                h('a', {
                    href: '#',
                    class: 'plugin-link activate-link',
                    onClick: (e) => { e.preventDefault(); toggle(row, !row.enabled) }
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
    requestAdapter: makeRequestAdapter('name'),
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
