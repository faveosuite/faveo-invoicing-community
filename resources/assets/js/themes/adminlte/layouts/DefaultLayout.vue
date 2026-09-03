<template>
    <Navbar />

    <Sidebar id="app-sidebar" />

    <main class="app-main">

        <!-- ── Content header: page title + breadcrumb ──────────────────── -->
        <div v-if="!route.meta?.isErrorPage" class="app-content-header">
            <div class="container-fluid">
                <div class="row align-items-center mb-2">
                    <div class="col-sm-6">
                        <h3 class="mb-0">{{ pageTitle }}</h3>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end mb-0" aria-label="Breadcrumb">
                            <li v-if="route.path !== '/dashboard'" class="breadcrumb-item">
                                <RouterLink to="/dashboard">{{ __('message.dashboard') }}</RouterLink>
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
                            <div class="row justify-content-center py-3"><loader /></div>
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

// Each page's own AppAlert clears itself on unmount (see Alert.vue) — a
// blanket unsetAlert() here used to wipe out a success alert set just before
// navigating to the next page (e.g. save → redirect to the index with a
// "saved" message).
watch(() => route.path, () => {
    if (window.innerWidth < 992) {
        close()
    }
})
</script>
