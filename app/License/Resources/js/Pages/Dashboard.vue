<template>
    <div class="row" v-if="loading">

        <custom-loader :duration="4000"></custom-loader>
    </div>


            <div class="container-fluid">

                <div class="row">
                    <div class="col-lg-3 col-6">

                        <div class="small-box text-bg-info">
                            <div class="inner">
                                <h3>{{products}}</h3>
                                <p>{{lang('products')}}</p>
                            </div>
                            <div class="small-box-icon">
                                <i class="fas fa-cart-arrow-down"></i>
                            </div>
                            <a class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover" :href="basePath() + '/products'">
                                {{lang('view_all')}}
                                <i class="fas fa-arrow-alt-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">

                        <div class="small-box text-bg-success">
                            <div class="inner">
                                <h3> {{versions}}<sup style="font-size: 20px"></sup></h3>
                                <p>{{lang('versions')}}</p>
                            </div>
                            <div class="small-box-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <router-link class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover" to="/versions/list">
                                {{lang('view_all')}}
                                <i class="far fa-arrow-alt-circle-right"></i>
                            </router-link>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">

                        <div class="small-box text-bg-warning">
                            <div class="inner">
                                <h3> {{licenses}}</h3>
                                <p>{{lang('licenses')}}</p>
                            </div>
                            <div class="small-box-icon">
                                <i class="fas fa-id-card" ></i>
                            </div>
                            <router-link class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"  to="/licenses/list">
                                {{lang('view_all')}}
                                <i class="far fa-arrow-alt-circle-right"></i>
                            </router-link>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">

                        <div class="small-box text-bg-danger">
                            <div class="inner">
                                <h3>{{callbacks}}</h3>
                                <p>{{lang('callbacks')}}</p>
                            </div>
                            <div class="small-box-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <router-link class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover"  to="/callbacks/list">
                                {{lang('view_all')}}
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

</template>

<script>

import axios from 'axios';

import LatestProduct from "./Dashboard/LatestProducts.vue";

import LatestVersion from "./Dashboard/LatestVersions.vue";

import LatestInstallations from "./Dashboard/LatestInstallations.vue";

import LatestCallbacks from "./Dashboard/LatestCallbacks.vue";

import LatestProductReport from "./Dashboard/LatestProductReport.vue";

import ExpiringVersion from "./Dashboard/ExpiringVersion.vue";

import LatestClients from "./Dashboard/LatestClients.vue";

import LatestLicenses from "./Dashboard/LatestLicenses.vue";

import ExpiringSupport from "./Dashboard/ExpiringSupport.vue";

import ExpiringUpdates from "./Dashboard/ExpiringUpdates.vue";

export default {

    name: 'dashboard',

    components: {

        ExpiringVersion,

        LatestProductReport,

        LatestCallbacks,

        LatestInstallations,

        LatestVersion,

        LatestProduct,

        LatestClients,

        LatestLicenses,

        ExpiringSupport,

        ExpiringUpdates

    },

    props : {
    },

    data() {

        return {

            versions: 0,

            clients: '',

            products: 0,

            licenses: 0,

            callbacks: 0,

            latest_products : [],

            latest_versions : [],

            latest_installations : [],

            latest_callbacks : [],

            latest_reports : [],

            expired_versions : [],

            latest_clients : [],

            latest_licenses: [],

            expiring_support: [],

            expiring_update: [],

            items: [],

            widgetKeys: [],

            loading: true, // Add loading state
        };
    },

    mounted() {

        this.getData();
    },

    methods: {

        getData() {

            this.loading = true; // Set loading state to true before making the request

            axios
                .get('/api/admin/dashboarddropdown')
                .then((res) => {
                    this.loading = false; // Set loading state to false after the request is completed
                    const { data } = res.data;

                    if (data) {

                        this.products = data.productsCount;

                        this.licenses = data.licenseCount;

                        this.versions = data.versionsCount;

                        this.callbacks = data.callbacksCount;

                        this.latest_products = data.latestProducts;

                        this.latest_versions = data.latestVersions;

                        this.latest_installations = data.latestInstallations;

                        this.latest_callbacks = data.latestCallbacks;

                        this.latest_reports = data.latestReports;

                        this.expired_versions = data.expiredVersions;

                        this.latest_clients = data.latestClients;

                        this.latest_licenses = data.latestLicenses;

                        this.expiring_support = data.expiringSupport;

                        this.expiring_update = data.expiringUpdates
                    }
                })
                .catch((error) => {

                    this.loading = false; // Set loading state to false if an error occurs
                });

        },

        updateProp(type, data) {

            if(type === 'latestProducts' ){
                this.latest_products = data.latestProducts;
            }
            if(type === 'latestVersions' ){
                this.latest_versions = data.latestVersions;
            }
            if(type === 'latestInstallations' ){
                this.latest_installations = data.latestInstallations;
            }
            if(type === 'latestCallbacks' ){
                this.latest_callbacks = data.latestCallbacks;
            }
            if(type === 'latestReports' ){
                this.latest_reports = data.latestReports;
            }
            if(type === 'expiredVersions' ){
                this.expired_versions = data.expiredVersions;
            }
            if(type === 'latestClients' ){
                this.latest_clients = data.latestClients;
            }
            if(type === 'latestLicenses' ){
                this.latest_licenses = data.latestLicenses;
            }
            if(type === 'expiringSupport' ){
                this.expiring_support = data.expiringSupport;
            }
            if(type === 'expiringUpdates' ){
                this.expiring_update = data.expiringUpdates
            }
        }
    },
};
</script>

<style>
.disabled-link {
    pointer-events: none;
    cursor: default;
    color: #999999;
    text-decoration: none;
    opacity: 0.6;
}

.word_wrap{
    font-size: 1.9rem;
}
.datatable-container {
    max-height: 300px; /* Adjust the maximum height as per your needs */
    overflow-y: auto;
}
</style>
