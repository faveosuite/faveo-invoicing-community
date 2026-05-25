<template>
    <div class="header-body border-0 border-bottom-light">
        <div class="header-container container-fluid p-0">
            <div class="header-row">

                <!-- Logo column -->
                <div class="header-column header-column-border-right flex-grow-0 d-sticky-header-active-none">
                    <div class="header-row">
                        <div id="main-logo" class="header-logo p-relative top-sm-40 top-30 m-0" style="width:250px;height:150px;text-align:center">
                            <RouterLink :to="isAuthenticated ? '/dashboard' : '/'">
                                <img v-if="logoUrl" :src="logoUrl" alt="Logo" width="130" height="75">
                                <span v-else class="brand-text fw-bold">{{ appCompany }}</span>
                            </RouterLink>
                        </div>
                    </div>
                </div>

                <!-- Nav column -->
                <div class="header-column">

                    <!-- Top info bar -->
                    <div class="border-bottom-light w-100">
                        <div class="hstack gap-4 px-4 py-2 font-weight-semi-bold d-none d-lg-flex">
                            <div class="ms-auto"></div>
                            <div class="vr opacity-2 d-none d-lg-inline-block"></div>
                        </div>
                    </div>

                    <!-- Main navigation -->
                    <div class="header-row h-100">
                        <div class="hstack h-100 w-100">
                            <div class="h-100 w-100 w-xl-auto">
                                <div class="header-nav header-nav-links h-100 justify-content-end justify-content-lg-start me-4 me-lg-0 ms-lg-3">
                                    <div class="header-nav-main header-nav-main-square header-nav-main-dropdown-no-borders header-nav-main-text-capitalize header-nav-main-text-size-4 header-nav-main-arrows header-nav-main-effect-5">
                                        <nav class="collapse">
                                            <ul class="nav nav-pills" id="mainNav">

                                                <!-- Sticky logo (shown only when header shrinks) -->
                                                <li class="d-sticky-header-negative-none" style="display:none">
                                                    <RouterLink class="nav-link" :to="isAuthenticated ? '/dashboard' : '/'">
                                                        <img v-if="logoUrl" :src="logoUrl" alt="Logo" width="75" height="50">
                                                    </RouterLink>
                                                </li>

                                                <!-- My Account dropdown -->
                                                <li v-if="isAuthenticated" class="dropdown">
                                                    <a class="nav-link dropdown-toggle" href="javascript:;">
                                                        &nbsp;{{ __('message.my_account') }}&nbsp;
                                                    </a>
                                                    <ul class="dropdown-menu border-light mt-n1">
                                                        <li>
                                                            <RouterLink to="/dashboard" class="dropdown-item">
                                                                {{ __('message.dashboard') }}
                                                            </RouterLink>
                                                        </li>
                                                        <li>
                                                            <RouterLink to="/orders" class="dropdown-item">
                                                                {{ __('message.my_orders') }}
                                                            </RouterLink>
                                                        </li>
                                                        <li>
                                                            <RouterLink to="/invoices" class="dropdown-item">
                                                                {{ __('message.my_invoices') }}
                                                            </RouterLink>
                                                        </li>
                                                        <li>
                                                            <RouterLink to="/profile" class="dropdown-item">
                                                                {{ __('message.my_profile') }}
                                                            </RouterLink>
                                                        </li>
                                                        <li>
                                                            <a :href="logoutUrl" class="dropdown-item">
                                                                {{ __('message.logout') }}
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </li>

                                                <li v-else>
                                                    <a class="nav-link" :href="loginUrl">
                                                        {{ __('message.sign-up') }}
                                                    </a>
                                                </li>

                                            </ul>
                                        </nav>
                                    </div>

                                    <!-- Mobile toggle -->
                                    <button class="btn header-btn-collapse-nav" data-bs-toggle="collapse" data-bs-target=".header-nav-main nav">
                                        <i class="fas fa-bars"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const el = document.getElementById('app-client')

const isAuthenticated = computed(() => el?.dataset?.authenticated === 'true')
const logoUrl         = computed(() => el?.dataset?.appLogo ?? '')
const appCompany      = computed(() => el?.dataset?.company ?? '')
const baseUrl         = computed(() => el?.dataset?.baseUrl ?? '')
const logoutUrl       = computed(() => `${baseUrl.value}/auth/logout`)
const loginUrl        = computed(() => `${baseUrl.value}/login`)
</script>
