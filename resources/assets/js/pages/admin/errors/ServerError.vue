<template>
    <AdminErrorPage
        code="500"
        color="danger"
        :title="__('message.server_error')"
        :description="__('message.server_error_desc')"
    />
</template>

<script setup>
import AdminErrorPage from './AdminErrorPage.vue'
</script>
