<template>
    <AdminErrorPage
        code="403"
        color="danger"
        :title="__('message.access_denied')"
        :description="__('message.access_denied_desc')"
    />
</template>

<script setup>
import AdminErrorPage from './AdminErrorPage.vue'
</script>
