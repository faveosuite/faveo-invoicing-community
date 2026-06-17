<template>
    <div class="user-table-actions">
        <router-link :to="`/products/${productId}/edit`" class="btn btn-light table_btn" v-tooltip="__('message.edit')">
            <i class="fas fa-edit"></i>
        </router-link>
        <button v-if="downloadUrl"
                class="btn btn-light table_btn"
                v-tooltip="__('message.download')"
                @click="downloadFile(downloadUrl)">
            <i class="fas fa-download"></i>
        </button>
    </div>
</template>

<script setup>
import { __ } from '@/plugins/i18n'
import { useDownload } from '@/core/composables/useDownload'

defineProps({
    productId:   { type: [Number, String], required: true },
    downloadUrl: { type: String, default: null },
})

const { downloadFile } = useDownload('products-index')
</script>
