<template>
    <AdminErrorPage
        code="404"
        color="warning"
        :title="__('message.page_not_found')"
        :description="__('message.page_not_found_desc')"
    />
</template>

<script setup>
import AdminErrorPage from './AdminErrorPage.vue'
</script>
