<template>
    <RecaptchaProvider>
    <!-- Standalone pages (e.g. open-payment) bypass the full shell -->
    <RouterView v-if="isStandalone" />
    <div v-else class="body">
        <a href="#main-content" class="visually-hidden-focusable skip-link" @click.prevent="focusMain">{{ __('message.skip_to_content') }}</a>

        <header id="header" class="header-effect-reveal"
                data-plugin-options="{'stickyEnabled': true, 'stickyEffect': 'reveal', 'stickyEnableOnBoxed': true, 'stickyEnableOnMobile': false, 'stickyChangeLogo': false, 'stickyStartAt': 200, 'stickySetTop': '-44px'}">
            <Navbar />
        </header>

        <main id="main-content" ref="mainEl" class="main" tabindex="-1">

            <!-- Page header with title + breadcrumb -->
            <section v-if="pageTitle" class="page-header page-header-modern bg-color-grey page-header-lg">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 align-self-center p-static order-2 text-center">
                            <h1 class="font-weight-bold text-dark">{{ pageTitle }}</h1>
                        </div>
                        <div class="col-md-12 align-self-center order-1">
                            <nav :aria-label="__('message.breadcrumb')">
                            <ul class="breadcrumb d-block text-center">
                                <li><a :href="homeUrl">{{ __('message.home') }}</a></li>
                                <li v-for="(crumb, i) in breadcrumbs"
                                    :key="i"
                                    :class="{ active: crumb.isActive }"
                                    :aria-current="crumb.isActive ? 'page' : undefined">
                                    <RouterLink v-if="!crumb.isActive" :to="crumb.to">
                                        {{ crumb.title }}
                                    </RouterLink>
                                    <span v-else>{{ crumb.title }}</span>
                                </li>
                            </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Notification toast -->
            <div v-if="notification.visible" class="container mt-3">
                <div :class="`alert alert-${notification.type} alert-dismissible`" role="alert">
                    {{ notification.message }}
                    <button type="button" class="btn-close" :aria-label="__('message.close')"
                            @click="notification.dismiss" />
                </div>
            </div>

            <!-- Sidebar + content -->
            <div class="container pt-3 pb-2">
                <div class="row pt-2">

                    <Sidebar v-if="showSidebar" />

                    <div :class="showSidebar ? 'col-12 client-content-col' : 'col-12'">
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

        </main>

        <AppFooter />

    </div>
    </RecaptchaProvider>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRoute }       from 'vue-router'
import { useNotification }           from '@/core/composables/useNotification.js'
import { useAlertStore }             from '@/core/stores/alert.js'
import { useBreadcrumb }             from '@/core/composables/useBreadcrumb.js'
import { useStickyHeader }           from '../composables/useStickyHeader.js'
import { useAnalyticsScripts }       from '@/core/composables/useAnalyticsScripts.js'
import { RecaptchaProvider }         from '@recaptcha'
import { useBaseUrl }                from '@/core/composables/useBaseUrl'

useStickyHeader()
useAnalyticsScripts()

const route        = useRoute()
const alertStore   = useAlertStore()
const notification = reactive(useNotification())
const { pageTitle, breadcrumbs } = useBreadcrumb()

const showSidebar  = computed(() => route.meta?.sidebar !== false)
const isStandalone = computed(() => route.meta?.standalone === true)
const homeUrl      = useBaseUrl() || '/'

watch(() => route.path, () => alertStore.unsetAlert())

const mainEl = ref(null)
// Router owns the URL hash, so move focus manually instead of following #main-content.
const focusMain = () => mainEl.value?.focus()
</script>

<style scoped>
.skip-link {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    z-index: 10000;
    padding: 0.5rem 1rem;
    background: #fff;
    color: #212529;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}
#main-content:focus { outline: none; }
</style>
