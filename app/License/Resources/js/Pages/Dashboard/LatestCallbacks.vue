<template>
    <div class="card card-light">
        <div class="card-header">
            <h4 class="card-title">{{ lang('latest_callbacks') }}</h4>
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
                    <template v-slot:callback_status="props">
                        <span :style="{ color: props.row.callback_status ? 'green' : 'red' }">
                            {{ props.row.callback_status ? lang('active') : lang('inactive') }}
                        </span>
                    </template>

                    <template v-slot:callback_domain="props">
                        <a v-if="props.row.callback_domain" :href="'https://' + props.row.callback_domain" target="_blank">{{ props.row.callback_domain }}</a>
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

const emit = defineEmits(['latestCallbacks'])

const columns = ['callback_domain', 'callback_ip', 'callback_date_time', 'callback_status']
const counter = ref(0)
const loading = ref(false)

const options = reactive({
    columnsClasses: {
        callback_domain: 'callback_domain',
        callback_date_time: 'callback_date_time',
        callback_ip: 'callback_ip',
        callback_status: 'callback_status',
    },
    templates: {
        callback_date_time(h, row) {
            return row.callback_date_time
        },
        callback_ip(h, row) {
            return row.callback_ip ? row.callback_ip : '----'
        },
    },
    headings: {
        callback_domain: lang('domain'),
        callback_date_time: lang('date'),
        callback_ip: lang('ip'),
        callback_status: lang('status')
    },
})

function getData() {
    loading.value = true
    axios.get(baseUrl + '/api/admin/dashboarddropdown').then((res) => {
        loading.value = false
        const { data } = res.data
        if (data) {
            emit('latestCallbacks', 'latestCallbacks', data)
        }
    }).catch(() => {
        loading.value = false
    })
}
</script>
