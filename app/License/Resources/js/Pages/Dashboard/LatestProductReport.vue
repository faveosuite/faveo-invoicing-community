<template>
    <div class="card card-light">
        <div class="card-header">
            <h4 class="card-title">{{ lang('latest_product_report') }}</h4>
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
                    <template v-slot:report_status="props">
                        <span :style="{ color: props.row.report_status ? 'green' : 'red' }">
                            {{ props.row.report_status ? lang('active') : lang('inactive') }}
                        </span>
                    </template>

                    <template v-slot:license="props">
                        <router-link v-if="props.row.license_code && props.row.license_id" :to="'/licenses/' + props.row.license_id + '/view'">{{ props.row.license_code.match(/.{1,4}/g).join('-') }}</router-link>
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

const emit = defineEmits(['latestReports'])

const columns = ['report_text', 'report_date_time', 'report_status']
const counter = ref(0)
const loading = ref(false)

const options = reactive({
    columnsClasses: {
        report_text: 'report_status',
        report_date_time: 'report_date_time',
        report_status: 'report_status'
    },
    templates: {
        report_date_time(h, row) {
            return row.report_date_time
        },
    },
    headings: {
        report_text: lang('report'),
        report_date_time: lang('date'),
        report_status: lang('status')
    },
})

function getData() {
    loading.value = true
    axios.get(baseUrl + '/api/admin/dashboarddropdown').then((res) => {
        loading.value = false
        const { data } = res.data
        if (data) {
            emit('latestReports', 'latestReports', data)
        }
    }).catch(() => {
        loading.value = false
    })
}
</script>
