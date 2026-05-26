<template>
    <div class="header-body border-top-0 box-shadow-none">
        <div class="header-container header-container-md container">
            <div class="header-row">

                <!-- Logo -->
                <div class="header-column">
                    <div class="header-row">
                        <div class="header-logo">
                            <RouterLink :to="isAuthenticated ? '/dashboard' : '/'">
                                <img v-if="logoUrl" :src="logoUrl" alt="Logo" width="100" height="48"
                                     data-sticky-width="82" data-sticky-height="40" data-sticky-top="0">
                                <span v-else class="brand-text fw-bold">{{ appCompany }}</span>
                            </RouterLink>
                        </div>
                    </div>
                </div>

                <!-- Nav -->
                <div class="header-column justify-content-end">
                    <div class="header-row">
                        <div class="header-nav header-nav-line header-nav-bottom-line header-nav-bottom-line-no-transform header-nav-bottom-line-active-text-dark header-nav-bottom-line-effect-1 order-2 order-lg-1">
                            <div class="header-nav-main header-nav-main-square header-nav-main-dropdown-no-borders header-nav-main-effect-2 header-nav-main-sub-effect-1">
                                <nav class="collapse">
                                    <ul class="nav nav-pills" id="mainNav">

                                        <!-- My Account dropdown (authenticated) -->
                                        <li v-if="isAuthenticated" class="dropdown">
                                            <a class="dropdown-item dropdown-toggle" href="javascript:;">
                                                {{ __('message.my_account') }}
                                            </a>
                                            <ul class="dropdown-menu">
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
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <RouterLink to="/profile" class="dropdown-item">
                                                        {{ __('message.my_profile') }}
                                                    </RouterLink>
                                                </li>
                                                <li>
                                                    <RouterLink to="/profile/change-password" class="dropdown-item">
                                                        {{ __('message.change_password') }}
                                                    </RouterLink>
                                                </li>
                                                <li>
                                                    <RouterLink to="/profile/2fa" class="dropdown-item">
                                                        {{ __('message.two_factor_auth') }}
                                                    </RouterLink>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a :href="logoutUrl" class="dropdown-item">
                                                        {{ __('message.logout') }}
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>

                                        <li v-else>
                                            <a class="dropdown-item" :href="loginUrl">
                                                {{ __('message.sign-up') }}
                                            </a>
                                        </li>

                                    </ul>
                                </nav>
                            </div>

                            <button class="btn header-btn-collapse-nav"
                                    data-bs-toggle="collapse"
                                    data-bs-target=".header-nav-main nav">
                                <i class="fas fa-bars"></i>
                            </button>
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
