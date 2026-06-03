<template>
    <template v-if="enabledProviders.length">
        <div class="divider">
            <span class="bg-light px-4 position-absolute left-50pct top-50pct transform3dxy-n50">{{ __('message.or') }}</span>
        </div>

        <a v-for="p in enabledProviders" :key="p.key" href="javascript:;"
           class="btn btn-dark btn-modern w-100 text-transform-none rounded-0 font-weight-bold align-items-center d-inline-flex justify-content-center text-3 py-3 mb-2"
           :class="{ disabled: busy === p.key }"
           @click="go(p.key)">
            <i class="fab text-5 me-2" :class="[p.icon, { 'fa-circle-notch fa-spin fas': busy === p.key }]"></i>
            {{ __('message.login_with') }} {{ p.label }}
        </a>
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
})

const busy = ref('')

const PROVIDERS = [
    { key: 'google',   icon: 'fa-google',      label: 'Google' },
    { key: 'facebook', icon: 'fa-facebook',    label: 'Facebook' },
    { key: 'github',   icon: 'fa-github',      label: 'GitHub' },
    { key: 'twitter',  icon: 'fa-twitter',     label: 'Twitter' },
    { key: 'linkedin', icon: 'fa-linkedin-in', label: 'LinkedIn' },
]

const enabledProviders = computed(() =>
    PROVIDERS.filter(p => Number(props.social?.[p.key]) === 1)
)

async function go(provider) {
    if (busy.value) return
    busy.value = provider
    try {
        const res = await http.get(`${props.baseUrl}/auth/redirect/${provider}`)
        const url = res.data?.data?.url
        if (url) {
            window.location.href = url
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
