<template>
    <template v-if="enabledProviders.length">
        <div class="d-flex align-items-center gap-3 my-4">
            <hr class="flex-grow-1 my-0">
            <span class="small">{{ __('message.or_continue_with') }}</span>
            <hr class="flex-grow-1 my-0">
        </div>

        <div class="row g-2">
            <!-- Always one row; with 3+ providers the buttons are icon-only (name stays in aria-label). -->
            <div v-for="p in enabledProviders" :key="p.key" class="col">
                <!-- White, input-style border, light grey on hover; brand-coloured icon. -->
                <button type="button"
                        class="btn btn-outline-light border text-body-emphasis w-100 d-inline-flex align-items-center justify-content-center gap-2 py-2"
                        :aria-label="`${__('message.login_with')} ${p.label}`"
                        :title="`${__('message.login_with')} ${p.label}`"
                        :disabled="busy === p.key"
                        @click="go(p.key)">
                    <!-- Swap, don't stack: fab+fas and two glyphs on one <i> renders a broken icon. -->
                    <i v-if="busy === p.key" class="fs-5 fas fa-circle-notch fa-spin" aria-hidden="true"></i>
                    <!-- Google's official multicolour "G" (Font Awesome's is single-colour). -->
                    <svg v-else-if="p.key === 'google'" width="20" height="20" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                    </svg>
                    <i v-else class="fs-5 fab" :class="p.icon" :style="{ color: p.color }" aria-hidden="true"></i>
                    <span v-if="enabledProviders.length <= 2" class="fw-medium" translate="no">{{ p.label }}</span>
                </button>
            </div>
        </div>
    </template>
</template>

<script setup>
import { computed, ref } from 'vue'
import http from '@/plugins/axios'
import { __ } from '@/plugins/i18n'
import { errorHandler } from '@/helpers/responseHandler.js'

const props = defineProps({
    social:  { type: Object, required: true },
    baseUrl: { type: String, default: '' },
    // Login page's ?redirect=; the backend keeps it for after the provider sends the user back.
    redirect: { type: String, default: '' },
})

const busy = ref('')

// color = the provider's brand colour for its icon.
const PROVIDERS = [
    { key: 'google',   icon: 'fa-google',      label: 'Google' }, // drawn as the multicolour SVG
    { key: 'github',   icon: 'fa-github',      label: 'GitHub',   color: '#181717' },
    { key: 'twitter',  icon: 'fa-x-twitter',   label: 'Twitter',  color: '#000000' },
    { key: 'linkedin', icon: 'fa-linkedin-in', label: 'LinkedIn', color: '#0A66C2' },
]

const enabledProviders = computed(() =>
    PROVIDERS.filter(p => Number(props.social?.[p.key]) === 1)
)

async function go(provider) {
    if (busy.value) return
    busy.value = provider
    try {
        const res = await http.get(`${props.baseUrl}/auth/redirect/${provider}`, props.redirect ? { params: { redirect: props.redirect } } : undefined)
        const url = res.data?.data?.url
        if (url) {
            globalThis.location.href = url
        } else {
            errorHandler({ response: { status: 400, data: { message: __('message.something_wrong') } } }, 'client-page')
            busy.value = ''
        }
    } catch (e) {
        errorHandler(e, 'client-page')
        busy.value = ''
    }
}
</script>
