<template>
    <div class="card card-light">
        <div class="card-header">
            <h4 class="card-title">{{ 'Latest Installations' }}</h4>
            <div class="card-tools">
                <button type="button" :disabled="loading" class="btn btn-tool" data-card-widget="refresh"
                        @click="getData()" v-tooltip="lang('refresh')">
                    <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="datatable-container my-table-container">
                <v-client-table
                    v-if="data"
                    :columns="columns"
                    :data="data"
                    :options="options"
                    :key="counter"
                >
                    <template v-slot:product_status="props">
                        <span :class="props.row.product_status ? 'btn btn-success btn-xs' : 'btn btn-secondary btn-xs'">
                            {{ props.row.product_status ? 'Active' : 'Inactive' }}
                        </span>
                    </template>

                    <template v-slot:installation_domain="props">
                        <a v-if="props.row.installation_domain" :href="'https://' + props.row.installation_domain" target="_blank">{{ props.row.installation_domain }}</a>
                        <span v-else>----</span>
                    </template>

                    <template v-slot:license="props">
                        <router-link v-if="props.row.license_code && props.row.license_id" :to="'/licenses/' + props.row.license_id + '/view'">{{ props.row.license_code.match(/.{1,4}/g).join('-') }}</router-link>
                        <span v-else>----</span>
                    </template>

                    <template v-slot:installation_status="props">
                        <span :style="{ color: props.row.installation_status ? 'green' : 'red' }">
                            {{ props.row.installation_status ? 'Active' : 'Inactive' }}
                        </span>
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

const emit = defineEmits(['latestInstallations'])

const columns = ['license', 'installation_ip', 'installation_date', 'installation_domain', 'installation_status']
const counter = ref(0)
const loading = ref(false)

const options = reactive({
    columnsClasses: {
        license: 'license_code',
        installation_ip: 'installation_ip',
        installation_date: 'installation_date',
        installation_domain: 'installation_domain',
        installation_status: 'installation_status'
    },
    templates: {
        installation_ip(h, row) {
            return row.installation_ip ? row.installation_ip : '----'
        },
        installation_date(h, row) {
            return row.installation_date
        },
    },
    headings: {
        license: 'License Code',
        installation_date: 'Installation Date',
        installation_ip: 'IP',
        installation_domain: 'Domain',
        installation_status: 'Status'
    },
})

function getData() {
    loading.value = true
    axios.get(baseUrl + '/api/admin/dashboarddropdown').then((res) => {
        loading.value = false
        const { data } = res.data
        if (data) {
            emit('latestInstallations', 'latestInstallations', data)
        }
    }).catch(() => {
        loading.value = false
    })
}
</script>
