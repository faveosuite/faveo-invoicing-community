<template>
    <div class="col-12 client-sidebar-col order-2 order-lg-1 mt-4 mt-lg-0">
        <aside class="sidebar client-sidebar mt-2" id="sidebar">

            <ul class="nav nav-list no-arrows flex-column mb-5">

                <li v-for="item in sidebarItems" :key="item.key" class="nav-item">

                    <!-- A group heading opens its section; the children carry the routes -->
                    <template v-if="item.children?.length">
                        <button type="button" class="nav-link nav-section"
                                :class="{ 'nav-section-current': isInSection(item) }"
                                :aria-expanded="openKeys.has(item.key)"
                                @click="toggleItem(item.key)">
                            <i :class="item.icon" class="fa-fw nav-icon"></i>{{ getLabel(item) }}
                            <i class="fas fa-angle-down nav-caret"
                               :class="{ 'nav-caret-open': openKeys.has(item.key) }"></i>
                        </button>
                        <ul v-show="openKeys.has(item.key)" class="nav-sublist">
                            <li v-for="child in item.children" :key="child.key" class="nav-item">
                                <RouterLink class="nav-link" :to="child.route"
                                            :class="{ active: isActive(child) }">
                                    <i :class="child.icon" class="fa-fw nav-icon"></i>{{ getLabel(child) }}
                                </RouterLink>
                            </li>
                        </ul>
                    </template>

                    <!-- External URL (e.g. logout) — leaves the panel, so it reads apart -->
                    <a v-else-if="item.url" class="nav-link nav-exit" :href="resolveUrl(item.url)">
                        <i :class="item.icon" class="fa-fw nav-icon"></i>{{ getLabel(item) }}
                    </a>

                    <!-- Router link -->
                    <RouterLink v-else class="nav-link" :to="item.route"
                                :class="{ active: isActive(item) }">
                        <i :class="item.icon" class="fa-fw nav-icon"></i>{{ getLabel(item) }}
                    </RouterLink>

                </li>

            </ul>
        </aside>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { __ } from '@/plugins/i18n'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const route   = useRoute()
const baseUrl = useBaseUrl()

const defaultItems = [
    { key: 'dashboard', label_key: 'dashboard',   icon: 'fas fa-gauge-high',   route: '/client-dashboard', active: 'exact' },
    { key: 'orders',    label_key: 'my_orders',   icon: 'fas fa-box',          route: '/my-orders',        active: 'prefix' },
    { key: 'invoices',  label_key: 'my_invoices', icon: 'fas fa-file-invoice', route: '/my-invoices',      active: 'prefix' },
    { key: 'profile',   label_key: 'my_profile',  icon: 'fas fa-user',
        children: [
            { key: 'profile_info',    label_key: 'profile_information', icon: 'fas fa-id-card',       route: '/my-profile',                 active: 'exact' },
            { key: 'change_password', label_key: 'change_password',     icon: 'fas fa-key',           route: '/my-profile/change-password', active: 'exact' },
            { key: 'two_fa',          label_key: 'two_factor_authentication', icon: 'fas fa-shield-halved', route: '/my-profile/2fa',       active: 'exact' },
        ],
    },
    { key: 'logout', label_key: 'logout', icon: 'fas fa-right-from-bracket', url: '__LOGOUT__' },
]

const sidebarItems = defaultItems

// Only the section holding the current route is open, so navigating out of a
// section closes it. A manual toggle stands until the next navigation.
const sectionsForRoute = () => new Set(sidebarItems.filter(isInSection).map(item => item.key))

const openKeys = ref(sectionsForRoute())

watch(() => route.path, () => {
    openKeys.value = sectionsForRoute()
})

function toggleItem(key) {
    const next = new Set(openKeys.value)
    if (next.has(key)) {
        next.delete(key)
    } else {
        next.add(key)
    }
    openKeys.value = next
}

function isInSection(item) {
    return !!item.children?.some(isActive)
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

