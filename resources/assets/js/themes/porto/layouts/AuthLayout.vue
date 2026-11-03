<template>
    <div class="d-flex flex-column min-vh-100 bg-white">
        <div class="row g-0 flex-grow-1">

            <!-- Brand panel (desktop only). Admin: Settings › Widgets › Login page; empty text = translated default. -->
            <aside v-if="brand.show" class="col-lg-7 col-xl-8 d-none d-lg-flex flex-column bg-color-grey p-5" :aria-label="appCompany || undefined">
                <h2 class="font-weight-bold text-color-dark text-9 line-height-2 mb-3">{{ brand.title || __('message.auth_brand_title') }}</h2>
                <p class="text-4 mb-0">{{ brand.description || __('message.auth_brand_description') }}</p>
                <div class="flex-grow-1 d-flex align-items-center justify-content-center py-5">
                    <img :src="brand.image || `${baseUrl}/images/auth-brand.svg`" alt="" class="img-fluid" width="560" height="380">
                </div>
                <p class="text-5 font-weight-semibold text-color-dark text-center mb-0">{{ brand.tagline || __('message.auth_brand_tagline') }}</p>
            </aside>

            <!-- Form column (full width, form centred, when the brand panel is off) -->
            <main id="main-content" class="col-12 d-flex flex-column px-3 px-md-5 py-3 py-md-4" :class="{ 'col-lg-5 col-xl-4': brand.show }">
                <div class="position-relative d-flex justify-content-center align-items-center mb-5">
                    <RouterLink to="/" class="d-inline-flex text-decoration-none">
                        <img v-if="logoUrl" :src="logoUrl" :alt="appCompany || __('message.logo')" height="56">
                        <span v-else class="fw-bold text-dark text-5">{{ appCompany }}</span>
                    </RouterLink>

                    <div v-if="languages.length" class="dropdown position-absolute top-0 end-0">
                        <button type="button" class="btn btn-light border d-flex align-items-center gap-2 px-3 py-2 dropdown-toggle"
                                data-bs-toggle="dropdown" aria-expanded="false"
                                :aria-label="__('message.change_language_current', { locale: currentLocale })">
                            <span :class="`fi fi-${flagCodeFor(currentLocale)}`" aria-hidden="true"></span>
                            <span class="text-2 font-weight-semibold">{{ currentLocale.toUpperCase() }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li v-for="lang in languages" :key="lang.locale">
                                <button type="button" class="dropdown-item d-flex align-items-center gap-2"
                                        :class="{ active: isCurrent(lang) }"
                                        :aria-current="isCurrent(lang) ? 'true' : undefined"
                                        @click="selectLang(lang)">
                                    <span :class="`fi fi-${flagCodeFor(lang.locale)}`" aria-hidden="true"></span>
                                    <span class="flex-grow-1">{{ lang.name }}{{ lang.translation ? ` (${lang.translation})` : '' }}</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-12 col-md-8" :class="brand.show ? 'col-lg-12' : 'col-lg-6 col-xl-4'">
                        <div v-if="route.meta?.titleKey" class="mb-4">
                            <h1 class="font-weight-bold text-color-dark text-7 line-height-2 mb-1">{{ __(route.meta.titleKey) }}</h1>
                            <p v-if="route.meta?.subtitleKey" class="text-3 mb-0">{{ __(route.meta.subtitleKey) }}</p>
                        </div>

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
            </main>
        </div>

        <footer class="bg-color-grey border-top px-4 py-3 text-2 text-center text-lg-start">
            {{ __('message.copyright') }} © {{ currentYear }}
            <a v-if="website" :href="website" class="text-color-primary text-decoration-none" target="_blank" rel="noopener">{{ appCompany }}</a>
            <span v-else>{{ appCompany }}</span>.
            {{ __('message.all_rights') }}
            <span class="font-weight-semibold" translate="no">Faveo</span>
        </footer>
    </div>
</template>

<script setup>
import { nextTick, onMounted, provide } from 'vue'
import { useRoute } from 'vue-router'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import { useLanguages, restoreStashedForm } from '../composables/useLanguages.js'

// Every auth field is required, so the red * is noise; optional ones say "(optional)".
provide('hideRequiredMark', true)

const route = useRoute()
const baseUrl = useBaseUrl()
const el = document.getElementById('app-client')
const logoUrl = el?.dataset?.appLogo ?? ''
const appCompany = el?.dataset?.company ?? ''
const website = el?.dataset?.website ?? ''
const currentYear = new Date().getFullYear()

// Brand panel settings from the server; any missing/broken value falls back to the defaults.
let brand = { show: true, title: '', description: '', tagline: '', image: null }
try {
    brand = { ...brand, ...JSON.parse(el?.dataset?.authBrand || '{}') }
} catch { /* keep defaults */ }

const { currentLocale, languages, isCurrent, selectLang, flagCodeFor } = useLanguages()

// After a language switch, put back what the user had typed (the page reloaded).
onMounted(() => nextTick(restoreStashedForm))
</script>
