<template>
    <ErrorPage :title="__('message.page_not_found_desc')" :backTo="backTo">
        <template #code>
            4<i class="far fa-frown text-primary"></i>4
        </template>
    </ErrorPage>
</template>

<script setup>
import ErrorPage from '@/pages/client/errors/ErrorPage.vue'
const backTo = '/client-dashboard'
</script>
