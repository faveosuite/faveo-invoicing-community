<template>
    <div class="body">

        <header id="header" class="header-effect-shrink"
                data-plugin-options="{'stickyEnabled': true, 'stickyEffect': 'shrink', 'stickyEnableOnBoxed': false, 'stickyEnableOnMobile': false, 'stickyStartAt': 70, 'stickyChangeLogo': false, 'stickyHeaderContainerHeight': 70}">
            <Navbar />
        </header>

        <div role="main" class="main">

            <!-- Page header with title + breadcrumb -->
            <section v-if="pageTitle" class="page-header page-header-modern bg-color-grey page-header-sm">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 align-self-center p-static order-2 text-center">
                            <h1 class="font-weight-bold text-dark text-8">{{ pageTitle }}</h1>
                        </div>
                        <div class="col-md-12 align-self-center order-1">
                            <ul class="breadcrumb d-block text-center">
                                <li><RouterLink to="/dashboard">{{ __('message.dashboard') }}</RouterLink></li>
                                <li class="active">{{ pageTitle }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Notification toast -->
            <div v-if="notification.visible" class="container mt-3">
                <div :class="`alert alert-${notification.type} alert-dismissible`" role="alert">
                    {{ notification.message }}
                    <button type="button" class="btn-close" aria-label="Close"
                            @click="notification.dismiss" />
                </div>
            </div>

            <!-- Sidebar + content -->
            <div class="container pt-3 pb-2">
                <div class="row pt-2">

                    <Sidebar v-if="showSidebar" />

                    <div :class="showSidebar ? 'col-lg-9 order-1 order-lg-2' : 'col-12'">
                        <RouterView v-slot="{ Component }">
                            <Suspense>
                                <component :is="Component" />
                                <template #fallback>
                                    <inline-loader />
                                </template>
                            </Suspense>
                        </RouterView>
                    </div>

                </div>
            </div>

        </div>

        <AppFooter />

    </div>
</template>

<script setup>
import { computed, onMounted, reactive, watch } from 'vue'
import { useRoute }                             from 'vue-router'
import { useNotification }                      from '@/core/composables/useNotification.js'
import { useAlertStore }                        from '@/core/stores/alert.js'

const route        = useRoute()
const alertStore   = useAlertStore()
const notification = reactive(useNotification())

const pageTitle  = computed(() => route.meta?.title ?? '')
const showSidebar = computed(() => route.meta?.sidebar !== false)

watch(() => route.path, () => alertStore.unsetAlert())

onMounted(() => {
    window.theme?.StickyHeader?.initialize()
    window.theme?.Nav?.initialize()
})
</script>
