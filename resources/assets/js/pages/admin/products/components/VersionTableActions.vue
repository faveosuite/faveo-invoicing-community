<template>
    <div class="d-flex gap-1">
        <router-link
            :to="`/products/${productId}/versions/${versionId}/edit`"
            class="btn btn-light table_btn"
            v-tooltip="__('message.edit')"
        >
            <i class="fas fa-edit"></i>
        </router-link>
        <button
            class="btn btn-light table_btn"
            v-tooltip="__('message.Delete')"
            @click="showModal = true"
        >
            <i class="fas fa-trash"></i>
        </button>

        <DeleteModal
            v-if="showModal"
            :showModal="showModal"
            :onClose="() => showModal = false"
            :deleteUrl="`${baseUrl}/product/upload`"
            :deleteData="{ select: [versionId] }"
            :title="__('message.Delete')"
            :message="__('message.are_you_sure')"
            componentName="products-edit"
            @deleted="emit('deleted')"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import DeleteModal from '@/components/Reusable/DeleteModal.vue'

defineProps({
    productId: { type: [Number, String], required: true },
    versionId: { type: [Number, String], required: true },
    baseUrl:   { type: String, default: '' },
})

const emit = defineEmits(['deleted'])
const showModal = ref(false)
</script>
