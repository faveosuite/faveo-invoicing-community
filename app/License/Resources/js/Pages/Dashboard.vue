<template>
    <div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-info">
                        <div class="inner">
                            <h3>{{ products }}</h3>
                            <p>{{ lang('products') }}</p>
                        </div>
                        <div class="small-box-icon">
                            <i class="fas fa-cart-arrow-down"></i>
                        </div>
                        <a class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover" :href="baseUrl + '/products'">
                            {{ lang('view_all') }}
                            <i class="fas fa-arrow-alt-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-success">
                        <div class="inner">
                            <h3>{{ versions }}<sup style="font-size: 20px"></sup></h3>
                            <p>{{ lang('versions') }}</p>
                        </div>
                        <div class="small-box-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <router-link class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover" to="/versions/list">
                            {{ lang('view_all') }}
                            <i class="far fa-arrow-alt-circle-right"></i>
                        </router-link>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-warning">
                        <div class="inner">
                            <h3>{{ licenses }}</h3>
                            <p>{{ lang('licenses') }}</p>
                        </div>
                        <div class="small-box-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <router-link class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover" to="/licenses/list">
                            {{ lang('view_all') }}
                            <i class="far fa-arrow-alt-circle-right"></i>
                        </router-link>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box text-bg-danger">
                        <div class="inner">
                            <h3>{{ callbacks }}</h3>
                            <p>{{ lang('callbacks') }}</p>
                        </div>
                        <div class="small-box-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <router-link class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover" to="/callbacks/list">
                            {{ lang('view_all') }}
                            <i class="far fa-arrow-alt-circle-right"></i>
                        </router-link>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row g-4 mb-4">
                <div class="shadow-none col-md-6">
                    <latest-product :data="latest_products" @latestProducts="updateProp"></latest-product>
                </div>
                <div class="shadow-none col-md-6">
                    <latest-version :data="latest_versions" @latestVersions="updateProp"></latest-version>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="shadow-none col-md-6">
                    <latest-installations :data="latest_installations" @latestInstallations="updateProp"></latest-installations>
                </div>
                <div class="shadow-none col-md-6">
                    <latest-callbacks :data="latest_callbacks" @latestCallbacks="updateProp"></latest-callbacks>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="shadow-none col-md-6">
                    <latest-product-report :data="latest_reports" @latestReports="updateProp"></latest-product-report>
                </div>
                <div class="shadow-none col-md-6">
                    <expiring-version :data="expired_versions" @expiredVersions="updateProp"></expiring-version>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="shadow-none col-md-6">
                    <latest-clients :data="latest_clients" @latestClients="updateProp"></latest-clients>
                </div>
                <div class="shadow-none col-md-6">
                    <latest-licenses :data="latest_licenses" @latestLicenses="updateProp"></latest-licenses>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="shadow-none col-md-6">
                    <expiring-support :data="expiring_support" @expiringSupport="updateProp"></expiring-support>
                </div>
                <div class="shadow-none col-md-6">
                    <expiring-updates :data="expiring_update" @expiringUpdates="updateProp"></expiring-updates>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/plugins/axios'
import { lang } from '@/helpers/extraLogics'
import LatestProduct from './Dashboard/LatestProducts.vue'
import LatestVersion from './Dashboard/LatestVersions.vue'
import LatestInstallations from './Dashboard/LatestInstallations.vue'
import LatestCallbacks from './Dashboard/LatestCallbacks.vue'
import LatestProductReport from './Dashboard/LatestProductReport.vue'
import ExpiringVersion from './Dashboard/ExpiringVersion.vue'
import LatestClients from './Dashboard/LatestClients.vue'
import LatestLicenses from './Dashboard/LatestLicenses.vue'
import ExpiringSupport from './Dashboard/ExpiringSupport.vue'
import ExpiringUpdates from './Dashboard/ExpiringUpdates.vue'

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const versions = ref(0)
const products = ref(0)
const licenses = ref(0)
const callbacks = ref(0)
const latest_products = ref([])
const latest_versions = ref([])
const latest_installations = ref([])
const latest_callbacks = ref([])
const latest_reports = ref([])
const expired_versions = ref([])
const latest_clients = ref([])
const latest_licenses = ref([])
const expiring_support = ref([])
const expiring_update = ref([])

function getData() {
    axios.get(baseUrl + '/api/admin/dashboarddropdown').then((res) => {
        const { data } = res.data
        if (data) {
            products.value = data.productsCount
            licenses.value = data.licenseCount
            versions.value = data.versionsCount
            callbacks.value = data.callbacksCount
            latest_products.value = data.latestProducts
            latest_versions.value = data.latestVersions
            latest_installations.value = data.latestInstallations
            latest_callbacks.value = data.latestCallbacks
            latest_reports.value = data.latestReports
            expired_versions.value = data.expiredVersions
            latest_clients.value = data.latestClients
            latest_licenses.value = data.latestLicenses
            expiring_support.value = data.expiringSupport
            expiring_update.value = data.expiringUpdates
        }
    }).catch(() => {})
}

function updateProp(type, data) {
    if (type === 'latestProducts') latest_products.value = data.latestProducts
    if (type === 'latestVersions') latest_versions.value = data.latestVersions
    if (type === 'latestInstallations') latest_installations.value = data.latestInstallations
    if (type === 'latestCallbacks') latest_callbacks.value = data.latestCallbacks
    if (type === 'latestReports') latest_reports.value = data.latestReports
    if (type === 'expiredVersions') expired_versions.value = data.expiredVersions
    if (type === 'latestClients') latest_clients.value = data.latestClients
    if (type === 'latestLicenses') latest_licenses.value = data.latestLicenses
    if (type === 'expiringSupport') expiring_support.value = data.expiringSupport
    if (type === 'expiringUpdates') expiring_update.value = data.expiringUpdates
}

onMounted(() => {
    getData()
})
</script>
