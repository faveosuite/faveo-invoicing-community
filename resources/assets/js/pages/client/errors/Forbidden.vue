<template>
    <ErrorPage :title="__('message.access_denied_desc')" :backTo="backTo">
        <template #code>
            4<i class="far fa-frown text-primary"></i>3
        </template>
    </ErrorPage>
</template>

<script setup>
import ErrorPage from '@/pages/client/errors/ErrorPage.vue'
const backTo = '/client-dashboard'
</script>
