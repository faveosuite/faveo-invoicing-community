<template>
    <div class="card card-light">
        <div class="card-header">
            <h4 class="card-title">{{ lang('expiring_version') }}</h4>
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
                    <template v-slot:version_status="props">
                        <span :style="{ color: props.row.version_status ? 'green' : 'red' }">
                            {{ props.row.version_status ? lang('active') : lang('inactive') }}
                        </span>
                    </template>

                    <template v-slot:version_number="props">
                        <router-link v-if="props.row.version_number" :to="'/versions/' + props.row.id + '/view'">{{ props.row.version_number }}</router-link>
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

const emit = defineEmits(['expiredVersions'])

const columns = ['version_number', 'version_date', 'version_expire_date', 'version_status']
const counter = ref(0)
const loading = ref(false)

const options = reactive({
    columnsClasses: {
        version_number: 'version_number',
        version_date: 'version_date',
        version_expire_date: 'version_expire_date',
        version_status: 'version_status'
    },
    templates: {
        version_date(h, row) {
            return row.version_date
        },
        version_expire_date(h, row) {
            return row.version_expire_date || '----'
        },
    },
    headings: {
        version_number: lang('version'),
        version_date: lang('version_date'),
        version_expire_date: lang('version_expire_date'),
        version_status: lang('status')
    },
})

function getData() {
    loading.value = true
    axios.get(baseUrl + '/api/admin/dashboarddropdown').then((res) => {
        loading.value = false
        const { data } = res.data
        if (data) {
            emit('expiredVersions', 'expiredVersions', data)
        }
    }).catch(() => {
        loading.value = false
    })
}
</script>
