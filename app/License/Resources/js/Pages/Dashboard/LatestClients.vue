<template>
    <div class="card card-light">
        <div class="card-header">
            <h4 class="card-title">{{ lang('latest_clients') }}</h4>
            <div class="card-tools">
                <button type="button" :disabled="loading" class="btn btn-tool" data-card-widget="refresh"
                        @click="getData()" v-tooltip="lang('refresh')">
                    <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="datatable-container">
                <v-client-table
                    v-if="data"
                    :columns="columns"
                    :data="data"
                    :options="options"
                    :key="counter"
                >
                    <template v-slot:client_status="props">
                        <span :style="{ color: props.row.client_status ? 'green' : 'red' }">
                            {{ props.row.client_status ? lang('active') : lang('inactive') }}
                        </span>
                    </template>

                    <template v-slot:client_email="props">
                        <a v-if="props.row.client_email" :href="baseUrl + '/clients/' + props.row.client_id">{{ props.row.client_email }}</a>
                        <span v-else>----</span>
                    </template>

                    <template v-slot:full_name="props">
                        <a v-if="props.row.full_name" :href="baseUrl + '/clients/' + props.row.client_id">{{ props.row.full_name }}</a>
                        <span v-else>----</span>
                    </template>
                </v-client-table>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import axios from '@/plugins/axios'
import { lang } from '@/helpers/extraLogics'

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const props = defineProps({
    data: { type: Array, default: () => [] }
})

const emit = defineEmits(['latestClients'])

const columns = ['full_name', 'client_email', 'client_active_date', 'license_count', 'client_status']
const counter = ref(0)
const loading = ref(false)

const options = reactive({
    columnsClasses: {
        full_name: 'full_name',
        client_email: 'client_email',
        client_active_date: 'client_active_date',
        license_count: 'license_count',
        client_status: 'client_status',
    },
    templates: {
        client_active_date(h, row) {
            return row.client_active_date
        },
    },
    headings: {
        full_name: lang('name'),
        client_email: lang('email'),
        client_active_date: lang('created_at'),
        license_count: lang('licenses'),
        client_status: lang('status')
    },
})

function getData() {
    loading.value = true
    axios.get(baseUrl + '/api/admin/dashboarddropdown').then((res) => {
        loading.value = false
        const { data } = res.data
        if (data) {
            emit('latestClients', 'latestClients', data)
        }
    }).catch(() => {
        loading.value = false
    })
}
</script>
