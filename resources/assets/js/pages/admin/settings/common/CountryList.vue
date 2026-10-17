<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.countries') }}</h4>
            </div>
            <div class="card-body">
                <DataTable
                    :url="apiUrl"
                    :dataColumns="columns"
                    :option="tableOptions"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue'
import { makeRequestAdapter } from '@/helpers/tableUtils'

const COMPONENT = 'country-list'
const apiUrl = `/get-country`

const columns = ['country', 'count']

const tableOptions = reactive({
    headings: {
        country: __('message.country'),
        count:   __('message.users'),
    },
    columnsClasses: {
        country: 'dt-name',
        count: 'dt-code',
    },
    templates: {
        country: (f, row) => row.country || '—',
        count: (f, row) => row.count ?? 0,
    },
    sortable: ['country', 'count'],
    filterable: true,
    requestAdapter: makeRequestAdapter('country_name', null, { country: 'country_name', count: 'users_count' }),
    orderBy: { column: 'country', ascending: true },
})
</script>
