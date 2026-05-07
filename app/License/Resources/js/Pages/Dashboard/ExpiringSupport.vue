<template>
    <div class="card card-light">
        <div class="card-header">
            <h4 class="card-title">{{ lang('expiring_support') }}</h4>
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
                    <template v-slot:license_status="props">
                        <span :style="{ color: props.row.license_status ? 'green' : 'red' }">
                            {{ props.row.license_status ? lang('active') : lang('inactive') }}
                        </span>
                    </template>

                    <template v-slot:license_code="props">
                        <router-link v-if="props.row.license_code && props.row.license_id" :to="'/licenses/' + props.row.license_id + '/view'">{{ props.row.license_code.match(/.{1,4}/g).join('-') }}</router-link>
                        <span v-else>----</span>
                    </template>

                    <template v-slot:product="props">
                        <a v-if="props.row.product_title && props.row.product_id" :href="baseUrl + '/products/' + props.row.product_id + '/edit'">{{ props.row.product_title }}</a>
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

const emit = defineEmits(['expiringSupport'])

const columns = ['license_code', 'product', 'license_date', 'license_support_date', 'license_status']
const counter = ref(0)
const loading = ref(false)

const options = reactive({
    columnsClasses: {
        license_code: 'license_code',
        product: 'product_title',
        license_date: 'license_date',
        license_support_date: 'license_support_date',
        license_status: 'license_status',
    },
    templates: {
        license_date(h, row) {
            return row.license_date
        },
        license_support_date(h, row) {
            return row.license_support_date
        },
    },
    headings: {
        license_code: lang('license_code'),
        product: lang('product'),
        license_date: lang('activation_date'),
        license_support_date: lang('support'),
        license_status: lang('status')
    },
})

function getData() {
    loading.value = true
    axios.get(baseUrl + '/api/admin/dashboarddropdown').then((res) => {
        loading.value = false
        const { data } = res.data
        if (data) {
            emit('expiringSupport', 'expiringSupport', data)
        }
    }).catch(() => {
        loading.value = false
    })
}
</script>
