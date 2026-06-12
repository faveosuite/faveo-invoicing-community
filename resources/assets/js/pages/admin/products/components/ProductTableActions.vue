<template>
    <div class="user-table-actions">
        <router-link :to="`/products/${productId}/edit`" class="btn btn-light table_btn" v-tooltip="__('message.edit')">
            <i class="fas fa-edit"></i>
        </router-link>
        <a v-if="downloadUrl" :href="downloadUrl" class="btn btn-light table_btn" v-tooltip="__('message.download')">
            <i class="fas fa-download"></i>
        </a>
    </div>
</template>

<script setup>
defineProps({
    productId: { type: [Number, String], required: true },
    downloadUrl: { type: String, default: null },
})
</script>
