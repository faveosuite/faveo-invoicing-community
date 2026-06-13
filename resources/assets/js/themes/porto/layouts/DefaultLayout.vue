<template>
    <RecaptchaProvider>
    <!-- Standalone pages (e.g. open-payment) bypass the full shell -->
    <RouterView v-if="isStandalone" />
    <div v-else class="body">

        <header id="header" class="header-effect-reveal"
                data-plugin-options="{'stickyEnabled': true, 'stickyEffect': 'reveal', 'stickyEnableOnBoxed': true, 'stickyEnableOnMobile': false, 'stickyChangeLogo': false, 'stickyStartAt': 200, 'stickySetTop': '-44px'}">
            <Navbar />
        </header>

        <div role="main" class="main">

            <!-- Page header with title + breadcrumb -->
            <section v-if="pageTitle" class="page-header page-header-modern bg-color-grey page-header-lg">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 align-self-center p-static order-2 text-center">
                            <h1 class="font-weight-bold text-dark">{{ pageTitle }}</h1>
                        </div>
                        <div class="col-md-12 align-self-center order-1">
                            <ul class="breadcrumb d-block text-center">
                                <li><a :href="homeUrl">{{ __('message.home') }}</a></li>
                                <li v-for="(crumb, i) in breadcrumbs"
                                    :key="i"
                                    :class="{ active: crumb.isActive }">
                                    <RouterLink v-if="!crumb.isActive" :to="crumb.to">
                                        {{ crumb.title }}
                                    </RouterLink>
                                    <span v-else>{{ crumb.title }}</span>
                                </li>
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
                        <AppAlert componentName="client-page" />
                        <RouterView v-slot="{ Component }">
                            <Suspense>
                                <template #default>
                                    <component :is="Component" />
                                </template>
                                <template #fallback>
                                    <div class="row justify-content-center py-3"><loader /></div>
                                </template>
                            </Suspense>
                        </RouterView>
                    </div>

                </div>
            </div>

        </div>

        <AppFooter />

    </div>
    </RecaptchaProvider>
</template>

<script setup>
import { computed, reactive, watch } from 'vue'
import { useRoute, useRouter }       from 'vue-router'
import { useNotification }           from '@/core/composables/useNotification.js'
import { useAlertStore }             from '@/core/stores/alert.js'
import { useBreadcrumb }             from '@/core/composables/useBreadcrumb.js'
import { useStickyHeader }           from '../composables/useStickyHeader.js'
import { useAnalyticsScripts }       from '@/core/composables/useAnalyticsScripts.js'
import { RecaptchaProvider }         from '@recaptcha'

useStickyHeader()
useAnalyticsScripts()

const route        = useRoute()
const router       = useRouter()
const alertStore   = useAlertStore()
const notification = reactive(useNotification())
const { pageTitle, breadcrumbs } = useBreadcrumb()

const el           = document.getElementById('app-client')
const showSidebar  = computed(() => route.meta?.sidebar !== false)
const isStandalone = computed(() => route.meta?.standalone === true)
const homeUrl      = computed(() => el?.dataset?.baseUrl ?? '/')

watch(() => route.path, () => alertStore.unsetAlert())
</script>
