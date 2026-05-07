<template>
    <div class="card card-light">
        <div class="card-header">
            <h4 class="card-title">{{ 'Latest Products' }}</h4>
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
                    <template v-slot:product_status="props">
                        <span :style="{ color: props.row.product_status ? 'green' : 'red' }">
                            {{ props.row.product_status ? 'Active' : 'Inactive' }}
                        </span>
                    </template>

                    <template v-slot:product_title="props">
                        <a :href="baseUrl + '/products/' + props.row.id + '/edit'">{{ props.row.product_title }}</a>
                    </template>

                    <template v-slot:versions="props">
                        <span>{{ props.row.versions }}</span>
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

const emit = defineEmits(['latestProducts'])

const columns = ['product_title', 'product_sku', 'versions', 'installations_count', 'licenses_count', 'product_status']
const counter = ref(0)
const loading = ref(false)

const options = reactive({
    columnsClasses: {
        product_title: 'product_title',
        product_sku: 'product_sku',
        product_date: 'product_date',
        product_status: 'product_status',
    },
    templates: {
        product_date(h, row) {
            return row.product_date
        }
    },
    headings: {
        product_title: 'Product',
        product_sku: 'SKU',
        versions: 'Versions',
        licenses_count: 'Licenses',
        installations_count: 'Installations',
        product_status: 'Status'
    },
})

function getData() {
    loading.value = true
    axios.get(baseUrl + '/api/admin/dashboarddropdown').then((res) => {
        loading.value = false
        const { data } = res.data
        if (data) {
            emit('latestProducts', 'latestProducts', data)
        }
    }).catch(() => {
        loading.value = false
    })
}
</script>
