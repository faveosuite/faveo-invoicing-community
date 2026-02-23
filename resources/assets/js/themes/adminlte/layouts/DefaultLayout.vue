<template>
    <Navbar />

    <Sidebar id="app-sidebar" />

    <main class="app-main">

        <!-- ── Content header: page title + breadcrumb ──────────────────── -->
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h3 class="mb-0">{{ pageTitle }}</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end mb-0" aria-label="Breadcrumb">
                            <li class="breadcrumb-item">
                                <RouterLink to="/dashboard">Home</RouterLink>
                            </li>
                            <li v-for="(crumb, i) in breadcrumbs"
                                :key="i"
                                class="breadcrumb-item"
                                :class="{ active: crumb.isActive }"
                                :aria-current="crumb.isActive ? 'page' : undefined">
                                <RouterLink v-if="!crumb.isActive" :to="crumb.to">
                                    {{ crumb.title }}
                                </RouterLink>
                                <span v-else>{{ crumb.title }}</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Page content ──────────────────────────────────────────────── -->
        <div class="app-content" id="main" role="main" tabindex="-1">

            <div class="container-fluid">

                <!-- ── Global notification alert ──────────────────────────── -->
                <Transition name="alert-slide">
                    <div v-if="visible"
                         :class="`alert alert-${type} alert-dismissible`"
                         role="alert">
                        {{ message }}
                        <button type="button" class="btn-close" aria-label="Close" @click="dismiss" />
                    </div>
                </Transition>

                <RouterView v-slot="{ Component }">
                    <Suspense>
                        <component :is="Component" />
                        <template #fallback>
                            <div class="d-flex justify-content-center align-items-center"
                                 style="min-height: 400px">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading…</span>
                                </div>
                            </div>
                        </template>
                    </Suspense>
                </RouterView>
            </div>
        </div>

    </main>

    <AppFooter />

    <!-- Mobile overlay — closes sidebar when tapping outside -->
    <div class="sidebar-overlay"
         :class="{ show: isOpen }"
         aria-hidden="true"
         @click="close" />
</template>

<script setup>
import { watch }              from 'vue'
import { useRoute }           from 'vue-router'
import { useSidebar }         from '@/core/composables/useSidebar.js'
import { useBreadcrumb }      from '@/core/composables/useBreadcrumb.js'
import { useNotification }    from '@/core/composables/useNotification.js'

const { isOpen, close }          = useSidebar()
const { pageTitle, breadcrumbs } = useBreadcrumb()
const { message, type, visible, dismiss } = useNotification()
const route                      = useRoute()

// Close sidebar on navigation when in mobile overlay mode (viewport < lg / 992 px)
watch(() => route.path, () => {
    if (window.innerWidth < 992) {
        close()
    }
})
</script>
