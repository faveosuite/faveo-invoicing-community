<template>
    <div class="card card-light">
        <div class="card-header">
            <h4 class="card-title">{{ 'Latest Versions' }}</h4>
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
                            {{ props.row.version_status ? 'Active' : 'Inactive' }}
                        </span>
                    </template>

                    <template v-slot:version_number="props">
                        <router-link :to="'/versions/' + props.row.id + '/view'">{{ props.row.version_number }}</router-link>
                    </template>

                    <template v-slot:product_title="props">
                        <a v-if="props.row.product_title" :href="baseUrl + '/products/' + props.row.product_id + '/edit'">{{ props.row.product_title }}</a>
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

const emit = defineEmits(['latestVersions'])

const columns = ['product_title', 'version_number', 'version_date', 'version_status']
const counter = ref(0)
const loading = ref(false)

const options = reactive({
    columnsClasses: {
        product_title: 'product_title',
        version_number: 'version_number',
        version_date: 'version_date',
        version_status: 'version_status',
    },
    templates: {
        version_date(h, row) {
            return row.version_date
        },
        version_expire_date(h, row) {
            return row.version_expire_date
        },
    },
    headings: {
        product_title: 'Product',
        version_date: 'Released Date',
        version_number: 'Version',
        version_status: 'Status'
    },
})

function getData() {
    loading.value = true
    axios.get(baseUrl + '/api/admin/dashboarddropdown').then((res) => {
        loading.value = false
        const { data } = res.data
        if (data) {
            emit('latestVersions', 'latestVersions', data)
        }
    }).catch(() => {
        loading.value = false
    })
}
</script>
