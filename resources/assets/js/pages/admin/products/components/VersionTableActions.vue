<template>
    <div class="d-flex gap-1">
        <router-link
            :to="`/products/${productId}/versions/${versionId}/edit`"
            class="btn btn-light table_btn"
            :title="__('message.edit')"
            v-tooltip
        >
            <i class="fas fa-edit"></i>
        </router-link>
        <button
            class="btn btn-light table_btn"
            :title="__('message.Delete')"
            v-tooltip
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
import DeleteModal from '@/themes/adminlte/components/common/DeleteModal.vue'

const props = defineProps({
    productId: { type: [Number, String], required: true },
    versionId: { type: [Number, String], required: true },
    baseUrl:   { type: String, default: '' },
})

const emit = defineEmits(['deleted'])
const showModal = ref(false)
</script>
