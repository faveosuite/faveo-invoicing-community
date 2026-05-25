<template>
    <div class="col-lg-2 mt-4 mt-lg-0">
        <aside class="sidebar mt-2 mb-5">
            <ul class="nav nav-list flex-column">

                <li class="nav-item">
                    <RouterLink class="nav-link" to="/dashboard"
                                :class="{ active: isActive('/dashboard') }">
                        {{ __('message.dashboard') }}
                    </RouterLink>
                </li>

                <li class="nav-item">
                    <RouterLink class="nav-link" to="/orders"
                                :class="{ active: isActive('/orders') }">
                        {{ __('message.my_orders') }}
                    </RouterLink>
                </li>

                <li class="nav-item">
                    <RouterLink class="nav-link" to="/invoices"
                                :class="{ active: isActive('/invoices') }">
                        {{ __('message.my_invoices') }}
                    </RouterLink>
                </li>

                <li class="nav-item">
                    <RouterLink class="nav-link" to="/profile"
                                :class="{ active: isActive('/profile') }">
                        {{ __('message.my_profile') }}
                    </RouterLink>
                </li>

                <li class="nav-item">
                    <a class="nav-link" :href="logoutUrl">
                        {{ __('message.logout') }}
                    </a>
                </li>

            </ul>
        </aside>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route   = useRoute()
const el      = document.getElementById('app-client')
const baseUrl = computed(() => el?.dataset?.baseUrl ?? '')
const logoutUrl = computed(() => `${baseUrl.value}/auth/logout`)

const isActive = (path) => route.path.startsWith(path)
</script>
