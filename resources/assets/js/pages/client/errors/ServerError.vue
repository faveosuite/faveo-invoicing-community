<template>
    <ErrorPage :title="__('message.server_error_desc')" :backTo="backTo">
        <template #code>
            5<i class="far fa-frown text-primary"></i><i class="far fa-frown text-primary"></i>
        </template>
    </ErrorPage>
</template>

<script setup>
import ErrorPage from '@/pages/client/errors/ErrorPage.vue'
const backTo = '/client-dashboard'
</script>
