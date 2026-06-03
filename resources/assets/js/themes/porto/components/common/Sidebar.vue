<template>
    <div class="col-lg-3 order-2 order-lg-1 mt-4 mt-lg-0">
        <aside class="sidebar mt-2" id="sidebar">

<ul class="nav nav-list flex-column mb-5">

                <li v-for="item in sidebarItems" :key="item.key" class="nav-item">

                    <!-- Collapsible parent with children -->
                    <template v-if="item.children?.length">
                        <a class="nav-link text-3" href="javascript:;"
                           @click="toggleItem(item.key)"
                           :class="{ active: isActive(item) }">
                            {{ getLabel(item) }}
                        </a>
                        <ul v-show="openKeys.has(item.key)">
                            <li v-for="child in item.children" :key="child.key" class="nav-item">
                                <RouterLink class="nav-link text-3" :to="child.route"
                                            :class="{ active: isActive(child) }">
                                    {{ getLabel(child) }}
                                </RouterLink>
                            </li>
                        </ul>
                    </template>

                    <!-- External URL (e.g. logout) -->
                    <a v-else-if="item.url" class="nav-link text-3" :href="resolveUrl(item.url)">
                        {{ getLabel(item) }}
                    </a>

                    <!-- Router link -->
                    <RouterLink v-else class="nav-link text-3" :to="item.route"
                                :class="{ active: isActive(item) }">
                        {{ getLabel(item) }}
                    </RouterLink>

                </li>

            </ul>
        </aside>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router'
import { __ } from '@/plugins/i18n'

const route   = useRoute()
const el      = document.getElementById('app-client')
const baseUrl = el?.dataset?.baseUrl ?? ''

const defaultItems = [
    { key: 'dashboard', label_key: 'dashboard',         route: '/client-dashboard',              active: 'exact' },
    { key: 'orders',    label_key: 'my_orders',          route: '/my-orders',                 active: 'prefix' },
    { key: 'invoices',  label_key: 'my_invoices',        route: '/my-invoices',               active: 'prefix' },
{ key: 'profile',   label_key: 'my_profile',         route: '/my-profile',                active: 'prefix',
        children: [
            { key: 'profile_info',    label_key: 'profile_information', route: '/my-profile',                 active: 'exact' },
            { key: 'change_password', label_key: 'change_password',     route: '/my-profile/change-password', active: 'exact' },
            { key: 'two_fa',          label_key: 'two_factor_auth',     route: '/my-profile/2fa',             active: 'exact' },
        ],
    },
    { key: 'logout', label_key: 'logout', url: '__LOGOUT__' },
]

const sidebarItems = defaultItems

const openKeys = ref(new Set(
    sidebarItems
        .filter(item => item.children?.length && route.path.startsWith(item.route ?? ''))
        .map(item => item.key)
))

function toggleItem(key) {
    if (openKeys.value.has(key)) {
        openKeys.value.delete(key)
    } else {
        openKeys.value.add(key)
    }
    openKeys.value = new Set(openKeys.value)
}

function isActive(item) {
    if (!item.route) return false
    return item.active === 'exact'
        ? route.path === item.route
        : route.path.startsWith(item.route)
}

function resolveUrl(url) {
    return url === '__LOGOUT__' ? `${baseUrl}/auth/logout` : url
}

function getLabel(item) {
    return item.label_key ? __(`message.${item.label_key}`) : (item.label ?? '')
}
</script>

