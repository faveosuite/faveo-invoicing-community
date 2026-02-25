<template>
    <aside id="app-sidebar" class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark"
           role="navigation" aria-label="Main sidebar">

        <!-- Brand -->
        <div class="sidebar-brand">
            <RouterLink v-if="logoUrl" to="/dashboard" class="brand-link">
                <img :src="logoUrl" alt="Logo"
                     class="brand-image"
                     @error="e => e.target.style.display = 'none'" />
            </RouterLink>

            <RouterLink v-else to="/" class="brand-link">

                <span class="brand-text"><b>{{appTitle}}</b></span>
            </RouterLink>
        </div>

        <!-- Sidebar Wrapper -->
        <div class="sidebar-wrapper">
            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column nav-child-indent"
                    role="navigation"
                    aria-label="Main navigation">

                    <!-- ── Dashboard ───────────────────────────────────────── -->
                    <li class="nav-item">
                        <RouterLink to="/dashboard" class="nav-link"
                                    :class="{ active: isExact('/dashboard') }"
                                    active-class="" exact-active-class="">
                            <i class="nav-icon bi bi-speedometer2"></i>
                            <p>{{ __('message.dashboard') }}</p>
                        </RouterLink>
                    </li>

                    <!-- ── Users ───────────────────────────────────────────── -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('users') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/users') }"
                           @click.prevent="toggle('users')">
                            <i class="nav-icon bi bi-person-fill"></i>
                            <p>{{ __('message.users') }} <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/users" class="nav-link"
                                            :class="{ active: isExact('/users') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.all-users') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/users/create" class="nav-link"
                                            :class="{ active: isExact('/users/create') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.add-new') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/users/suspended" class="nav-link"
                                            :class="{ active: isExact('/users/suspended') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.suspended_users') }}</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- ── Orders ──────────────────────────────────────────── -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('orders') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/orders') }"
                           @click.prevent="toggle('orders')">
                            <i class="nav-icon bi bi-pie-chart-fill"></i>
                            <p>{{ __('message.orders') }} <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/orders" class="nav-link"
                                            :class="{ active: isExact('/orders') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.all-orders') }}</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- ── Invoices ────────────────────────────────────────── -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('invoices') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/invoices') }"
                           @click.prevent="toggle('invoices')">
                            <i class="nav-icon bi bi-receipt"></i>
                            <p>{{ __('message.invoices') }} <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/invoices" class="nav-link"
                                            :class="{ active: isExact('/invoices') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.all-invoices') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/invoices/create" class="nav-link"
                                            :class="{ active: isExact('/invoices/create') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.add-new') }}</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- ── Pages ───────────────────────────────────────────── -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('pages') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/pages') }"
                           @click.prevent="toggle('pages')">
                            <i class="nav-icon bi bi-file-earmark-text-fill"></i>
                            <p>{{ __('message.pages') }} <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/pages" class="nav-link"
                                            :class="{ active: isExact('/pages') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.all-pages') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/pages/create" class="nav-link"
                                            :class="{ active: isExact('/pages/create') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.add-new') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/pages/demo" class="nav-link"
                                            :class="{ active: isExact('/pages/demo') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.add-demo') }}</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- ── Products ────────────────────────────────────────── -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('products') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/products') }"
                           @click.prevent="toggle('products')">
                            <i class="nav-icon bi bi-briefcase-fill"></i>
                            <p>{{ __('message.products') }} <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/products" class="nav-link"
                                            :class="{ active: isExact('/products') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.all_products') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/products/create" class="nav-link"
                                            :class="{ active: isExact('/products/create') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.add_product') }}</p>
                                </RouterLink>
                            </li>
                            <!-- Plans/Coupons/Groups: active on self AND child routes (e.g. /edit) -->
                            <li class="nav-item">
                                <RouterLink to="/products/plans" class="nav-link"
                                            :class="{ active: isGroupActive('/products/plans') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.plans') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/products/coupons" class="nav-link"
                                            :class="{ active: isGroupActive('/products/coupons') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.coupons') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/products/groups" class="nav-link"
                                            :class="{ active: isGroupActive('/products/groups') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.groups') }}</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- ── Reports ─────────────────────────────────────────── -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('reports') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/reports') }"
                           @click.prevent="toggle('reports')">
                            <i class="nav-icon bi bi-bar-chart-line-fill"></i>
                            <p>{{ __('message.reports') }} <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/reports" class="nav-link"
                                            :class="{ active: isExact('/reports') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.all_reports') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/reports/settings" class="nav-link"
                                            :class="{ active: isExact('/reports/settings') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.report_settings') }}</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- ── Settings ────────────────────────────────────────── -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('settings') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/settings') }"
                           @click.prevent="toggle('settings')">
                            <i class="nav-icon bi bi-gear-fill"></i>
                            <p>{{ __('message.settings') }} <i class="nav-arrow bi bi-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/settings/system" class="nav-link"
                                            :class="{ active: isExact('/settings/system') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.system') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/settings/email/settings" class="nav-link"
                                            :class="{ active: isGroupActive('/settings/email') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.email') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/settings/common/currency" class="nav-link"
                                            :class="{ active: isGroupActive('/settings/common') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.common') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/settings/logs/system" class="nav-link"
                                            :class="{ active: isGroupActive('/settings/logs') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.logs') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/settings/widgets/footer" class="nav-link"
                                            :class="{ active: isGroupActive('/settings/widgets') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.widgets') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/settings/api/pipedrive" class="nav-link"
                                            :class="{ active: isGroupActive('/settings/api') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon bi bi-circle"></i><p>{{ __('message.api_integrations') }}</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                </ul>
            </nav>
        </div>

    </aside>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { asset } from '@/core/utils/asset.js'

const route = useRoute()

const el = document.getElementById('app-root')
const logoUrl = el?.dataset?.appLogo ?? '';
const appTitle = el?.dataset?.appTitle ?? '';

// ── Treeview open/close state ─────────────────────────────────────────────────
const openItems = ref(new Set())

const routeGroupMap = {
    '/users':    'users',
    '/orders':   'orders',
    '/invoices': 'invoices',
    '/pages':    'pages',
    '/products': 'products',
    '/reports':  'reports',
    '/settings': 'settings',
}

function toggle(key) {
    const next = new Set(openItems.value)
    next.has(key) ? next.delete(key) : next.add(key)
    openItems.value = next
}

function isOpen(key) {
    return openItems.value.has(key)
}

// ── Active state helpers ──────────────────────────────────────────────────────
// Exact match — used for leaf pages that have no sub-routes in the sidebar.
function isExact(path) {
    return route.path === path
}

// Prefix match — used for parent <a> labels and section-root links (plans, coupons…).
// Guards against false matches like /products matching /products-something.
function isGroupActive(prefix) {
    return route.path === prefix || route.path.startsWith(prefix + '/')
}

// ── Auto-open parent on navigation ───────────────────────────────────────────
// On every route change: open only the matching parent, close everything else.
// This prevents stale open sections from showing a highlighted background
// (AdminLTE styles menu-open rows even without the active class).
watch(
    () => route.path,
    (path) => {
        for (const [prefix, key] of Object.entries(routeGroupMap)) {
            if (path === prefix || path.startsWith(prefix + '/')) {
                openItems.value = new Set([key])
                return
            }
        }
        // Top-level routes (e.g. /dashboard) — close all sections
        openItems.value = new Set()
    },
    { immediate: true },
)
</script>
