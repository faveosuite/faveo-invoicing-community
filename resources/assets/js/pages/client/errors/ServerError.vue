<template>
    <ErrorPage :title="__('message.server_error_desc')" :backTo="backTo">
        <template #code>
            <span aria-hidden="true">5<i class="far fa-frown text-primary"></i><i class="far fa-frown text-primary"></i></span><span class="visually-hidden">500</span>
        </template>
    </ErrorPage>
</template>

<script setup>
import ErrorPage from '@/pages/client/errors/ErrorPage.vue'
const backTo = '/client-dashboard'
</script>
