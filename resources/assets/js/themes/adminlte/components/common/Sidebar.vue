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
                            <i class="nav-icon fas fa-gauge"></i>
                            <p>{{ __('message.dashboard') }}</p>
                        </RouterLink>
                    </li>

                    <!-- ── Invoicing ──────────────────────────────────────────── -->
                    <li class="nav-header">Invoicing</li>

                    <!-- ── Users ───────────────────────────────────────────── -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('users') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/users') }"
                           @click.prevent="toggleSection('users')">
                            <i class="nav-icon fas fa-user"></i>
                            <p>{{ __('message.users') }} <i class="nav-arrow fas fa-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/users" class="nav-link"
                                            :class="{ active: isExact('/users') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.all-users') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/users/create" class="nav-link"
                                            :class="{ active: isExact('/users/create') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.add-new') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/users/suspended" class="nav-link"
                                            :class="{ active: isExact('/users/suspended') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.suspended_users') }}</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- ── Orders ──────────────────────────────────────────── -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('orders') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/orders') }"
                           @click.prevent="toggleSection('orders')">
                            <i class="nav-icon fas fa-chart-pie"></i>
                            <p>{{ __('message.orders') }} <i class="nav-arrow fas fa-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/orders" class="nav-link"
                                            :class="{ active: isExact('/orders') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.all-orders') }}</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- ── Invoices ────────────────────────────────────────── -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('invoices') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/invoices') }"
                           @click.prevent="toggleSection('invoices')">
                            <i class="nav-icon fas fa-receipt"></i>
                            <p>{{ __('message.invoices') }} <i class="nav-arrow fas fa-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/invoices" class="nav-link"
                                            :class="{ active: isExact('/invoices') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.all-invoices') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/invoices/create" class="nav-link"
                                            :class="{ active: isExact('/invoices/create') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.add-new') }}</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- ── Pages ───────────────────────────────────────────── -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('pages') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/pages') }"
                           @click.prevent="toggleSection('pages')">
                            <i class="nav-icon fas fa-file-lines"></i>
                            <p>{{ __('message.pages') }} <i class="nav-arrow fas fa-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/pages" class="nav-link"
                                            :class="{ active: isExact('/pages') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.all_pages') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/pages/create" class="nav-link"
                                            :class="{ active: isExact('/pages/create') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.add-new') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/pages/settings" class="nav-link"
                                            :class="{ active: isExact('/pages/settings') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.page_settings') }}</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- ── Products ────────────────────────────────────────── -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('products') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/products') }"
                           @click.prevent="toggleSection('products')">
                            <i class="nav-icon fas fa-briefcase"></i>
                            <p>{{ __('message.products') }} <i class="nav-arrow fas fa-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/products" class="nav-link"
                                            :class="{ active: isExact('/products') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.all-products') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/products/create" class="nav-link"
                                            :class="{ active: isExact('/products/create') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.add_product') }}</p>
                                </RouterLink>
                            </li>
                            <!-- Plans/Coupons/Groups: active on self AND child routes (e.g. /edit) -->
                            <li class="nav-item">
                                <RouterLink to="/products/plans" class="nav-link"
                                            :class="{ active: isGroupActive('/products/plans') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.plans') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/products/coupons" class="nav-link"
                                            :class="{ active: isGroupActive('/products/coupons') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.coupons') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/products/groups" class="nav-link"
                                            :class="{ active: isGroupActive('/products/groups') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.groups') }}</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- ── Reports ─────────────────────────────────────────── -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('reports') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/reports') }"
                           @click.prevent="toggleSection('reports')">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>{{ __('message.reports') }} <i class="nav-arrow fas fa-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/reports" class="nav-link"
                                            :class="{ active: isExact('/reports') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.all_reports') }}</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/reports/settings" class="nav-link"
                                            :class="{ active: isExact('/reports/settings') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>{{ __('message.report_settings') }}</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- ── Settings ────────────────────────────────────────── -->
                    <li class="nav-item">
                        <RouterLink to="/settings" class="nav-link"
                                    :class="{ active: isGroupActive('/settings') }"
                                    active-class="" exact-active-class="">
                            <i class="nav-icon fas fa-gear"></i>
                            <p>{{ __('message.settings') }}</p>
                        </RouterLink>
                    </li>

                    <!-- ── License Module ──────────────────────────────────── -->
                    <li class="nav-header">License Manager</li>

                    <!-- Versions -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('versions') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/versions') }"
                           @click.prevent="toggleSection('versions')">
                            <i class="nav-icon fas fa-box"></i>
                            <p>Versions <i class="nav-arrow fas fa-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/versions/list" class="nav-link"
                                            :class="{ active: isExact('/versions/list') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>All Versions</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- Licenses -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('licenses') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/licenses') }"
                           @click.prevent="toggleSection('licenses')">
                            <i class="nav-icon fas fa-key"></i>
                            <p>Licenses <i class="nav-arrow fas fa-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/licenses/create" class="nav-link"
                                            :class="{ active: isExact('/licenses/create') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>New License</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/licenses/list" class="nav-link"
                                            :class="{ active: isExact('/licenses/list') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>All Licenses</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- Installations -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('installations') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/installations') }"
                           @click.prevent="toggleSection('installations')">
                            <i class="nav-icon fas fa-download"></i>
                            <p>Installations <i class="nav-arrow fas fa-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/installations/list" class="nav-link"
                                            :class="{ active: isExact('/installations/list') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>All Installations</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- Callbacks -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('callbacks') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/callbacks') }"
                           @click.prevent="toggleSection('callbacks')">
                            <i class="nav-icon fas fa-phone"></i>
                            <p>Callbacks <i class="nav-arrow fas fa-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/callbacks/list" class="nav-link"
                                            :class="{ active: isExact('/callbacks/list') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>All Callbacks</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- Log Reports -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('log-reports') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/log-reports') }"
                           @click.prevent="toggleSection('log-reports')">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p>Log Reports <i class="nav-arrow fas fa-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/log-reports/license" class="nav-link"
                                            :class="{ active: isExact('/log-reports/license') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>License Reports</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/log-reports/system" class="nav-link"
                                            :class="{ active: isExact('/log-reports/system') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>System Reports</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/log-reports/update" class="nav-link"
                                            :class="{ active: isExact('/log-reports/update') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>Update Reports</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/log-reports/crack" class="nav-link"
                                            :class="{ active: isExact('/log-reports/crack') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>Cracking Reports</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- Banned Hosts -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('banned-hosts') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/banned-hosts') }"
                           @click.prevent="toggleSection('banned-hosts')">
                            <i class="nav-icon fas fa-circle-xmark"></i>
                            <p>Banned Hosts <i class="nav-arrow fas fa-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/banned-hosts/create" class="nav-link"
                                            :class="{ active: isExact('/banned-hosts/create') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>New Banned Host</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/banned-hosts/list" class="nav-link"
                                            :class="{ active: isExact('/banned-hosts/list') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>All Banned Hosts</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- Whitelist IP -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('whitelist') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/whitelist') }"
                           @click.prevent="toggleSection('whitelist')">
                            <i class="nav-icon fas fa-shield-halved"></i>
                            <p>Whitelist IP <i class="nav-arrow fas fa-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/whitelist/create" class="nav-link"
                                            :class="{ active: isExact('/whitelist/create') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>New Whitelist</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/whitelist/list" class="nav-link"
                                            :class="{ active: isExact('/whitelist/list') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>All Whitelist</p>
                                </RouterLink>
                            </li>
                        </ul>
                    </li>

                    <!-- Server Notifications -->
                    <li class="nav-item" :class="{ 'menu-open': isOpen('server') }">
                        <a class="nav-link" :class="{ active: isGroupActive('/server') }"
                           @click.prevent="toggleSection('server')">
                            <i class="nav-icon fas fa-server"></i>
                            <p>Server Notifications <i class="nav-arrow fas fa-chevron-right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <RouterLink to="/server/notifications" class="nav-link"
                                            :class="{ active: isExact('/server/notifications') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>License Notification</p>
                                </RouterLink>
                            </li>
                            <li class="nav-item">
                                <RouterLink to="/server/update-notifications" class="nav-link"
                                            :class="{ active: isExact('/server/update-notifications') }"
                                            active-class="" exact-active-class="">
                                    <i class="nav-icon far fa-circle"></i><p>Update Notification</p>
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
    '/log-reports': 'log-reports',
    '/server': 'server',
    '/versions': 'versions',
    '/licenses': 'licenses',
    '/installations': 'installations',
    '/callbacks': 'callbacks',
    '/banned-hosts': 'banned-hosts',
    '/whitelist': 'whitelist',
}

function toggleSection(key) {
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
