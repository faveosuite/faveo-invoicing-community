<template>
    <div class="col-lg-3 order-2 order-lg-1 mt-4 mt-lg-0">
        <aside class="sidebar mt-2" id="sidebar">
            <ul class="nav nav-list flex-column mb-5">

                <li class="nav-item">
                    <RouterLink class="nav-link text-3" to="/dashboard"
                                :class="{ active: route.path === '/dashboard' }">
                        {{ __('message.dashboard') }}
                    </RouterLink>
                </li>

                <li class="nav-item">
                    <RouterLink class="nav-link text-3" to="/orders"
                                :class="{ active: route.path.startsWith('/orders') }">
                        {{ __('message.my_orders') }}
                    </RouterLink>
                </li>

                <li class="nav-item">
                    <RouterLink class="nav-link text-3" to="/invoices"
                                :class="{ active: route.path.startsWith('/invoices') }">
                        {{ __('message.my_invoices') }}
                    </RouterLink>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-3" href="javascript:;" @click="profileOpen = !profileOpen">
                        {{ __('message.my_profile') }}
                    </a>
                    <ul v-show="profileOpen">
                        <li class="nav-item">
                            <RouterLink class="nav-link text-3" to="/profile"
                                        :class="{ active: route.path === '/profile' }">
                                {{ __('message.profile_information') }}
                            </RouterLink>
                        </li>
                        <li class="nav-item">
                            <RouterLink class="nav-link text-3" to="/profile/change-password"
                                        :class="{ active: route.path === '/profile/change-password' }">
                                {{ __('message.change_password') }}
                            </RouterLink>
                        </li>
                        <li class="nav-item">
                            <RouterLink class="nav-link text-3" to="/profile/2fa"
                                        :class="{ active: route.path === '/profile/2fa' }">
                                {{ __('message.two_factor_auth') }}
                            </RouterLink>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-3" :href="logoutUrl">
                        {{ __('message.logout') }}
                    </a>
                </li>

            </ul>
        </aside>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'

const route     = useRoute()
const el        = document.getElementById('app-client')
const baseUrl   = computed(() => el?.dataset?.baseUrl ?? '')
const logoutUrl = computed(() => `${baseUrl.value}/auth/logout`)

const profileOpen = ref(route.path.startsWith('/profile'))
</script>
