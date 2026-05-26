<template>
    <footer id="footer" class="footer-top-border bg-color-grey">

        <div v-if="footerWidgets.length" class="container py-4">
            <div class="row py-5">

                <div v-for="(widget, index) in footerWidgets" :key="widget.id"
                     class="col-md-6 col-lg-4 mb-4 mb-lg-0">

                    <h5 class="text-3 text-color-dark mb-3">{{ widget.name.toUpperCase() }}</h5>

                    <!-- Widget HTML content -->
                    <div v-if="widget.content" class="footer-widget-content" v-html="widget.content"></div>

                    <!-- Newsletter form (allow_mailchimp) -->
                    <template v-if="widget.allow_mailchimp">
                        <div v-if="newsletterSuccess" class="alert alert-success">
                            <strong>{{ __('message.success') }}!</strong>
                            {{ __('message.newsletter_subscribed') }}
                        </div>
                        <div v-if="newsletterError" class="alert alert-danger">
                            {{ newsletterError }}
                        </div>
                        <form v-if="!newsletterSuccess" @submit.prevent="subscribeNewsletter" class="me-4 mb-4">
                            <div class="input-group input-group-rounded">
                                <input class="form-control form-control-sm bg-light"
                                       type="email" v-model="newsletterEmail"
                                       placeholder="Email Address..." required>
                                <button class="btn btn-light text-color-dark"
                                        type="submit" :disabled="subscribing">
                                    <strong>GO!</strong>
                                </button>
                            </div>
                        </form>
                    </template>

                    <!-- Contact details (allow_social_media) -->
                    <template v-if="widget.allow_social_media">
                        <ul class="list list-icons list-icons-lg">
                            <li v-if="phone" class="mb-1">
                                <i class="fab fa-whatsapp text-color-primary"></i>
                                <p class="m-0">
                                    <a class="text-color-default" :href="`tel:${phone}`">
                                        {{ phoneCode ? `+${phoneCode} ` : '' }}{{ phone }}
                                    </a>
                                </p>
                            </li>
                            <li v-if="companyEmail" class="mb-1">
                                <i class="far fa-envelope text-color-primary"></i>
                                <p class="m-0">
                                    <a class="text-color-default" :href="`mailto:${companyEmail}`">{{ companyEmail }}</a>
                                </p>
                            </li>
                        </ul>
                        <ul v-if="social.length" class="header-social-icons social-icons mt-3">
                          <li v-for="s in social" :key="s.name"
                              :class="`social-icons-${s.name.toLowerCase()}`">
                          <a :href="s.link" target="_blank" :title="s.name">
                                    <i :class="`fab fa-${s.name.toLowerCase()} text-2`"></i>
                                </a>
                            </li>
                        </ul>
                    </template>

                </div>

            </div>
        </div>

        <div class="footer-copyright footer-top-border bg-color-grey">
            <div class="container py-2">
                <div class="row py-4">
                    <div class="col-12 d-flex align-items-center justify-content-center">
                        <p class="mb-0 text-center">
                            {{ __('message.copyright') }} © {{ currentYear }}
                            <a v-if="website" :href="website"
                               class="text-color-primary text-decoration-none"
                               target="_blank">{{ company }}</a>
                            <span v-else>{{ company }}</span>.
                            {{ __('message.all_rights') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </footer>
</template>

<script setup>
import { ref, computed } from 'vue'
import { __ } from '@/plugins/i18n'
import http from '@/plugins/axios'

const el           = document.getElementById('app-client')
const company      = computed(() => el?.dataset?.company ?? '')
const website      = computed(() => el?.dataset?.website ?? '')
const companyEmail = computed(() => el?.dataset?.companyEmail ?? '')
const phone        = computed(() => el?.dataset?.phone ?? '')
const phoneCode    = computed(() => el?.dataset?.phoneCode ?? '')
const currentYear  = new Date().getFullYear()

const social = computed(() => {
    try { return JSON.parse(el?.dataset?.social ?? '[]') } catch { return [] }
})

const footerWidgets = computed(() => {
    try {
        const all = JSON.parse(el?.dataset?.widgets ?? '[]')
        return ['footer1', 'footer2', 'footer3']
            .map(t => all.find(w => w.type === t))
            .filter(Boolean)
    } catch { return [] }
})

const newsletterEmail   = ref('')
const newsletterSuccess = ref(false)
const newsletterError   = ref('')
const subscribing       = ref(false)

async function subscribeNewsletter() {
    subscribing.value     = true
    newsletterError.value = ''
    try {
        const baseUrl = el?.dataset?.baseUrl ?? ''
        await http.post(`${baseUrl}/newsletter/subscribe`, { email: newsletterEmail.value })
        newsletterSuccess.value = true
    } catch (e) {
        newsletterError.value = e.response?.data?.message ?? __('message.something_went_wrong')
    } finally {
        subscribing.value = false
    }
}
</script>

<style scoped>
.footer-widget-content :deep(ul) {
    column-count: 2;
    column-gap: 1.5rem;
    padding-left: 1rem;
}
.footer-widget-content :deep(ul li) {
    break-inside: avoid;
}
</style>
